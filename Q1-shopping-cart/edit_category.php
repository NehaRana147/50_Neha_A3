<?php
require_once "auth.php";
require_once "connection.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid category ID.");
}

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->execute([":id" => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    exit("Category not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "") {
        $message = "Category name is required.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE categories
             SET name = :name, description = :description
             WHERE id = :id"
        );

        $stmt->execute([
            ":name" => $name,
            ":description" => $description,
            ":id" => $id
        ]);

        header("Location: categories.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category</title>
</head>
<body>

    <h1>Edit Category</h1>

    <p><?= htmlspecialchars($message) ?></p>

    <form method="POST">
        <label>Category Name:</label>
        <input
            type="text"
            name="name"
            value="<?= htmlspecialchars($category['name']) ?>"
            required
        >
        <br><br>

        <label>Description:</label>
        <textarea name="description"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
        <br><br>

        <button type="submit">Update Category</button>
    </form>

    <a href="categories.php">Back to Categories</a>

</body>
</html>
