<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize($_POST['order_status']);
    
    $update_query = "UPDATE orders SET order_status = '$new_status' WHERE id = $order_id";
    if (mysqli_query($conn, $update_query)) {
        $success = "Order status updated successfully!";
    } else {
        $error = "Failed to update order status.";
    }
}

$query = "SELECT o.*, b.title, b.cover_image, b.price, 
          buyer.username as buyer_name, buyer.phone as buyer_phone, buyer.email as buyer_email,
          seller.username as seller_name 
          FROM orders o 
          JOIN books b ON o.book_id = b.id 
          JOIN users buyer ON o.buyer_id = buyer.id 
          JOIN users seller ON o.seller_id = seller.id 
          ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - BooksHub Admin</title>
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
        <h2 style="color: #667eea; margin-bottom: 2rem;">📦 Manage All Orders</h2>
        
        <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: 2rem; display: inline-block;">
            ← Back to Dashboard
        </a>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Book</th>
                            <th>Buyer</th>
                            <th>Seller</th>
                            <th>Delivery Address</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <?php if ($order['cover_image']): ?>
                                            <img src="../uploads/books/<?php echo htmlspecialchars($order['cover_image']); ?>" 
                                                 style="width: 50px; height: 70px; object-fit: cover; border-radius: 5px;">
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($order['title']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['buyer_name']); ?></strong><br>
                                    <small>📧 <?php echo htmlspecialchars($order['buyer_email']); ?></small><br>
                                    <?php if ($order['buyer_phone']): ?>
                                        <small>📱 <?php echo htmlspecialchars($order['buyer_phone']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($order['seller_name']); ?></td>
                                <td>
                                    <div style="max-width: 200px; word-wrap: break-word;">
                                        <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                                    </div>
                                </td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td><strong>Rs. <?php echo number_format($order['total_price'], 2); ?></strong></td>
                                <td>
                                    <?php 
                                    $payment_methods = [
                                        'cash_on_delivery' => '💵 COD',
                                        'bank_transfer' => '🏦 Bank',
                                        'card' => '💳 Card'
                                    ];
                                    echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'pending' => '#ffa500',
                                        'confirmed' => '#4dabf7',
                                        'delivered' => '#51cf66',
                                        'cancelled' => '#ff6b6b'
                                    ];
                                    $color = $status_colors[$order['order_status']];
                                    ?>
                                    <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline-block;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="order_status" class="form-control" style="margin-bottom: 0.5rem; min-width: 120px; font-size: 0.85rem;">
                                            <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-small btn-edit" style="width: 100%;">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; background: #f9f9f9; border-radius: 10px;">
                <p style="font-size: 1.2rem; color: #666;">📦 No orders yet</p>
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