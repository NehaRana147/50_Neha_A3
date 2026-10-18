<?php
require_once "auth.php";
require_once "connection.php";

$message = "";

$stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_id = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT);
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
        
        
$imageName = null;

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

    // Create the uploads directory if necessary
    $uploadDir = __DIR__ . "/uploads/";

    if (
        !is_dir($uploadDir) &&
        !mkdir($uploadDir, 0755, true)
    ) {
        exit("Could not create upload directory.");
    }

    // Get original image dimensions
    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];

    // Resize only when either dimension exceeds 800 pixels
    $maxSize = 800;

    $scale = min(
        1,
        $maxSize / $originalWidth,
        $maxSize / $originalHeight
    );

    $newWidth = max(1, (int) round($originalWidth * $scale));
    $newHeight = max(1, (int) round($originalHeight * $scale));

    // Load the uploaded image
    switch ($mime) {
        case "image/jpeg":
            $source = imagecreatefromjpeg($_FILES["image"]["tmp_name"]);
            break;

        case "image/png":
            $source = imagecreatefrompng($_FILES["image"]["tmp_name"]);
            break;

        case "image/webp":
            $source = imagecreatefromwebp($_FILES["image"]["tmp_name"]);
            break;

        default:
            exit("Unsupported image format.");
    }

    if ($source === false) {
        exit("Could not process uploaded image.");
    }

    // Create the resized image
    $resized = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG and WebP
    if ($mime === "image/png" || $mime === "image/webp") {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        $transparent = imagecolorallocatealpha(
            $resized, 0, 0, 0, 127
        );

        imagefilledrectangle(
            $resized, 0, 0,
            $newWidth - 1, $newHeight - 1,
            $transparent
        );
    }

    imagecopyresampled(
        $resized,
        $source,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $originalWidth, $originalHeight
    );

    // Generate a unique filename
    $imageName = bin2hex(random_bytes(16))
        . "." . $allowedTypes[$mime];

    $destination = $uploadDir . $imageName;

    // Save the resized image in its original format
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
}


        $stmt = $conn->prepare(
            "INSERT INTO products
            (category_id, name, description, price, stock, image)
            VALUES (:category_id, :name, :description, :price, :stock, :image)"
        );

        $stmt->execute([
            ":category_id" => $category_id,
            ":name" => $name,
            ":description" => $description,
            ":price" => $price,
            ":stock" => $stock,
            ":image" => $imageName
        ]);

        header("Location: products.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
</head>
<body>

<h1>Add Product</h1>

<p><?= htmlspecialchars($message) ?></p>

<form method="POST" enctype="multipart/form-data">
    <label>Category:</label>
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int)$category['id'] ?>">
                <?= htmlspecialchars($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>Product Name:</label>
    <input type="text" name="name" required maxlength="150">

    <br><br>

    <label>Description:</label>
    <textarea name="description"></textarea>

    <br><br>

    <label>Price:</label>
    <input type="number" name="price" min="0" step="0.01" required>

    <br><br>

    <label>Stock:</label>
    <input type="number" name="stock" min="0" step="1" required>

    <br><br>

    <label>Product Image:</label>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <br><br>

    <button type="submit">Save Product</button>
</form>

</body>
</html>
