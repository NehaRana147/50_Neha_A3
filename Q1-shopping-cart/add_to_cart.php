
<?php
session_start();
require_once "connection.php";

// Only logged-in customers can add products
if (!isset($_SESSION["customer_id"])) {
    header("Location: customer_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: shop.php");
    exit;
}

$product_id = filter_input(INPUT_POST, "product_id", FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, "quantity", FILTER_VALIDATE_INT);

if (!$product_id || !$quantity || $quantity < 1) {
    die("Invalid product or quantity.");
}

// Check whether the product exists and has enough stock
$stmt = $conn->prepare(
    "SELECT id, name, price, stock
     FROM products
     WHERE id = ?"
);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

if ($quantity > (int)$product["stock"]) {
    die("Sorry, not enough stock available.");
}

// Create the cart if it doesn't exist
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// Add quantity if the product is already in the cart
$current_quantity = $_SESSION["cart"][$product_id]["quantity"] ?? 0;
$new_quantity = $current_quantity + $quantity;

if ($new_quantity > (int)$product["stock"]) {
    die("You cannot add more than the available stock.");
}

$_SESSION["cart"][$product_id] = [
    "name" => $product["name"],
    "price" => (float)$product["price"],
    "quantity" => $new_quantity
];

header("Location: cart.php");
exit;
?>
