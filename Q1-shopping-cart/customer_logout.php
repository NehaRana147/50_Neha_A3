
<?php
session_start();

unset(
    $_SESSION["customer_id"],
    $_SESSION["customer_name"]
);

header("Location: customer_login.php");
exit;
?>
