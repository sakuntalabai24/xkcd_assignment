<?php
require_once 'functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<span style='color: red;'>Invalid email address.</span>";
    } else {
        unsubscribeEmail($email);
        $message = "<span style='color: green;'>$email unsubscribed successfully.</span>";
    }               
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe from XKCD</title>
</head>
<body>
    <h2>Unsubscribe</h2>
    <p><?= $message ?></p>
    <form method="POST">
        Email: <input type="email" name="email" required><br>
        <button type="submit">Unsubscribe</button>
    </form>
</body>
</html>
