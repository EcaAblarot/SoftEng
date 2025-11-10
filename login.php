<?php
session_start();
include 'db.php';

$message = "";
$attempts = $_SESSION['attempts'] ?? 0;

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['attempts'] = 0;
            header("Location: dashboard.php");
            exit();
        } else {
            $attempts++;
            $_SESSION['attempts'] = $attempts;

            if ($attempts >= 3) {
                $_SESSION['attempts'] = 0; // reset counter
                $_SESSION['recover_user'] = $username;
                header("Location: forgot_password.php");
                exit();
            }

            $message = "Invalid password. Attempts: $attempts / 3";
        }
    } else {
        $message = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-container">
    <div class="imageContainer">
        <img src = "gics_image.png"  alt = "Image of Logo" width = "250" height = "250">
    </div>
    <h2>Genesis Integrated Christian Scool</h2>

    <?php if ($message): ?>
        <div class="alert"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>
