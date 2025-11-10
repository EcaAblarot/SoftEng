<?php
session_start();
include 'db.php';

$message = "";
$attempts = isset($_SESSION['attempts']) ? intval($_SESSION['attempts']) : 0;

// Handle logout posted from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Prepared statement
    $stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Regenerate session id on login
                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['attempts'] = 0;
                header('Location: dashboard.php');
                exit();
            } else {
                $attempts++;
                $_SESSION['attempts'] = $attempts;

                if ($attempts >= 3) {
                    $_SESSION['attempts'] = 0; // reset counter
                    $_SESSION['recover_user'] = $username;
                    header('Location: forgot_password.php');
                    exit();
                }

                $message = "Invalid password. Attempts: $attempts / 3";
            }
        } else {
            $message = 'Username not found.';
        }

        $stmt->close();
    } else {
        $message = 'Server error. Please try again later.';
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
        <div class="alert"><?= htmlspecialchars($message) ?></div>
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
