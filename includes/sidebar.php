<?php
// Reusable sidebar include
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h4>
            <img class="yes" src="gics_image.png" alt="Image of Logo" width="50" height="50">
            Genesis Integrated Christian School
        </h4>
        <p class="user">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></p>
    </div>
    <?php $current = basename($_SERVER['PHP_SELF']); ?>
    <nav class="menu">
        <a href="dashboard.php" class="menu-item <?= $current === 'dashboard.php' ? 'active' : '' ?>">📋 Enrollment</a>
        <a href="student_records.php" class="menu-item <?= $current === 'student_records.php' ? 'active' : '' ?>">🎓 Student Records</a>
        <a href="#" class="menu-item">💰 Payment Tracking</a>
        <a href="#" class="menu-item">📚 Section & Schedule</a>
        <a href="#" class="menu-item">📑 Reports</a>
        <a href="#" class="menu-item">⚙️ Maintenance</a>
        <a href="#" class="menu-item">🔐 Security / User</a>
        <a href="#" class="menu-item">❓ Help</a>
    </nav>
    <form method="POST" action="login.php">
        <button class="logout-btn" type="submit" name="logout">Logout</button>
    </form>
</aside>
