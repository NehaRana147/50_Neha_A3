<?php
require_once "auth.php";
require_once "connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Use the category list to delete a category.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid category ID.");
}

$stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
$stmt->execute([":id" => $id]);

header("Location: categories.php");
exit;
?>
