<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Remove from cart
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    $success = 'Item removed from cart!';
}

// Handle Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        $cart_id = intval($cart_id);
        $qty = intval($qty);
        if ($qty > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity = $qty WHERE id = $cart_id AND user_id = $user_id");
        }
    }
    $success = 'Cart updated successfully!';
}

// Get cart items
$query = "SELECT c.*, b.title, b.price, b.cover_image, b.stock_quantity, b.seller_id, u.username as seller_name
          FROM cart c
          JOIN books b ON c.book_id = b.id
          JOIN users u ON b.seller_id = u.id
          WHERE c.user_id = $user_id
          ORDER BY c.added_at DESC";
$result = mysqli_query($conn, $query);

// Calculate totals
$subtotal = 0;
$cart_items = [];
while ($item = mysqli_fetch_assoc($result)) {
    $item['item_total'] = $item['price'] * $item['quantity'];
    $subtotal += $item['item_total'];
    $cart_items[] = $item;
}

include 'includes/header.php';
?>

<style>
    .cart-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 2rem;
    }
    
    .cart-box {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .cart-title {
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 2.5rem;
        font-weight: 900;
        margin-bottom: 2rem;
    }
    
    .cart-item {
        display: grid;
        grid-template-columns: 120px 1fr 150px 150px 100px;
        gap: 2rem;
        align-items: center;
        padding: 2rem;
        background: #fafafa;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        transition: all 0.3s;
    }
    
    .cart-item:hover {
        background: #f0f4ff;
        transform: translateX(10px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
    }
    
    .cart-item-image {
        width: 100px;
        height: 140px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .cart-item-details h3 {
        color: #333;
        font-size: 1.3rem;
        margin-bottom: 0.5rem;
    }
    
    .cart-item-details p {
        color: #666;
        margin: 0.3rem 0;
    }
    
    .qty-input {
        width: 80px;
        padding: 0.8rem;
        border: 2px solid #667eea;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 700;
        text-align: center;
    }
    
    .item-price {
        font-size: 1.5rem;
        font-weight: 900;
        color: #667eea;
    }
    
    .btn-remove {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
        transition: all 0.3s;
    }
    
    .btn-remove:hover {
        background: #f03e3e;
        transform: scale(1.05);
    }
    
    .cart-summary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 2.5rem;
        border-radius: 15px;
        color: white;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }
    
    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid rgba(255, 255, 255, 0.3);
        font-size: 1.8rem;
        font-weight: 900;
    }
    
    .btn-checkout {
        width: 100%;
        padding: 1.2rem;
        background: white;
        color: #667eea;
        border: none;
        border-radius: 12px;
        font-size: 1.2rem;
        font-weight: 700;
        cursor: pointer;
        margin-top: 1.5rem;
        transition: all 0.3s;
    }
    
    .btn-checkout:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 255, 255, 0.3);
    }
    
    .empty-cart {
        text-align: center;
        padding: 5rem 2rem;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 15px;
    }
    
    .empty-cart-icon {
        font-size: 6rem;
        margin-bottom: 2rem;
    }
</style>

<div class="cart-container">
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="cart-box">
        <h1 class="cart-title">🛒 Shopping Cart</h1>
        
        <?php if (count($cart_items) > 0): ?>
            <form method="POST" action="">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div>
                            <?php if ($item['cover_image']): ?>
                                <img src="uploads/books/<?php echo htmlspecialchars($item['cover_image']); ?>" 
                                     class="cart-item-image"
                                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <div style="width: 100px; height: 140px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;"></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><strong>Seller:</strong> <?php echo htmlspecialchars($item['seller_name']); ?></p>
                            <p><strong>Price:</strong> Rs. <?php echo number_format($item['price'], 2); ?></p>
                            <?php if ($item['quantity'] > $item['stock_quantity']): ?>
                                <p style="color: #ff6b6b; font-weight: bold;">
                                    ⚠ Only <?php echo $item['stock_quantity']; ?> available!
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Quantity</label>
                            <input type="number" 
                                   name="quantity[<?php echo $item['id']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>"
                                   min="1"
                                   max="<?php echo $item['stock_quantity']; ?>"
                                   class="qty-input">
                        </div>
                        
                        <div class="item-price">
                            Rs. <?php echo number_format($item['item_total'], 2); ?>
                        </div>
                        
                        <div>
                            <a href="?remove=<?php echo $item['id']; ?>" 
                               class="btn-remove"
                               onclick="return confirm('Remove this item from cart?')">
                                🗑️ Remove
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 2rem;">
                    <button type="submit" name="update_cart" class="btn btn-secondary">
                        Update Cart
                    </button>
                </div>
            </form>
            
            <!-- Cart Summary -->
            <div style="margin-top: 3rem;">
                <div class="cart-summary">
                    <h2 style="margin-bottom: 2rem;">Order Summary</h2>
                    
                    <div class="summary-row">
                        <span>Items (<?php echo count($cart_items); ?>):</span>
                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Delivery:</span>
                        <span>FREE</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total:</span>
                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn-checkout">
                        Proceed to Checkout →
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2 style="color: #667eea; font-size: 2rem; margin-bottom: 1rem;">Your Cart is Empty</h2>
                <p style="color: #666; font-size: 1.2rem; margin-bottom: 2rem;">
                    Add some books to get started!
                </p>
                <a href="books.php" class="btn btn-primary">
                    Browse Books
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>