
<?php
require_once "auth.php";
require_once "connection.php";

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC";

$products = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product List</title>
</head>
<body>

<h1>Product List</h1>
<a href="add_product.php">Add New Product</a>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Image</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($products as $product): ?>
        <tr>
            <td><?= (int)$product['id'] ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= htmlspecialchars($product['category_name']) ?></td>
            <td><?= number_format((float)$product['price'], 2) ?></td>
            <td><?= (int)$product['stock'] ?></td>
            <td>
                <?php if (!empty($product['image'])): ?>
                    <img
                        src="uploads/<?= rawurlencode($product['image']) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        width="80"
                    >
                <?php else: ?>
                    No image
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_product.php?id=<?= (int)$product['id'] ?>">Edit</a>

                <form action="delete_product.php" method="POST"
                    onsubmit="return confirm('Delete this product?')"
                    style="display:inline">
                    <input type="hidden" name="id"
                        value="<?= (int)$product['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
