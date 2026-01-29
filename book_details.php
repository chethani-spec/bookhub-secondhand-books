<?php
require_once 'includes/config.php';

$error = '';
$success = '';

// Get book ID
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isLoggedIn()) {
    $quantity = intval($_POST['quantity']);
    
    // Get book details
    $book_query = "SELECT * FROM books WHERE id = $book_id AND status = 'approved' AND stock_quantity > 0";
    $book_result = mysqli_query($conn, $book_query);
    
    if (mysqli_num_rows($book_result) === 1) {
        $book = mysqli_fetch_assoc($book_result);
        
        if ($quantity > 0 && $quantity <= $book['stock_quantity']) {
            $user_id = $_SESSION['user_id'];
            
            // Check if already in cart
            $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = $user_id AND book_id = $book_id");
            
            if (mysqli_num_rows($check_cart) > 0) {
                // Update quantity
                mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND book_id = $book_id");
                $success = 'Cart updated! Quantity increased.';
            } else {
                // Add new
                mysqli_query($conn, "INSERT INTO cart (user_id, book_id, quantity) VALUES ($user_id, $book_id, $quantity)");
                $success = 'Added to cart successfully!';
            }
        } else {
            $error = 'Invalid quantity selected.';
        }
    } else {
        $error = 'Book not available.';
    }
}

// Handle Direct Order (Buy Now)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now']) && isLoggedIn()) {
    $buyer_id = $_SESSION['user_id'];
    $quantity = intval($_POST['quantity']);
    $delivery_address = sanitize($_POST['delivery_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    if (empty($delivery_address)) {
        $error = 'Please provide a delivery address.';
    } else {
        $book_query = "SELECT * FROM books WHERE id = $book_id AND status = 'approved' AND stock_quantity >= $quantity";
        $book_result = mysqli_query($conn, $book_query);
        
        if (mysqli_num_rows($book_result) === 1) {
            $book = mysqli_fetch_assoc($book_result);
            $total_price = $book['price'] * $quantity;
            $seller_id = $book['seller_id'];
            
            $order_query = "INSERT INTO orders (buyer_id, book_id, seller_id, quantity, total_price, delivery_address, payment_method) 
                           VALUES ($buyer_id, $book_id, $seller_id, $quantity, $total_price, '$delivery_address', '$payment_method')";
            
            if (mysqli_query($conn, $order_query)) {
                $new_stock = $book['stock_quantity'] - $quantity;
                mysqli_query($conn, "UPDATE books SET stock_quantity = $new_stock WHERE id = $book_id");
                
                $success = 'Order placed successfully! Check My Orders for details.';
            } else {
                $error = 'Failed to place order. Please try again.';
            }
        } else {
            $error = 'Book not available or insufficient stock.';
        }
    }
}

// Get book details
$query = "SELECT b.*, u.username as seller_name, u.phone as seller_phone, u.email as seller_email 
          FROM books b 
          JOIN users u ON b.seller_id = u.id 
          WHERE b.id = $book_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    redirect('books.php');
}

$book = mysqli_fetch_assoc($result);

// Update views
mysqli_query($conn, "UPDATE books SET views = views + 1 WHERE id = $book_id");

include 'includes/header.php';
?>

<style>
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.5rem 0;
    }
    
    .quantity-selector label {
        font-weight: 700;
        color: #333;
        font-size: 1.1rem;
    }
    
    .quantity-selector input {
        width: 100px;
        padding: 0.8rem;
        border: 2px solid #667eea;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 700;
        text-align: center;
    }
    
    .quantity-selector button {
        width: 40px;
        height: 40px;
        border: none;
        background: #667eea;
        color: white;
        border-radius: 8px;
        font-size: 1.5rem;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .quantity-selector button:hover {
        background: #5568d3;
        transform: scale(1.1);
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn-add-cart {
        flex: 1;
        padding: 1.2rem;
        background: linear-gradient(135deg, #43e97b, #38f9d7);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-add-cart:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(67, 233, 123, 0.4);
    }
    
    .btn-buy-now {
        flex: 1;
        padding: 1.2rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-buy-now:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .stock-indicator {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    .stock-high {
        background: linear-gradient(135deg, #d3f9d8, #b2f2bb);
        color: #2f9e44;
    }
    
    .stock-low {
        background: linear-gradient(135deg, #fff4e6, #ffe8cc);
        color: #f76707;
    }
    
    .stock-out {
        background: linear-gradient(135deg, #ffe0e0, #ffc9c9);
        color: #c92a2a;
    }
</style>

<div class="container">
    <div style="background: white; padding: 3rem; border-radius: 15px; margin: 2rem 0;">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <!-- Book Image -->
            <div>
                <?php if ($book['cover_image']): ?>
                    <img src="uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>"
                         style="width: 100%; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <?php else: ?>
                    <div style="width: 100%; height: 500px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 15px;"></div>
                <?php endif; ?>
            </div>
            
            <!-- Book Details -->
            <div>
                <h1 style="color: #667eea; margin-bottom: 1rem;"><?php echo htmlspecialchars($book['title']); ?></h1>
                <h3 style="color: #666; margin-bottom: 2rem;">by <?php echo htmlspecialchars($book['author']); ?></h3>
                
                <div style="margin-bottom: 2rem;">
                    <span class="card-condition"><?php echo htmlspecialchars($book['condition_type']); ?></span>
                    <?php if ($book['category']): ?>
                        <span class="card-condition"><?php echo htmlspecialchars($book['category']); ?></span>
                    <?php endif; ?>
                </div>
                
                <p class="card-price" style="font-size: 2.5rem;">Rs. <?php echo number_format($book['price'], 2); ?></p>
                
                <?php if ($book['original_price']): ?>
                    <p style="color: #999; text-decoration: line-through; margin-bottom: 2rem;">
                        Original Price: Rs. <?php echo number_format($book['original_price'], 2); ?>
                    </p>
                <?php endif; ?>
                
                <!-- Stock Status -->
                <?php
                $stock = $book['stock_quantity'];
                if ($stock > 10) {
                    echo '<span class="stock-indicator stock-high">✓ In Stock (' . $stock . ' available)</span>';
                } elseif ($stock > 0) {
                    echo '<span class="stock-indicator stock-low">⚠ Only ' . $stock . ' left!</span>';
                } else {
                    echo '<span class="stock-indicator stock-out">✗ Out of Stock</span>';
                }
                ?>
                
                <div style="margin: 2rem 0;">
                    <h3 style="color: #333;">Description</h3>
                    <p style="color: #666; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>
                
                <?php if ($book['isbn']): ?>
                    <p style="margin-bottom: 1rem;"><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
                <?php endif; ?>
                
                <p style="margin-bottom: 1rem;"><strong>Views:</strong> <?php echo $book['views']; ?></p>
                <p style="margin-bottom: 2rem;"><strong>Seller:</strong> <?php echo htmlspecialchars($book['seller_name']); ?></p>
                
                <!-- Quantity Selector & Cart Actions -->
                <?php if (isLoggedIn() && $book['stock_quantity'] > 0 && $book['seller_id'] != $_SESSION['user_id']): ?>
                    
                    <form method="POST" action="" id="cartForm">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <button type="button" onclick="decreaseQty()">-</button>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $book['stock_quantity']; ?>" 
                                   readonly>
                            <button type="button" onclick="increaseQty()">+</button>
                            <span style="color: #666; font-size: 0.9rem;">
                                (Max: <?php echo $book['stock_quantity']; ?>)
                            </span>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="submit" name="add_to_cart" class="btn-add-cart">
                                🛒 Add to Cart
                            </button>
                            <button type="button" onclick="showBuyNowForm()" class="btn-buy-now">
                                ⚡ Buy Now
                            </button>
                        </div>
                    </form>
                    
                    <!-- Buy Now Form (Hidden by default) -->
                    <div id="buyNowForm" style="display: none; margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #e0e0e0;">
                        <h3 style="color: #667eea; margin-bottom: 1rem;">💳 Complete Your Order</h3>
                        
                        <form method="POST" action="" id="orderForm">
                            <input type="hidden" name="quantity" id="orderQuantity" value="1">
                            
                            <div class="form-group">
                                <label for="delivery_address">Delivery Address *</label>
                                <textarea id="delivery_address" name="delivery_address" class="form-control" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_method">Payment Method *</label>
                                <select id="payment_method" name="payment_method" class="form-control" required>
                                    <option value="cash_on_delivery">Cash on Delivery</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="card">Card Payment</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="buy_now" class="btn-submit">Place Order Now</button>
                            <button type="button" onclick="hideBuyNowForm()" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">Cancel</button>
                        </form>
                    </div>
                    
                <?php elseif (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-primary" style="width: 100%; text-align: center; display: block;">
                        Login to Order
                    </a>
                <?php elseif ($book['stock_quantity'] <= 0): ?>
                    <p style="color: #ff6b6b; font-weight: bold; text-align: center; padding: 1rem; background: #ffe0e0; border-radius: 10px;">
                        This book is currently out of stock
                    </p>
                <?php elseif ($book['seller_id'] == $_SESSION['user_id']): ?>
                    <p style="color: #4dabf7; font-weight: bold; text-align: center; padding: 1rem; background: #e3f2fd; border-radius: 10px;">
                        This is your own listing
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const maxStock = <?php echo $book['stock_quantity']; ?>;

function increaseQty() {
    const qtyInput = document.getElementById('quantity');
    const currentQty = parseInt(qtyInput.value);
    if (currentQty < maxStock) {
        qtyInput.value = currentQty + 1;
        updateOrderQty();
    }
}

function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    const currentQty = parseInt(qtyInput.value);
    if (currentQty > 1) {
        qtyInput.value = currentQty - 1;
        updateOrderQty();
    }
}

function updateOrderQty() {
    const qty = document.getElementById('quantity').value;
    document.getElementById('orderQuantity').value = qty;
}

function showBuyNowForm() {
    updateOrderQty();
    document.getElementById('buyNowForm').style.display = 'block';
    document.getElementById('buyNowForm').scrollIntoView({ behavior: 'smooth' });
}

function hideBuyNowForm() {
    document.getElementById('buyNowForm').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>