<?php
session_start();
require_once "connection.php";

if (!isset($_SESSION["customer_id"])) {
    header("Location: customer_login.php");
    exit;
}

$order_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$order_id || $order_id < 1) {
    exit("Invalid order ID.");
}

// Get only an order belonging to the logged-in customer
$stmt = $conn->prepare(
    "SELECT id, total_amount, status, created_at
     FROM orders
     WHERE id = :order_id AND customer_id = :customer_id"
);

$stmt->execute([
    ":order_id" => $order_id,
    ":customer_id" => $_SESSION["customer_id"]
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit("Order not found.");
}

// Get products belonging to this order
$stmt = $conn->prepare(
    "SELECT product_name, price, quantity, subtotal
     FROM order_items
     WHERE order_id = :order_id"
);

$stmt->execute([":order_id" => $order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
</head>
<body>

<h1>Order Details</h1>

<p><strong>Order ID:</strong> <?= (int) $order["id"] ?></p>

<p><strong>Status:</strong>
    <?= htmlspecialchars($order["status"]) ?>
</p>

<p><strong>Order Date:</strong>
    <?= htmlspecialchars($order["created_at"]) ?>
</p>

<h2>Products</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Subtotal</th>
    </tr>

    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item["product_name"]) ?></td>
            <td>₹<?= number_format((float) $item["price"], 2) ?></td>
            <td><?= (int) $item["quantity"] ?></td>
            <td>₹<?= number_format((float) $item["subtotal"], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>
    Total: ₹<?= number_format((float) $order["total_amount"], 2) ?>
</h3>

<a href="my_orders.php">Back to My Orders</a> |
<a href="shop.php">Continue Shopping</a>

</body>
</html>
