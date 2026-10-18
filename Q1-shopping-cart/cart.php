
<?php
session_start();
require_once "connection.php";

// Make sure customer is logged in
if (!isset($_SESSION["customer_id"])) {
    header("Location: customer_login.php");
    exit;
}

// Initialize cart
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// Handle adding a product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["product_id"])) {
    $product_id = (int) $_POST["product_id"];

    $stmt = $conn->prepare(
        "SELECT id, name, price, stock, image FROM products WHERE id = ?"
    );
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $product["stock"] > 0) {
        $current_qty = $_SESSION["cart"][$product_id]["quantity"] ?? 0;

        if ($current_qty < $product["stock"]) {
            $_SESSION["cart"][$product_id] = [
                "id" => $product["id"],
                "name" => $product["name"],
                "price" => (float) $product["price"],
                "image" => $product["image"],
                "quantity" => $current_qty + 1
            ];
        }
    }

    header("Location: cart.php");
    exit;
}

// Remove a product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_id"])) {
    $remove_id = (int) $_POST["remove_id"];
    unset($_SESSION["cart"][$remove_id]);

    header("Location: cart.php");
    exit;
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
</head>
<body>

<h1>My Shopping Cart</h1>

<p>
    Welcome, <?= htmlspecialchars($_SESSION["customer_name"]) ?>!
</p>

<a href="shop.php">Continue Shopping</a>
<hr>

<?php if (empty($_SESSION["cart"])): ?>

    <p>Your cart is empty.</p>

<?php else: ?>

    <table border="1" cellpadding="10">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>

        <?php foreach ($_SESSION["cart"] as $item): ?>
            <?php
                $subtotal = $item["price"] * $item["quantity"];
                $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item["name"]) ?></td>
                <td>₹<?= number_format($item["price"], 2) ?></td>
                <td><?= $item["quantity"] ?></td>
                <td>₹<?= number_format($subtotal, 2) ?></td>
                <td>
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="remove_id"
                               value="<?= (int) $item["id"] ?>">
                        <button type="submit">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

    <h3>Total: ₹<?= number_format($total, 2) ?></h3>

    <p>
        <a href="checkout.php">Proceed to Checkout</a>
    </p>

<?php endif; ?>

</body>
</html>
