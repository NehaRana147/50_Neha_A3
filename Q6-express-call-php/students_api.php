
<?php
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$dbname = "student_db";
$username = "root";
$password = "nr@21";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $stmt = $conn->query(
        "SELECT id, name, email, course
         FROM students
         ORDER BY id"
    );

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($students);

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        "error" => "Unable to retrieve students."
    ]);
}
?>
