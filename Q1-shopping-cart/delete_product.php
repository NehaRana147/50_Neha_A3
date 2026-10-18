<?php
require_once "auth.php";
require_once "connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Invalid request method.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid product ID.");
}

$stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
$stmt->execute([":id" => $id]);

header("Location: products.php");
exit;
?>
