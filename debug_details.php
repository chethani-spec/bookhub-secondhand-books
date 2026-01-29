<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

echo "<h2>🔍 Book Order Debug</h2>";
echo "<hr>";

// Check if user is logged in
echo "<h3>1️⃣ User Login Status</h3>";
if (isLoggedIn()) {
    echo "✅ <span style='color: green;'>User is logged in</span><br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . $_SESSION['username'] . "<br>";
    echo "User Type: " . $_SESSION['user_type'] . "<br><br>";
} else {
    echo "❌ <span style='color: red;'>User is NOT logged in</span><br>";
    echo "You need to login first to place orders.<br><br>";
}

// Check books availability
echo "<h3>2️⃣ Available Books Check</h3>";
$books_query = "SELECT b.*, u.username as seller_name 
                FROM books b 
                JOIN users u ON b.seller_id = u.id 
                WHERE b.status = 'approved' AND b.stock_quantity > 0";
$books_result = mysqli_query($conn, $books_query);

if (mysqli_num_rows($books_result) > 0) {
    echo "✅ Found " . mysqli_num_rows($books_result) . " available books<br><br>";
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Seller</th>
            <th>Seller ID</th>
            <th>Test Order Link</th>
          </tr>";
    
    while ($book = mysqli_fetch_assoc($books_result)) {
        echo "<tr>";
        echo "<td>{$book['id']}</td>";
        echo "<td>{$book['title']}</td>";
        echo "<td>{$book['author']}</td>";
        echo "<td>Rs. " . number_format($book['price'], 2) . "</td>";
        echo "<td style='color: green; font-weight: bold;'>{$book['stock_quantity']}</td>";
        echo "<td>{$book['seller_name']}</td>";
        echo "<td>{$book['seller_id']}</td>";
        echo "<td><a href='book_details.php?id={$book['id']}' style='color: #667eea;'>View & Order</a></td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "❌ <span style='color: red;'>No books available for ordering</span><br><br>";
}

// Check if current user can order (not their own books)
if (isLoggedIn()) {
    echo "<h3>3️⃣ Books You Can Order (Not Your Own)</h3>";
    $user_id = $_SESSION['user_id'];
    $orderable_query = "SELECT b.*, u.username as seller_name 
                        FROM books b 
                        JOIN users u ON b.seller_id = u.id 
                        WHERE b.status = 'approved' 
                        AND b.stock_quantity > 0 
                        AND b.seller_id != $user_id";
    $orderable_result = mysqli_query($conn, $orderable_query);
    
    if (mysqli_num_rows($orderable_result) > 0) {
        echo "✅ You can order " . mysqli_num_rows($orderable_result) . " books<br><br>";
        
        echo "<ul>";
        while ($book = mysqli_fetch_assoc($orderable_result)) {
            echo "<li><strong>{$book['title']}</strong> by {$book['author']} - Rs. " . number_format($book['price'], 2) . " - <a href='book_details.php?id={$book['id']}' style='color: #667eea;'>Order Now</a></li>";
        }
        echo "</ul><br>";
    } else {
        echo "⚠️ <span style='color: orange;'>All available books are listed by you. You cannot order your own books.</span><br><br>";
    }
    
    // Check your own books
    echo "<h3>4️⃣ Your Listed Books</h3>";
    $my_books_query = "SELECT * FROM books WHERE seller_id = $user_id";
    $my_books_result = mysqli_query($conn, $my_books_query);
    
    if (mysqli_num_rows($my_books_result) > 0) {
        echo "📚 You have listed " . mysqli_num_rows($my_books_result) . " books<br><br>";
        echo "<ul>";
        while ($book = mysqli_fetch_assoc($my_books_result)) {
            echo "<li>{$book['title']} - Status: <strong>{$book['status']}</strong> - Stock: {$book['stock_quantity']}</li>";
        }
        echo "</ul><br>";
    } else {
        echo "No books listed yet.<br><br>";
    }
}

// Check orders table structure
echo "<h3>5️⃣ Orders Table Structure</h3>";
$table_check = mysqli_query($conn, "DESCRIBE orders");
if ($table_check) {
    echo "✅ Orders table exists<br>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = mysqli_fetch_assoc($table_check)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "❌ Orders table not found!<br><br>";
}

// Check existing orders
echo "<h3>6️⃣ Existing Orders</h3>";
$orders_query = "SELECT COUNT(*) as count FROM orders";
$orders_result = mysqli_query($conn, $orders_query);
$orders_count = mysqli_fetch_assoc($orders_result)['count'];

if ($orders_count > 0) {
    echo "✅ Found $orders_count orders in database<br>";
    
    $recent_orders = mysqli_query($conn, "SELECT o.*, b.title, u.username 
                                           FROM orders o 
                                           JOIN books b ON o.book_id = b.id 
                                           JOIN users u ON o.buyer_id = u.id 
                                           ORDER BY o.order_date DESC 
                                           LIMIT 5");
    
    if (mysqli_num_rows($recent_orders) > 0) {
        echo "<h4>Recent Orders:</h4>";
        echo "<ul>";
        while ($order = mysqli_fetch_assoc($recent_orders)) {
            echo "<li>Order #{$order['id']} - {$order['title']} by {$order['username']} - Status: <strong>{$order['order_status']}</strong></li>";
        }
        echo "</ul>";
    }
} else {
    echo "⚠️ No orders placed yet<br>";
}

echo "<hr>";
echo "<h3>💡 Recommendations:</h3>";
echo "<ol>";

if (!isLoggedIn()) {
    echo "<li><strong style='color: red;'>Login first:</strong> <a href='login.php' style='color: #667eea;'>Go to Login Page</a></li>";
} else {
    $user_id = $_SESSION['user_id'];
    $orderable_count_query = "SELECT COUNT(*) as count FROM books 
                               WHERE status = 'approved' 
                               AND stock_quantity > 0 
                               AND seller_id != $user_id";
    $orderable_count = mysqli_fetch_assoc(mysqli_query($conn, $orderable_count_query))['count'];
    
    if ($orderable_count == 0) {
        echo "<li><strong style='color: orange;'>No books to order:</strong> Either all books are yours, or no approved books with stock available. Try creating another user account to test ordering.</li>";
    } else {
        echo "<li><strong style='color: green;'>You can order books!</strong> Try clicking the 'View & Order' links above.</li>";
    }
}

echo "<li><strong>Test Order Flow:</strong>
    <ul>
        <li>1. Login as buyer</li>
        <li>2. Go to Browse Books or click a book link above</li>
        <li>3. Click 'View Details'</li>
        <li>4. Fill delivery address and select payment method</li>
        <li>5. Click 'Place Order'</li>
    </ul>
</li>";

echo "<li><strong>Check book_details.php:</strong> Make sure the form is showing correctly when you're logged in</li>";

echo "</ol>";

mysqli_close($conn);
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h2, h3, h4 { color: #667eea; }
table { background: white; margin: 10px 0; }
th { background: #667eea; color: white; }
a { text-decoration: none; }
a:hover { text-decoration: underline; }
</style>