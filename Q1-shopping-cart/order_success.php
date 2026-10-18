
<?php
session_start();

if (!isset($_SESSION["customer_id"])) {
    header("Location: customer_login.php");
    exit;
}

if (!isset($_SESSION["last_order_id"])) {
    header("Location: shop.php");
    exit;
}

$order_id = (int) $_SESSION["last_order_id"];
unset($_SESSION["last_order_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Successful</title>
</head>
<body>

<h1>Order Placed Successfully!</h1>

<p>Your order number is: <?= $order_id ?></p>

<p>Thank you for shopping with us!</p>

<a href="shop.php">Continue Shopping</a>

</body>
</html>
