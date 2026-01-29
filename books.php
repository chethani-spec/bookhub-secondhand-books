<?php
require_once 'includes/config.php';
include 'includes/header.php';

// Get filter parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT b.*, u.username as seller_name 
          FROM books b 
          JOIN users u ON b.seller_id = u.id 
          WHERE b.status = 'approved'";

if (!empty($search)) {
    $query .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.isbn LIKE '%$search%')";
}

if (!empty($category)) {
    $query .= " AND b.category = '$category'";
}

$query .= " ORDER BY b.created_at DESC";

$result = mysqli_query($conn, $query);

// Get categories
$cat_query = "SELECT DISTINCT category FROM books WHERE status = 'approved' AND category IS NOT NULL";
$cat_result = mysqli_query($conn, $cat_query);
?>

<div class="container">
    <div style="background: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem;">
        <h2 style="color: #667eea; margin-bottom: 1.5rem;">Browse Books</h2>
        
        <!-- Search and Filter -->
        <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <input type="text" 
                   name="search" 
                   placeholder="Search by title, author, or ISBN..." 
                   class="form-control" 
                   style="flex: 1; min-width: 250px;"
                   value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="category" class="form-control" style="width: 200px;">
                <option value="">All Categories</option>
                <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                    <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                            <?php echo ($category === $cat['category']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="books.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="cards-grid">
            <?php while ($book = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <?php if ($book['cover_image']): ?>
                     <img src="uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
     alt="<?php echo htmlspecialchars($book['title']); ?>" 
     class="card-image" 
     loading="lazy">  
                    <?php else: ?>
                        <div class="card-image"></div>
                    <?php endif; ?>
                    
                    <div class="card-content">
                        <h3 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="card-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                        
                        <?php if ($book['category']): ?>
                            <p style="color: #999; font-size: 0.9rem;">
                                📚 <?php echo htmlspecialchars($book['category']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <span class="card-condition"><?php echo htmlspecialchars($book['condition_type']); ?></span>
                        <p class="card-price">Rs. <?php echo number_format($book['price'], 2); ?></p>
                        
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">
                            Seller: <?php echo htmlspecialchars($book['seller_name']); ?>
                        </p>
                        
                        <?php if ($book['stock_quantity'] > 0): ?>
                            <a href="book_details.php?id=<?php echo $book['id']; ?>" 
                               class="btn btn-primary" 
                               style="width: 100%; text-align: center; display: block;">
                                View Details
                            </a>
                        <?php else: ?>
                            <span style="color: #ff6b6b; font-weight: bold; display: block; text-align: center;">
                                Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; background: white; padding: 3rem; border-radius: 15px;">
            <p style="font-size: 1.2rem; color: #666;">No books found matching your criteria.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>