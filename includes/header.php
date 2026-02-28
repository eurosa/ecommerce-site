<?php
// Make sure session is started and auth functions are available
require_once 'includes/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <a href="index.php"><?php echo $company['company_name']; ?></a>
                </div>
                
                <!-- Navigation Menu -->
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                
                <!-- User and Cart Section -->
                <div class="nav-user-cart">
                    <?php if (isLoggedIn()): ?>
                    <!-- User Dropdown for Logged-in Users -->
                    <div class="nav-user-dropdown">
                        <a href="javascript:void(0)" class="nav-user">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-content">
                            <a href="profile.php">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="my-orders.php">
                                <i class="fas fa-shopping-bag"></i> My Orders
                            </a>
                            <a href="profile.php#password">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="logout-link">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Login/Register for Guests -->
                    <a href="login.php" class="nav-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="nav-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                    <?php endif; ?>
                    
                    <!-- Shopping Cart -->
                    <a href="cart.php" class="nav-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">
                            <?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                </div>
                
                <!-- Mobile Menu Button (Hamburger) -->
                <div class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </nav>
    </header>
    <main>