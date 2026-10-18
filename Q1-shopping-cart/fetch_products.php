
<?php
session_start();
require_once "connection.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["customer_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Please log in again."]);
    exit;
}

$page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT);
$page = ($page && $page > 0) ? $page : 1;

$category_id = filter_input(INPUT_GET, "category_id", FILTER_VALIDATE_INT);
$category_id = ($category_id && $category_id > 0) ? $category_id : null;

$limit = 4;
$offset = ($page - 1) * $limit;

try {
    // Count total products for selected category
    $count_sql = "SELECT COUNT(*) FROM products";
    if ($category_id !== null) {
        $count_sql .= " WHERE category_id = ?";
        $stmt = $conn->prepare($count_sql);
        $stmt->execute([$category_id]);
    } else {
        $stmt = $conn->query($count_sql);
    }

    $total_products = (int) $stmt->fetchColumn();
    $total_pages = (int) ceil($total_products / $limit);

    // Fetch products for current page
    $sql = "SELECT p.id, p.name, p.description, p.price,
                   p.stock, p.image, c.name AS category_name
            FROM products p
            INNER JOIN categories c ON p.category_id = c.id";

    if ($category_id !== null) {
        $sql .= " WHERE p.category_id = ?";
    }

    $sql .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    if ($category_id !== null) {
        $stmt->bindValue(1, $category_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "products" => $products,
        "current_page" => $page,
        "total_pages" => $total_pages
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Unable to load products."]);
}
