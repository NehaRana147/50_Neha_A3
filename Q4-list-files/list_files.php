
<?php
$directory = __DIR__;

if (!is_dir($directory)) {
    exit("Directory does not exist.");
}

$files = scandir($directory);

echo "<h1>Files in Directory</h1>";
echo "<ul>";

foreach ($files as $file) {
    if ($file === "." || $file === "..") {
        continue;
    }

    $fullPath = $directory . DIRECTORY_SEPARATOR . $file;

    if (is_file($fullPath)) {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
}

echo "</ul>";
?>
