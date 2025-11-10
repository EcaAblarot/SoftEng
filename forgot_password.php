<?php
session_start();
include 'db.php';

$message = "";
$username = $_SESSION['recover_user'] ?? "";

if (!$username) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['recover'])) {
    $a1 = strtolower(trim($_POST['answer1'] ?? ''));
    $a2 = strtolower(trim($_POST['answer2'] ?? ''));
    $a3 = strtolower(trim($_POST['answer3'] ?? ''));
    $newpass = password_hash($_POST['new_password'] ?? '', PASSWORD_DEFAULT);

    // Use prepared statement to fetch stored answers
    $stmt = $conn->prepare('SELECT answer1, answer2, answer3 FROM users WHERE username = ?');
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (
                $a1 === strtolower($user['answer1']) &&
                $a2 === strtolower($user['answer2']) &&
                $a3 === strtolower($user['answer3'])
            ) {
                $upd = $conn->prepare('UPDATE users SET password = ? WHERE username = ?');
                if ($upd) {
                    $upd->bind_param('ss', $newpass, $username);
                    $upd->execute();
                    $message = '✅ Password successfully reset! You can now log in.';
                    unset($_SESSION['recover_user']);
                    $upd->close();
                } else {
                    $message = 'Server error. Please try again later.';
                }
            } else {
                $message = '❌ Incorrect answers. Try again.';
            }
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
    <title>Forgot Password | Enrollment System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-container">
    <h2>Password Recovery</h2>

    <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <p class="info">Answer your security questions to reset your password for <b><?= htmlspecialchars($username) ?></b>.</p>

        <label>1. What is your favorite color?</label>
        <input type="text" name="answer1" required>

        <label>2. What is your pet’s name?</label>
        <input type="text" name="answer2" required>

        <label>3. What city were you born in?</label>
        <input type="text" name="answer3" required>

        <label>Enter new password</label>
        <input type="password" name="new_password" required>

        <button type="submit" name="recover">Reset Password</button>
        <a href="login.php" class="back-link">← Back to Login</a>
    </form>
</div>
</body>
</html>
