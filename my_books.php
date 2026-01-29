<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$seller_id = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM books WHERE id = $book_id AND seller_id = $seller_id";
    mysqli_query($conn, $delete_query);
    redirect('my_books.php');
}

// Get seller's books
$query = "SELECT * FROM books WHERE seller_id = $seller_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php';
?>

<div class="container">
    <div class="table-container">
        <h2 style="color: #667eea; margin-bottom: 2rem;">📚 My Listed Books</h2>
        
        <a href="sell_book.php" class="btn btn-primary" style="margin-bottom: 2rem; display: inline-block;">
            ➕ Add New Book
        </a>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <?php if ($book['cover_image']): ?>
                                        <img src="uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                             style="width: 60px; height: 85px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 85px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 8px;"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="font-size: 1.1rem; color: #333;"><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                    <small style="color: #999;">by <?php echo htmlspecialchars($book['author']); ?></small>
                                </td>
                                <td>
                                    <span style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1976d2; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($book['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="font-size: 1.2rem; color: #667eea;">
                                        Rs. <?php echo number_format($book['price'], 2); ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php if ($book['stock_quantity'] > 0): ?>
                                        <span style="color: #51cf66; font-weight: bold; font-size: 1.1rem;">
                                            ✓ <?php echo $book['stock_quantity']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #ff6b6b; font-weight: bold;">✗ Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_styles = [
                                        'pending' => 'background: linear-gradient(135deg, #fff4e6, #ffe8cc); color: #f76707; border: 2px solid #ffd8a8;',
                                        'approved' => 'background: linear-gradient(135deg, #d3f9d8, #b2f2bb); color: #2f9e44; border: 2px solid #8ce99a;',
                                        'rejected' => 'background: linear-gradient(135deg, #ffe0e0, #ffc9c9); color: #c92a2a; border: 2px solid #ffa8a8;',
                                        'sold' => 'background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1976d2; border: 2px solid #74c0fc;'
                                    ];
                                    $style = $status_styles[$book['status']];
                                    ?>
                                    <span style="<?php echo $style; ?> padding: 0.5rem 1.2rem; border-radius: 20px; font-weight: 700; font-size: 0.9rem; display: inline-block;">
                                        <?php echo ucfirst($book['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #666; font-weight: 600;">
                                        👁️ <?php echo $book['views']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <a href="book_details.php?id=<?php echo $book['id']; ?>" 
                                           class="btn-small btn-edit" 
                                           style="text-decoration: none;">
                                            👁️ View
                                        </a>
                                        <a href="?delete=<?php echo $book['id']; ?>" 
                                           class="btn-small btn-delete"
                                           style="text-decoration: none;"
                                           onclick="return confirm('⚠️ Are you sure you want to delete this book?')">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Statistics Summary -->
            <div style="margin-top: 3rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <?php
                // Calculate stats
                mysqli_data_seek($result, 0); // Reset pointer
                $stats = [
                    'total' => 0,
                    'approved' => 0,
                    'pending' => 0,
                    'rejected' => 0,
                    'total_views' => 0
                ];
                
                while ($book = mysqli_fetch_assoc($result)) {
                    $stats['total']++;
                    $stats[$book['status']]++;
                    $stats['total_views'] += $book['views'];
                }
                ?>
                
                <div style="background: linear-gradient(135deg, #667eea, #764ba2); padding: 1.5rem; border-radius: 12px; color: white; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 900;"><?php echo $stats['total']; ?></div>
                    <div style="font-size: 1rem; opacity: 0.9;">Total Books</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #51cf66, #37b24d); padding: 1.5rem; border-radius: 12px; color: white; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 900;"><?php echo $stats['approved']; ?></div>
                    <div style="font-size: 1rem; opacity: 0.9;">Approved</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #ffa500, #ff8800); padding: 1.5rem; border-radius: 12px; color: white; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 900;"><?php echo $stats['pending']; ?></div>
                    <div style="font-size: 1rem; opacity: 0.9;">Pending</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #4dabf7, #339af0); padding: 1.5rem; border-radius: 12px; color: white; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: 900;"><?php echo $stats['total_views']; ?></div>
                    <div style="font-size: 1rem; opacity: 0.9;">Total Views</div>
                </div>
            </div>
            
        <?php else: ?>
            <div style="text-align: center; padding: 4rem; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 15px;">
                <div style="font-size: 5rem; margin-bottom: 1rem;">📚</div>
                <h3 style="color: #667eea; font-size: 2rem; margin-bottom: 1rem;">No Books Listed Yet</h3>
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem;">
                    Start selling by listing your first book!
                </p>
                <a href="sell_book.php" class="btn btn-primary">
                    ➕ List Your First Book
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>