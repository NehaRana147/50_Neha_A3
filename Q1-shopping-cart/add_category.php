
<?php
require_once "auth.php";
require_once "connection.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "") {
        $message = "Category name is required.";
    } else {
        $sql = "INSERT INTO categories (name, description)
                VALUES (:name, :description)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ":name" => $name,
            ":description" => $description
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
    <title>Add Category</title>
</head>
<body>

    <h1>Add Category</h1>

    <p><?= htmlspecialchars($message) ?></p>

    <form method="POST">
        <label>Category Name:</label>
        <input type="text" name="name" required>
        <br><br>

        <label>Description:</label>
        <textarea name="description"></textarea>
        <br><br>

        <button type="submit">Save Category</button>
    </form>

    <a href="categories.php">View Categories</a>

</body>
</html>
