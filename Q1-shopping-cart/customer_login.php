

<?php
session_start();
require_once "connection.php";

$message = "";

// Generate CAPTCHA if it doesn't exist
if (
    !isset($_SESSION["login_captcha_a"]) ||
    !isset($_SESSION["login_captcha_b"])
) {
    $_SESSION["login_captcha_a"] = random_int(1, 9);
    $_SESSION["login_captcha_b"] = random_int(1, 9);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $captcha_answer = trim($_POST["captcha_answer"] ?? "");

    $correct_answer =
        (int) $_SESSION["login_captcha_a"] +
        (int) $_SESSION["login_captcha_b"];

    if (
        $captcha_answer === "" ||
        !ctype_digit($captcha_answer) ||
        (int) $captcha_answer !== $correct_answer
    ) {
        $message = "Incorrect CAPTCHA answer. Please try again.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM customers
             WHERE email = :email"
        );

        $stmt->execute([":email" => $email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $customer &&
            password_verify($password, $customer["password"])
        ) {
            // Clear login CAPTCHA
            unset(
                $_SESSION["login_captcha_a"],
                $_SESSION["login_captcha_b"]
            );

            session_regenerate_id(true);

            $_SESSION["customer_id"] = $customer["id"];
            $_SESSION["customer_name"] = $customer["name"];

            header("Location: shop.php");
            exit;
        }

        $message = "Invalid email or password.";
    }

    // Generate a fresh CAPTCHA after every failed attempt
    $_SESSION["login_captcha_a"] = random_int(1, 9);
    $_SESSION["login_captcha_b"] = random_int(1, 9);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Login</title>
</head>
<body>

<h1>Customer Login</h1>

<p><?= htmlspecialchars($message) ?></p>

<form method="POST">
    <label>Email:</label>
    <input type="email" name="email" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" required>
    <br><br>

    <label>
        CAPTCHA:
        <?= (int) $_SESSION["login_captcha_a"] ?>
        +
        <?= (int) $_SESSION["login_captcha_b"] ?>
        = ?
    </label>

    <input
        type="number"
        name="captcha_answer"
        placeholder="Enter the answer"
        required
    >
    <br><br>

    <button type="submit">Login</button>
</form>

<a href="register.php">Create an account</a>

</body>
</html>
