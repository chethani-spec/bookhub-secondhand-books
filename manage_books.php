<?php
require_once '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../index.php');
}

// Handle approve/reject/delete
if (isset($_GET['action']) && isset($_GET['id'])) {
    $book_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        mysqli_query($conn, "UPDATE books SET status = 'approved' WHERE id = $book_id");
    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE books SET status = 'rejected' WHERE id = $book_id");
    } elseif ($action === 'delete') {
        mysqli_query($conn, "DELETE FROM books WHERE id = $book_id");
    }
    
    redirect('manage_books.php');
}

// Get all books
$query = "SELECT b.*, u.username as seller_name 
          FROM books b 
          JOIN users u ON b.seller_id = u.id 
          ORDER BY 
            CASE 
              WHEN b.status = 'pending' THEN 1
              WHEN b.status = 'approved' THEN 2
              WHEN b.status = 'rejected' THEN 3
              ELSE 4
            END,
            b.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - BooksHub Admin</title>
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
        <h2 style="color: #667eea; margin-bottom: 2rem;">📚 Manage Books</h2>
        
        <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: 2rem; display: inline-block;">
            ← Back to Dashboard
        </a>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $book['id']; ?></td>
                            <td>
                                <?php if ($book['cover_image']): ?>
                                    <img src="../uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                         style="width: 50px; height: 70px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 70px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 5px;"></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                <small style="color: #999;">ISBN: <?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['seller_name']); ?></td>
                            <td><?php echo htmlspecialchars($book['category']); ?></td>
                            <td>Rs. <?php echo number_format($book['price'], 2); ?></td>
                            <td>
                                <?php if ($book['stock_quantity'] > 0): ?>
                                    <span style="color: #51cf66;"><?php echo $book['stock_quantity']; ?></span>
                                <?php else: ?>
                                    <span style="color: #ff6b6b;">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_colors = [
                                    'pending' => '#ffa500',
                                    'approved' => '#51cf66',
                                    'rejected' => '#ff6b6b',
                                    'sold' => '#4dabf7'
                                ];
                                $color = $status_colors[$book['status']];
                                ?>
                                <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                    <?php echo ucfirst($book['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="../book_details.php?id=<?php echo $book['id']; ?>" 
                                   class="btn-small btn-edit" 
                                   target="_blank">View</a>
                                
                                <?php if ($book['status'] === 'pending'): ?>
                                    <a href="?action=approve&id=<?php echo $book['id']; ?>" 
                                       class="btn-small btn-approve"
                                       onclick="return confirm('Approve this book?')">Approve</a>
                                    <a href="?action=reject&id=<?php echo $book['id']; ?>" 
                                       class="btn-small btn-delete"
                                       onclick="return confirm('Reject this book?')">Reject</a>
                                <?php endif; ?>
                                
                                <a href="?action=delete&id=<?php echo $book['id']; ?>" 
                                   class="btn-small btn-delete"
                                   onclick="return confirm('Delete this book permanently?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666;">No books found.</p>
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