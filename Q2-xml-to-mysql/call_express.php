
<?php
// Express REST API URL
$url = "http://localhost:3000/api/students";

// Call the API using PHP cURL
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo "API request failed: " . htmlspecialchars(curl_error($ch));
    curl_close($ch);
    exit;
}

curl_close($ch);

// Check API response
if ($httpCode !== 200) {
    exit("Express API returned HTTP status: " . $httpCode);
}

// Convert JSON into a PHP array
$students = json_decode($response, true);

if (!is_array($students)) {
    exit("Invalid JSON response from Express API.");
}

// Display student data
echo "<h1>Students from Express API</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Course</th></tr>";

foreach ($students as $student) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars((string) $student["id"]) . "</td>";
    echo "<td>" . htmlspecialchars($student["name"]) . "</td>";
    echo "<td>" . htmlspecialchars($student["course"]) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
