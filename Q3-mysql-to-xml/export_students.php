
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

    // Read students from MySQL
    $stmt = $conn->query(
        "SELECT id, name, email, course FROM students ORDER BY id"
    );

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create XML document
    $xml = new SimpleXMLElement(
        '<?xml version="1.0" encoding="UTF-8"?><students/>'
    );

    foreach ($students as $student) {
        $studentNode = $xml->addChild("student");

        $studentNode->addChild("id", (string) $student["id"]);
        // $studentNode->addChild("name")->addCData($student["name"]);
        // $studentNode->addChild("email")->addCData($student["email"]);
        // $studentNode->addChild("course")->addCData($student["course"]);
        $studentNode->addChild("name", htmlspecialchars(
            $student["name"], ENT_QUOTES | ENT_XML1, "UTF-8"
        ));

        $studentNode->addChild("email", htmlspecialchars(
            $student["email"], ENT_QUOTES | ENT_XML1, "UTF-8"
        ));

        $studentNode->addChild("course", htmlspecialchars(
            $student["course"], ENT_QUOTES | ENT_XML1, "UTF-8"
        ));
    }

    // Save XML file
    $xmlFile = __DIR__ . "/exported_students.xml";

    $xml->asXML($xmlFile);

    echo "Students exported successfully to exported_students.xml!";

} catch (Throwable $e) {
    echo "Export failed: " . htmlspecialchars($e->getMessage());
}
?>
