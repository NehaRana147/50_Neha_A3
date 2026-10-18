
<?php
session_start();
require_once "connection.php";

$message = "";

// Generate CAPTCHA numbers if they don't exist
if (!isset($_SESSION["captcha_a"], $_SESSION["captcha_b"])) {
    $_SESSION["captcha_a"] = random_int(1, 9);
    $_SESSION["captcha_b"] = random_int(1, 9);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $captcha_answer = trim($_POST["captcha_answer"] ?? "");

     // Verify CAPTCHA
    $correct_answer = (int) $_SESSION["captcha_a"]
                    + (int) $_SESSION["captcha_b"];

    if (
        $captcha_answer === "" ||
        !ctype_digit($captcha_answer) ||
        (int) $captcha_answer !== $correct_answer
    ) {
        $message = "Incorrect CAPTCHA answer. Please try again.";
    } elseif (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $message = "Enter a valid name, email, and password of at least 8 characters.";
    } else {
        $check = $conn->prepare(
            "SELECT id FROM customers WHERE email = :email"
        );
        $check->execute([":email" => $email]);

        if ($check->fetch()) {
            $message = "This email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO customers (name, email, password)
                 VALUES (:name, :email, :password)"
            );

            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":password" => $hash
            ]);

            unset($_SESSION["captcha_a"], $_SESSION["captcha_b"]);

            header("Location: customer_login.php");
            exit;
        }
    }

    // Generate a new CAPTCHA after every submission
    $_SESSION["captcha_a"] = random_int(1, 9);
    $_SESSION["captcha_b"] = random_int(1, 9);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Registration</title>
</head>
<body>

<h1>Create Customer Account</h1>

<p><?= htmlspecialchars($message) ?></p>

<form method="POST">
    <label>Name:</label>
    <input type="text" name="name" maxlength="100" required>
    <br><br>

    <label>Email:</label>
    <input type="email" name="email" maxlength="150" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" minlength="8" required>
    <br><br>

    
    <label>
        CAPTCHA:
        <?= (int) $_SESSION["captcha_a"] ?>
        +
        <?= (int) $_SESSION["captcha_b"] ?>
        = ?
    </label>

    <input
        type="number"
        name="captcha_answer"
        placeholder="Enter the answer"
        required
    >
    <br><br>

    <button type="submit">Register</button>
</form>

<a href="customer_login.php">Already registered? Login</a>

</body>
</html>
