
<?php
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

    $xmlFile = __DIR__ . "/students.xml";

    if (!file_exists($xmlFile)) {
        exit("students.xml file not found!");
    }

    $xml = simplexml_load_file($xmlFile);

    if ($xml === false) {
        exit("Could not read the XML file.");
    }

    $stmt = $conn->prepare(
        "INSERT INTO students (id, name, email, course)
         VALUES (:id, :name, :email, :course)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            email = VALUES(email),
            course = VALUES(course)"
    );

    $conn->beginTransaction();

    foreach ($xml->student as $student) {
        $stmt->execute([
            ":id" => (int) $student->id,
            ":name" => (string) $student->name,
            ":email" => (string) $student->email,
            ":course" => (string) $student->course
        ]);
    }

    $conn->commit();

    echo "Students imported successfully!";

} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    echo "Import failed: " . htmlspecialchars($e->getMessage());
}
?>
