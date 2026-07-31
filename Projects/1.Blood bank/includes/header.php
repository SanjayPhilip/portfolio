<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

<header>
    <div class="container">
        <nav class="navbar">
            <a href="index.php" class="logo">E <span>Blood Connect</span></a>
            
            <div class="hamburger">
                <i class="fas fa-bars"></i>
            </div>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="request_blood.php">Request Blood</a></li>
                <li><a href="donate.php">Donate</a></li>
                <li><a href="blood_bank.php">Blood Bank</a></li>
                <li><a href="about.php">About</a></li>
                
                <?php if($isLoggedIn): ?>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="profile.php">My Profile</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn primary-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn secondary-btn">Login</a></li>
                    <li><a href="register.php" class="btn primary-btn">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header> 