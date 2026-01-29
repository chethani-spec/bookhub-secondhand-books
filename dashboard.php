<?php
require_once '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

// Get statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_books = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM books"))['count'];
$pending_books = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM books WHERE status = 'pending'"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as revenue FROM orders WHERE order_status != 'cancelled'"))['revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BooksHub</title>
    
    <!-- CSS - CORRECT PATH for admin folder -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

<style>
    /* Admin Dashboard Specific Styles */
    .admin-container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 2rem;
    }
    
    .admin-box {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        margin-bottom: 2rem;
    }
    
    .page-title {
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 3rem;
        font-weight: 900;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .quick-links {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }
    
    .quick-links .btn {
        flex: 1;
        min-width: 220px;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.1rem;
        text-align: center;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .quick-links .btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .stat-card {
        padding: 2.5rem;
        border-radius: 20px;
        color: white;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        transition: transform 0.6s;
    }
    
    .stat-card:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }
    
    .stat-card:hover::before {
        transform: translate(-25%, -25%);
    }
    
    .stat-card h3 {
        font-size: 4rem;
        margin-bottom: 0.5rem;
        font-weight: 900;
        position: relative;
        z-index: 1;
    }
    
    .stat-card p {
        font-size: 1.3rem;
        opacity: 0.95;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    
    .stat-card-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card-pink { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-card-blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card-green { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .stat-card-orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .stat-card-teal { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
    
    .section-title {
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 2rem;
        font-weight: 800;
        margin: 3rem 0 1.5rem 0;
    }
    
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.8rem;
        margin-top: 1.5rem;
    }
    
    .data-table thead {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }
    
    .data-table th {
        padding: 1.2rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.95rem;
        text-transform: uppercase;
    }
    
    .data-table th:first-child { border-radius: 10px 0 0 10px; }
    .data-table th:last-child { border-radius: 0 10px 10px 0; }
    
    .data-table td {
        padding: 1.2rem;
        background: #fafafa;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .data-table tbody tr {
        transition: all 0.3s;
    }
    
    .data-table tbody tr:hover {
        background: #f0f4ff;
        transform: scale(1.01);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    }
    
    .btn-small {
        padding: 0.5rem 1.2rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.3s;
        display: inline-block;
        margin: 0.2rem;
    }
    
    .btn-edit {
        background: #4dabf7;
        color: white;
    }
    
    .btn-edit:hover {
        background: #339af0;
        transform: translateY(-2px);
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 15px;
        margin: 2rem 0;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        color: #667eea;
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #666;
        font-size: 1.1rem;
    }
</style>

<div class="admin-container">
    <div class="admin-box">
        <h1 class="page-title">📊 Admin Dashboard</h1>
        
        <!-- Quick Action Links -->
        <div class="quick-links">
            <a href="manage_books.php" class="btn">📚 Manage Books</a>
            <a href="manage_users.php" class="btn">👥 Manage Users</a>
            <a href="manage_orders.php" class="btn">📦 Manage Orders</a>
        </div>
        
        <!-- Colorful Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-purple">
                <h3><?php echo $total_users; ?></h3>
                <p>👥 Total Users</p>
            </div>
            
            <div class="stat-card stat-card-pink">
                <h3><?php echo $total_books; ?></h3>
                <p>📚 Total Books</p>
            </div>
            
            <div class="stat-card stat-card-blue">
                <h3><?php echo $pending_books; ?></h3>
                <p>⏳ Pending Approvals</p>
            </div>
            
            <div class="stat-card stat-card-green">
                <h3><?php echo $total_orders; ?></h3>
                <p>📦 Total Orders</p>
            </div>
            
            <div class="stat-card stat-card-orange">
                <h3><?php echo $pending_orders; ?></h3>
                <p>⏰ Pending Orders</p>
            </div>
            
            <div class="stat-card stat-card-teal">
                <h3>Rs. <?php echo number_format($total_revenue, 2); ?></h3>
                <p>💰 Total Revenue</p>
            </div>
        </div>
        
        <!-- Recent Books Awaiting Approval -->
        <h2 class="section-title">📚 Recent Books Awaiting Approval</h2>
        
        <?php
        $recent_books = mysqli_query($conn, "SELECT b.*, u.username as seller_name 
                                             FROM books b 
                                             JOIN users u ON b.seller_id = u.id 
                                             WHERE b.status = 'pending' 
                                             ORDER BY b.created_at DESC 
                                             LIMIT 5");
        ?>
        
        <?php if (mysqli_num_rows($recent_books) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Seller</th>
                        <th>Price</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = mysqli_fetch_assoc($recent_books)): ?>
                        <tr>
                            <td><strong><?php echo $book['id']; ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['seller_name']); ?></td>
                            <td><strong style="color: #667eea;">Rs. <?php echo number_format($book['price'], 2); ?></strong></td>
                            <td><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                            <td>
                                <a href="manage_books.php" class="btn-small btn-edit">Review</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h3>No books awaiting approval</h3>
                <p>New submissions will appear here</p>
            </div>
        <?php endif; ?>
        
        <!-- Recent Orders -->
        <h2 class="section-title">📦 Recent Orders</h2>
        
        <?php
        $recent_orders = mysqli_query($conn, "SELECT o.*, 
                                              b.title as book_title,
                                              buyer.username as buyer_name,
                                              seller.username as seller_name
                                              FROM orders o
                                              JOIN books b ON o.book_id = b.id
                                              JOIN users buyer ON o.buyer_id = buyer.id
                                              JOIN users seller ON o.seller_id = seller.id
                                              ORDER BY o.order_date DESC
                                              LIMIT 5");
        ?>
        
        <?php if (mysqli_num_rows($recent_orders) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Book</th>
                        <th>Buyer</th>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($order['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['seller_name']); ?></td>
                            <td><strong style="color: #667eea;">Rs. <?php echo number_format($order['total_price'], 2); ?></strong></td>
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
                                <a href="manage_orders.php" class="btn-small btn-edit">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <h3>No orders yet</h3>
                <p>Customer orders will appear here</p>
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
<script src="<?php echo BASE_URL; ?>js/validation.js"></script>
</body>
</html>