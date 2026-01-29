<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle status update for SELLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize($_POST['order_status']);
    
    // Verify this order belongs to the current user (as seller)
    $verify_query = "SELECT * FROM orders WHERE id = $order_id AND seller_id = $user_id";
    $verify_result = mysqli_query($conn, $verify_query);
    
    if (mysqli_num_rows($verify_result) === 1) {
        $update_query = "UPDATE orders SET order_status = '$new_status' WHERE id = $order_id";
        if (mysqli_query($conn, $update_query)) {
            $success = "Order status updated successfully!";
        } else {
            $error = "Failed to update order status.";
        }
    } else {
        $error = "You don't have permission to update this order.";
    }
}

// Get orders as BUYER
$buyer_query = "SELECT o.*, b.title, b.cover_image, u.username as seller_name 
                FROM orders o 
                JOIN books b ON o.book_id = b.id 
                JOIN users u ON o.seller_id = u.id 
                WHERE o.buyer_id = $user_id 
                ORDER BY o.order_date DESC";
$buyer_result = mysqli_query($conn, $buyer_query);

// Get orders as SELLER
$seller_query = "SELECT o.*, b.title, b.cover_image, u.username as buyer_name, u.phone as buyer_phone, u.email as buyer_email 
                 FROM orders o 
                 JOIN books b ON o.book_id = b.id 
                 JOIN users u ON o.buyer_id = u.id 
                 WHERE o.seller_id = $user_id 
                 ORDER BY o.order_date DESC";
$seller_result = mysqli_query($conn, $seller_query);

include 'includes/header.php';
?>

<div class="container">
    <!-- Orders as Buyer -->
    <div class="table-container">
        <h2 style="color: #667eea; margin-bottom: 2rem;">🛒 My Purchases</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (mysqli_num_rows($buyer_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Book</th>
                        <th>Seller</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($buyer_result)): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <?php if ($order['cover_image']): ?>
                                        <img src="uploads/books/<?php echo htmlspecialchars($order['cover_image']); ?>" 
                                             style="width: 50px; height: 70px; object-fit: cover; border-radius: 5px;">
                                    <?php endif; ?>
                                    <strong><?php echo htmlspecialchars($order['title']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($order['seller_name']); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>Rs. <?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></td>
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
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666;">You haven't made any purchases yet.</p>
                <a href="books.php" class="btn btn-primary">Browse Books</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Orders as Seller -->
    <div class="table-container" style="margin-top: 3rem;">
        <h2 style="color: #667eea; margin-bottom: 2rem;">📦 Sales (Books I Sold)</h2>
        
        <?php if (mysqli_num_rows($seller_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Book</th>
                        <th>Buyer Info</th>
                        <th>Delivery Address</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($seller_result)): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <?php if ($order['cover_image']): ?>
                                        <img src="uploads/books/<?php echo htmlspecialchars($order['cover_image']); ?>" 
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
                            <td>
                                <div style="max-width: 200px; word-wrap: break-word;">
                                    <?php echo nl2br(htmlspecialchars(substr($order['delivery_address'], 0, 50))); ?>...
                                </div>
                            </td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>Rs. <?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></td>
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
                                    <select name="order_status" class="form-control" style="margin-bottom: 0.5rem; min-width: 140px;">
                                        <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-small btn-edit" style="width: 100%;">
                                        Update Status
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666;">You haven't received any orders yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>