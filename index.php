<?php
require_once 'includes/config.php';
include 'includes/header.php';

// Get featured books (approved books)
$featured_query = "SELECT b.*, u.username as seller_name 
                   FROM books b 
                   JOIN users u ON b.seller_id = u.id 
                   WHERE b.status = 'approved' 
                   ORDER BY b.views DESC, b.created_at DESC 
                   LIMIT 6";
$featured_result = mysqli_query($conn, $featured_query);
?>

<!-- Hero Section -->
<div class="container">
    <div class="hero">
        <h1>📚 Welcome to BookHub</h1>
        <p>Your trusted marketplace for buying and selling second-hand books</p>
        
        <div class="hero-buttons">
            <a href="books.php" class="btn btn-primary">Browse Books</a>
            <a href="sell_book.php" class="btn btn-secondary">Sell Your Books</a>
        </div>
    </div>
</div>

<!-- Featured Books Section -->
<div class="container">
    <h2 style="color: white; text-align: center; font-size: 2rem; margin-bottom: 2rem;">
        Featured Books
    </h2>
    <!-- Before cards-grid -->
<div style="background: white; border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 40px rgba(0,0,0,0.1); text-align: center;">
    <p style="font-size: 1.2rem; color: #666;">Browse our collection of quality second-hand books!</p>
</div>
    <?php if ($featured_result && mysqli_num_rows($featured_result) > 0): ?>
        <div class="cards-grid">
            <?php while ($book = mysqli_fetch_assoc($featured_result)): ?>
                <div class="card">
                    <?php if ($book['cover_image']): ?>
                        <img src="uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>" 
                             class="card-image"
                             loading="lazy">
                    <?php else: ?>
                        <div class="card-image" style="display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 4rem;">📚</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-content">
                        <h3 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="card-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                        
                        <?php if ($book['category']): ?>
                            <span class="card-condition"><?php echo htmlspecialchars($book['category']); ?></span>
                        <?php endif; ?>
                        <span class="card-condition"><?php echo htmlspecialchars($book['condition_type']); ?></span>
                        
                        <p class="card-price">Rs. <?php echo number_format($book['price'], 2); ?></p>
                        
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">
                            👤 <?php echo htmlspecialchars($book['seller_name']); ?>
                        </p>
                        
                        <?php if ($book['stock_quantity'] > 0): ?>
                            <a href="book_details.php?id=<?php echo $book['id']; ?>" 
                               class="btn btn-primary" 
                               style="width: 100%; text-align: center; display: block; text-decoration: none;">
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
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="books.php" class="btn btn-primary">View All Books →</a>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p style="font-size: 1.5rem; margin-bottom: 1rem;">📚</p>
            <p>No books available yet. Be the first to sell!</p>
            <?php if (isLoggedIn()): ?>
                <a href="sell_book.php" class="btn btn-primary">List Your First Book</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Register to Sell</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Features Section -->
<div class="container" style="margin-top: 4rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
        <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📖</div>
            <h3 style="color: #667eea; margin-bottom: 1rem;">Buy Books</h3>
            <p style="color: #666;">Find great deals on second-hand books from students and readers</p>
        </div>
        
        <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💰</div>
            <h3 style="color: #667eea; margin-bottom: 1rem;">Sell Books</h3>
            <p style="color: #666;">Turn your old books into cash and help others save money</p>
        </div>
        
        <div style="background: white; padding: 2rem; border-radius: 15px; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
            <h3 style="color: #667eea; margin-bottom: 1rem;">Safe & Secure</h3>
            <p style="color: #666;">All listings are verified and transactions are protected</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>