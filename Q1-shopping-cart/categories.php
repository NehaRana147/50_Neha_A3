
<?php
require_once "auth.php";
require_once "connection.php";

$sql = "SELECT * FROM categories ORDER BY id DESC";
$stmt = $conn->query($sql);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
</head>
<body>
    <a href="add_category.php">Add New Categories</a>

    <h1>Category List</h1>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= (int) $category['id'] ?></td>
                <td><?= htmlspecialchars($category['name']) ?></td>
                <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                <td><?= htmlspecialchars($category['created_at']) ?></td>
                <td>
                    <a href="edit_category.php?id=<?= (int) $category['id'] ?>">Edit</a>
                    |
                    <!-- <a href="delete_category.php?id=<?= (int) $category['id'] ?>"
                    onclick="return confirm('Are you sure you want to delete this category?')">
                        Delete
                    </a> -->
                    <form action="delete_category.php" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this category?')">
                        <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

</body>
</html>
