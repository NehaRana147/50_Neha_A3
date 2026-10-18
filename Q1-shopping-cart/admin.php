
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>

<h1>Welcome, <?= htmlspecialchars($_SESSION["admin_username"]) ?>!</h1>

<ul>
    <li><a href="categories.php">Manage Categories</a></li>
    <li><a href="products.php">Manage Products</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>

</body>
</html>
