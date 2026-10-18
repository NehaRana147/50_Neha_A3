
<?php
session_start();
require_once "connection.php";

// Check customer login
if (!isset($_SESSION["customer_id"])) {
    header("Location: customer_login.php");
    exit;
}

// Check whether cart has products
if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

$customer_id = (int) $_SESSION["customer_id"];
$cart = $_SESSION["cart"];
$total = 0;
$items = [];

try {
    // Verify current stock and prices using the database
    foreach ($cart as $product_id => $cart_item) {
        $stmt = $conn->prepare(
            "SELECT id, name, price, stock
             FROM products
             WHERE id = ?
             FOR UPDATE"
        );
        // FOR UPDATE is effective inside a transaction
        // so the transaction starts below before locking rows.
    }

    $conn->beginTransaction();

    foreach ($cart as $product_id => $cart_item) {
        $stmt = $conn->prepare(
            "SELECT id, name, price, stock
             FROM products
             WHERE id = ?
             FOR UPDATE"
        );
        $stmt->execute([(int) $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $quantity = (int) $cart_item["quantity"];

        if (!$product) {
            throw new Exception("A product in your cart no longer exists.");
        }

        if ($quantity < 1 || $quantity > (int) $product["stock"]) {
            throw new Exception(
                "Insufficient stock for " . $product["name"] .
                ". Available stock: " . $product["stock"]
            );
        }

        $price = (float) $product["price"];
        $subtotal = $price * $quantity;
        $total += $subtotal;

        $items[] = [
            "id" => (int) $product["id"],
            "name" => $product["name"],
            "price" => $price,
            "quantity" => $quantity,
            "subtotal" => $subtotal
        ];
    }

    // Create order
    $stmt = $conn->prepare(
        "INSERT INTO orders (customer_id, total_amount, status)
         VALUES (?, ?, 'Placed')"
    );
    $stmt->execute([$customer_id, $total]);

    $order_id = (int) $conn->lastInsertId();

    // Save order items and reduce stock
    $item_stmt = $conn->prepare(
        "INSERT INTO order_items
         (order_id, product_id, product_name, price, quantity, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stock_stmt = $conn->prepare(
        "UPDATE products
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?"
    );

    foreach ($items as $item) {
        $item_stmt->execute([
            $order_id,
            $item["id"],
            $item["name"],
            $item["price"],
            $item["quantity"],
            $item["subtotal"]
        ]);

        $stock_stmt->execute([
            $item["quantity"],
            $item["id"],
            $item["quantity"]
        ]);

        if ($stock_stmt->rowCount() !== 1) {
            throw new Exception(
                "Stock changed for " . $item["name"] .
                ". Please try again."
            );
        }
    }

    $conn->commit();

    // Empty cart after successful order
    unset($_SESSION["cart"]);
    $_SESSION["last_order_id"] = $order_id;

    header("Location: order_success.php");
    exit;

} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<body>

<h1>Checkout</h1>

<?php if (isset($error)): ?>
    <p style="color:red;">
        <?= htmlspecialchars($error) ?>
    </p>
    <a href="cart.php">Return to Cart</a>
<?php endif; ?>

</body>
</html>
