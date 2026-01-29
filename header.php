<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get cart count if user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $cart_query = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id = " . $_SESSION['user_id']);
    $cart_count = mysqli_fetch_assoc($cart_query)['total'] ?? 0;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BooksHub - Buy & Sell Second Hand Books</title>
    
    <!-- CSS - Try multiple paths to ensure loading -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Google Fonts for better typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* Critical CSS - Loads immediately for instant styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
        }
        
        /* Ensure CSS loads indicator */
        .css-loaded-check {
            display: none;
        }

.cart-icon {
            position: relative;
            display: inline-block;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 900;
            box-shadow: 0 2px 8px rgba(240, 147, 251, 0.4);
            animation: pulse 2s infinite;
        }
 
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .nav-menu a.cart-link {
            position: relative;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-menu a.cart-link:hover .cart-icon {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?php echo BASE_URL; ?>">📚 BookHub</a>
            </div>
            <ul class="nav-menu">
                <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>books.php">Browse Books</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo BASE_URL; ?>sell_book.php">Sell a Book</a></li>
                    <li><a href="<?php echo BASE_URL; ?>my_books.php">My Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>my_orders.php">My Orders</a></li>
                    
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>cart.php" class="cart-link">
                            <span class="cart-icon">
                                🛒
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </span>
                            Cart
                        </a>
                    </li>
                    <li class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php" class="btn-login">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="main-content">