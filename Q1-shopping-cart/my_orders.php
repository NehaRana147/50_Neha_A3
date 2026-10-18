<?php
session_start();
require_once "connection.php";

// Customer must be logged in
if (!isset($_SESSION["customer_id"])) {
    header("Location: customer_login.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, total_amount, status, created_at
     FROM orders
     WHERE customer_id = :customer_id
     ORDER BY created_at DESC"
);

$stmt->execute([
    ":customer_id" => $_SESSION["customer_id"]
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
</head>
<body>

<h1>My Order History</h1>

<p>
    Welcome,
    <?= htmlspecialchars($_SESSION["customer_name"]) ?>!
</p>

<a href="shop.php">Continue Shopping</a> |
<a href="customer_logout.php">Logout</a>

<hr>

<?php if (empty($orders)): ?>

    <p>You have not placed any orders yet.</p>

<?php else: ?>

    <table border="1" cellpadding="10">
        <tr>
            <th>Order ID</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Order Date</th>
        </tr>

        <?php foreach ($orders as $order): ?>
            <tr>
                <td>
                    <a href="order_details.php?id=<?= (int) $order["id"] ?>">
                        <?= (int) $order["id"] ?>
                    </a>
                </td>

                <td>
                    ₹<?= number_format(
                        (float) $order["total_amount"], 2
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars($order["status"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($order["created_at"]) ?>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

<?php endif; ?>

</body>
</html>