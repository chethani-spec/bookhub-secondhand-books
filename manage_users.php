<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    if ($user_id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
    }
    redirect('manage_users.php');
}

$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM books WHERE seller_id = u.id) as total_books,
          (SELECT COUNT(*) FROM orders WHERE buyer_id = u.id) as total_purchases
          FROM users u 
          ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - BooksHub Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                <li><a href="<?php echo BASE_URL; ?>sell_book.php">Sell a Book</a></li>
                <li><a href="<?php echo BASE_URL; ?>my_books.php">My Books</a></li>
                <li><a href="<?php echo BASE_URL; ?>my_orders.php">My Orders</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin Panel</a></li>
                <li class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </li>
                <li><a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    <main class="main-content">

<div class="container">
    <div class="table-container">
        <h2 style="color: #667eea; margin-bottom: 2rem;">👥 Manage Users</h2>
        
        <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: 2rem; display: inline-block;">
            ← Back to Dashboard
        </a>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>User Type</th>
                        <th>Books Listed</th>
                        <th>Purchases</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span style="color: #667eea; font-size: 0.8rem;">(You)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                $type_colors = [
                                    'buyer' => '#4dabf7',
                                    'seller' => '#51cf66',
                                    'admin' => '#ff6b6b'
                                ];
                                $color = $type_colors[$user['user_type']];
                                ?>
                                <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                    <?php echo ucfirst($user['user_type']); ?>
                                </span>
                            </td>
                            <td><?php echo $user['total_books']; ?></td>
                            <td><?php echo $user['total_purchases']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       class="btn-small btn-delete"
                                       onclick="return confirm('Delete this user? This will also delete all their books and orders.')">Delete</a>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 0.9rem;">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666;">No users found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</main>
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>📚 Book Hub</h3>
                <p>Your trusted platform for buying and selling second-hand books.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>books.php">Browse Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>sell_book.php">Sell Books</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p>Email: info@bookhub.com</p>
                <p>Phone: +94 77 123 4567</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 BookHub. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>