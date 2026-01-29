<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get cart items
$query = "SELECT c.*, b.title, b.price, b.cover_image, b.stock_quantity, b.seller_id
          FROM cart c
          JOIN books b ON c.book_id = b.id
          WHERE c.user_id = $user_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    redirect('cart.php');
}

// Calculate totals
$cart_items = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($result)) {
    if ($item['quantity'] > $item['stock_quantity']) {
        $error = 'Some items in your cart exceed available stock. Please update your cart.';
    }
    $item['item_total'] = $item['price'] * $item['quantity'];
    $subtotal += $item['item_total'];
    $cart_items[] = $item;
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $delivery_address = sanitize($_POST['delivery_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    if (empty($delivery_address)) {
        $error = 'Please provide a delivery address.';
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            $all_success = true;
            
            foreach ($cart_items as $item) {
                $book_id = $item['book_id'];
                $quantity = $item['quantity'];
                $total_price = $item['item_total'];
                $seller_id = $item['seller_id'];
                
                // Check stock again
                $stock_check = mysqli_query($conn, "SELECT stock_quantity FROM books WHERE id = $book_id");
                $current_stock = mysqli_fetch_assoc($stock_check)['stock_quantity'];
                
                if ($current_stock < $quantity) {
                    throw new Exception('Insufficient stock for: ' . $item['title']);
                }
                
                // Insert order
                $order_query = "INSERT INTO orders (buyer_id, book_id, seller_id, quantity, total_price, delivery_address, payment_method) 
                               VALUES ($user_id, $book_id, $seller_id, $quantity, $total_price, '$delivery_address', '$payment_method')";
                
                if (!mysqli_query($conn, $order_query)) {
                    throw new Exception('Failed to create order');
                }
                
                // Update stock
                $new_stock = $current_stock - $quantity;
                if (!mysqli_query($conn, "UPDATE books SET stock_quantity = $new_stock WHERE id = $book_id")) {
                    throw new Exception('Failed to update stock');
                }
            }
            
            // Clear cart
            mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");
            
            // Commit transaction
            mysqli_commit($conn);
            
            $success = 'Orders placed successfully! Check My Orders for details.';
            header("refresh:2;url=my_orders.php");
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<style>
    .checkout-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 2rem;
    }
    
    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 3rem;
    }
    
    .checkout-section {
        background: white;
        padding: 2.5rem;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .section-title {
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 2rem;
    }
    
    .order-item {
        display: flex;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #fafafa;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    
    .order-item-image {
        width: 80px;
        height: 110px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .order-item-details h4 {
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .order-item-details p {
        color: #666;
        margin: 0.3rem 0;
        font-size: 0.95rem;
    }
    
    .order-summary {
        position: sticky;
        top: 2rem;
    }
    
    .summary-box {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 2rem;
        border-radius: 15px;
        color: white;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 1.1rem;
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
</style>

<div class="checkout-container">
    <h1 style="background: linear-gradient(135deg, #667eea, #764ba2);
               -webkit-background-clip: text;
               -webkit-text-fill-color: transparent;
               background-clip: text;
               font-size: 3rem;
               font-weight: 900;
               margin-bottom: 2rem;">
        🛍️ Checkout
    </h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="checkout-grid">
        <!-- Left: Order Details & Delivery Form -->
        <div>
            <div class="checkout-section">
                <h2 class="section-title">📦 Order Items</h2>
                
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <div>
                            <?php if ($item['cover_image']): ?>
                                <img src="uploads/books/<?php echo htmlspecialchars($item['cover_image']); ?>" 
                                     class="order-item-image"
                                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="order-item-details">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p><strong>Quantity:</strong> <?php echo $item['quantity']; ?></p>
                            <p><strong>Price:</strong> Rs. <?php echo number_format($item['price'], 2); ?> each</p>
                            <p style="color: #667eea; font-weight: 700; font-size: 1.1rem;">
                                <strong>Subtotal:</strong> Rs. <?php echo number_format($item['item_total'], 2); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="checkout-section" style="margin-top: 2rem;">
                <h2 class="section-title">🚚 Delivery Information</h2>
                
                <form method="POST" action="" id="checkoutForm">
                    <div class="form-group">
                        <label for="delivery_address">Delivery Address *</label>
                        <textarea id="delivery_address" 
                                  name="delivery_address" 
                                  class="form-control" 
                                  rows="4"
                                  required
                                  placeholder="Enter your complete delivery address with city and postal code"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" class="form-control" required>
                            <option value="cash_on_delivery">💵 Cash on Delivery</option>
                            <option value="bank_transfer">🏦 Bank Transfer</option>
                            <option value="card">💳 Card Payment</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        Place Order - Rs. <?php echo number_format($subtotal, 2); ?>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Right: Order Summary -->
        <div>
            <div class="order-summary">
                <div class="summary-box">
                    <h2 style="margin-bottom: 2rem;">Order Summary</h2>
                    
                    <div class="summary-row">
                        <span>Items (<?php echo count($cart_items); ?>):</span>
                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span>FREE</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>Rs. 0.00</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total:</span>
                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.2);">
                        <p style="font-size: 0.9rem; opacity: 0.9; line-height: 1.6;">
                            ✓ Secure checkout<br>
                            ✓ Free delivery<br>
                            ✓ Easy returns
                        </p>
                    </div>
                </div>
                
                <a href="cart.php" class="btn btn-secondary" style="width: 100%; text-align: center; margin-top: 1rem; display: block;">
                    ← Back to Cart
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>