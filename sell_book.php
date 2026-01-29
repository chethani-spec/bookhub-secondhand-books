<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seller_id = $_SESSION['user_id'];
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $isbn = sanitize($_POST['isbn']);
    $category = sanitize($_POST['category']);
    $condition_type = sanitize($_POST['condition_type']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : NULL;
    $stock_quantity = intval($_POST['stock_quantity']);
    
    // Handle image upload
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['cover_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/books/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $cover_image = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $cover_image;
            
            if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid image format. Please upload JPG or PNG.';
        }
    }
    
    if (empty($error)) {
        $original_price_sql = $original_price !== NULL ? $original_price : 'NULL';
        
        $query = "INSERT INTO books (seller_id, title, author, isbn, category, condition_type, description, price, original_price, cover_image, stock_quantity) 
                 VALUES ($seller_id, '$title', '$author', '$isbn', '$category', '$condition_type', '$description', $price, $original_price_sql, '$cover_image', $stock_quantity)";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Book listed successfully! It will be visible after admin approval.';
        } else {
            $error = 'Failed to list book. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container" style="max-width: 700px;">
    <h2>Sell Your Book</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" id="bookForm">
        <div class="form-group">
            <label for="title">Book Title </label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="author">Author </label>
            <input type="text" id="author" name="author" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="isbn">ISBN ( International Standard Book Number )</label>
            <input type="text" id="isbn" name="isbn" class="form-control" placeholder="978-0-123456-78-9">
        </div>
        
        <div class="form-group">
            <label for="category">Category </label>
            <select id="category" name="category" class="form-control" required>
                <option value="">Select Category</option>
                <option value="Fiction">Fiction</option>
                <option value="Academic">Academic</option>
                <option value="Science">Science</option>
                <option value="Business">Business</option>
                <option value="Self-Help">Self-Help</option>
                <option value="Technology">Technology</option>
                <option value="History">History</option>
                <option value="Biography">Biography</option>
                <option value="Children">Children's Books</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="condition_type">Condition </label>
            <select id="condition_type" name="condition_type" class="form-control" required>
                <option value="New">New</option>
                <option value="Like New">Like New</option>
                <option value="Good">Good</option>
                <option value="Fair">Fair</option>
                <option value="Poor">Poor</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description">Description </label>
            <textarea id="description" name="description" class="form-control" required placeholder="Describe the book's condition, any highlights, etc."></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="price">Selling Price (Rs.) </label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="original_price">Original Price (Rs.)</label>
                <input type="number" id="original_price" name="original_price" class="form-control" step="0.01" min="0">
            </div>
        </div>
        
        <div class="form-group">
            <label for="stock_quantity">Quantity Available </label>
            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="1" value="1" required>
        </div>
        
        <div class="form-group">
            <label for="cover_image">Book Cover Image</label>
            <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
            <small style="color: #666;">Accepted formats: JPG, PNG (Max 5MB)</small>
        </div>
        
        <button type="submit" class="btn-submit">List Book for Sale</button>
    </form>
    
    <p style="text-align: center; margin-top: 1rem;">
        <a href="my_books.php" style="color: #667eea;">View My Books</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>