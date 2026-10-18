<?php
require_once "auth.php";
require_once "connection.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid product ID.");
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([":id" => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    exit("Product not found.");
}

$categories = $conn->query(
    "SELECT id, name FROM categories ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_id = filter_input(
        INPUT_POST, "category_id", FILTER_VALIDATE_INT
    );
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = $_POST["price"] ?? "";
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    if (
        !$category_id ||
        $name === "" ||
        !is_numeric($price) ||
        (float)$price < 0 ||
        $stock === false ||
        $stock === null ||
        $stock < 0
    ) {
        $message = "Please enter valid product details.";
    } else {
        // Keep the current image unless a new one is uploaded.
        $imageName = $product["image"] ?? null;
        $newImageUploaded = false;

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {
            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
                exit("Image upload failed.");
            }

            if ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                exit("Image must be 2 MB or smaller.");
            }

            $imageInfo = getimagesize($_FILES["image"]["tmp_name"]);

            if ($imageInfo === false) {
                exit("Please upload a valid image.");
            }

            $allowedTypes = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $mime = $imageInfo["mime"];

            if (!isset($allowedTypes[$mime])) {
                exit("Only JPG, PNG, and WebP images are allowed.");
            }

            // Ensure GD is available.
            if (!extension_loaded("gd")) {
                exit("PHP GD extension is required for image resizing.");
            }

            $uploadDir = __DIR__ . "/uploads/";

            if (
                !is_dir($uploadDir) &&
                !mkdir($uploadDir, 0755, true)
            ) {
                exit("Could not create upload directory.");
            }

            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];

            // Maximum dimensions: 800 x 800.
            $maxSize = 800;

            $scale = min(
                1,
                $maxSize / $originalWidth,
                $maxSize / $originalHeight
            );

            $newWidth = max(
                1, (int) round($originalWidth * $scale)
            );

            $newHeight = max(
                1, (int) round($originalHeight * $scale)
            );

            switch ($mime) {
                case "image/jpeg":
                    $source = imagecreatefromjpeg(
                        $_FILES["image"]["tmp_name"]
                    );
                    break;

                case "image/png":
                    $source = imagecreatefrompng(
                        $_FILES["image"]["tmp_name"]
                    );
                    break;

                case "image/webp":
                    $source = imagecreatefromwebp(
                        $_FILES["image"]["tmp_name"]
                    );
                    break;

                default:
                    $source = false;
            }

            if ($source === false) {
                exit("Could not process uploaded image.");
            }

            $resized = imagecreatetruecolor(
                $newWidth, $newHeight
            );

            // Preserve PNG and WebP transparency.
            if ($mime === "image/png" || $mime === "image/webp") {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);

                $transparent = imagecolorallocatealpha(
                    $resized, 0, 0, 0, 127
                );

                imagefilledrectangle(
                    $resized,
                    0,
                    0,
                    $newWidth - 1,
                    $newHeight - 1,
                    $transparent
                );
            }

            imagecopyresampled(
                $resized,
                $source,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            $imageName = bin2hex(random_bytes(16))
                . "." . $allowedTypes[$mime];

            $destination = $uploadDir . $imageName;

            switch ($mime) {
                case "image/jpeg":
                    $saved = imagejpeg($resized, $destination, 85);
                    break;

                case "image/png":
                    $saved = imagepng($resized, $destination, 6);
                    break;

                case "image/webp":
                    $saved = imagewebp($resized, $destination, 85);
                    break;

                default:
                    $saved = false;
            }

            imagedestroy($source);
            imagedestroy($resized);

            if (!$saved) {
                exit("Could not save resized image.");
            }

            $newImageUploaded = true;
        }

        try {
            $stmt = $conn->prepare(
                "UPDATE products
                 SET category_id = :category_id,
                     name = :name,
                     description = :description,
                     price = :price,
                     stock = :stock,
                     image = :image
                 WHERE id = :id"
            );

            $stmt->execute([
                ":category_id" => $category_id,
                ":name" => $name,
                ":description" => $description,
                ":price" => $price,
                ":stock" => $stock,
                ":image" => $imageName,
                ":id" => $id
            ]);

            // Remove the old image only after the update succeeds.
            if (
                $newImageUploaded &&
                !empty($product["image"])
            ) {
                $oldImage = basename($product["image"]);
                $oldImagePath = __DIR__ . "/uploads/" . $oldImage;

                if (is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            header("Location: products.php");
            exit;

        } catch (PDOException $e) {
            // Avoid leaving an unused new image behind.
            if ($newImageUploaded && !empty($imageName)) {
                $newImagePath = __DIR__ . "/uploads/" . basename($imageName);

                if (is_file($newImagePath)) {
                    unlink($newImagePath);
                }
            }

            $message = "Could not update product. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
</head>
<body>

<h1>Edit Product</h1>

<p><?= htmlspecialchars($message) ?></p>

<form method="POST" enctype="multipart/form-data">

    <label>Category:</label>
    <select name="category_id" required>
        <?php foreach ($categories as $category): ?>
            <option
                value="<?= (int)$category['id'] ?>"
                <?= (int)$category['id'] === (int)$product['category_id']
                    ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>Product Name:</label>
    <input
        type="text"
        name="name"
        maxlength="150"
        value="<?= htmlspecialchars($product['name']) ?>"
        required
    >

    <br><br>

    <label>Description:</label>
    <textarea name="description"><?= htmlspecialchars(
        $product['description'] ?? ''
    ) ?></textarea>

    <br><br>

    <label>Price:</label>
    <input
        type="number"
        name="price"
        min="0"
        step="0.01"
        value="<?= htmlspecialchars($product['price']) ?>"
        required
    >

    <br><br>

    <label>Stock:</label>
    <input
        type="number"
        name="stock"
        min="0"
        step="1"
        value="<?= (int)$product['stock'] ?>"
        required
    >

    <br><br>

    <label>Current Product Image:</label>
    <br>

    <?php if (!empty($product['image'])): ?>
        <img
            src="uploads/<?= rawurlencode(basename($product['image'])) ?>"
            alt="Current product image"
            style="max-width: 200px; max-height: 200px;"
        >
    <?php else: ?>
        <p>No product image uploaded.</p>
    <?php endif; ?>

    <br><br>

    <label>Upload New Image (optional):</label>
    <input
        type="file"
        name="image"
        accept="image/jpeg,image/png,image/webp"
    >

    <p>Maximum file size: 2 MB. Images are resized to fit within 800 × 800 pixels.</p>

    <button type="submit">Update Product</button>

</form>

<br>

<a href="products.php">Back to Products</a>

</body>
</html>
