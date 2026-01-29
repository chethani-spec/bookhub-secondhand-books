<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $user_type = sanitize($_POST['user_type']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $insert_query = "INSERT INTO users (username, email, password, full_name, phone, address, user_type) 
                           VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', '$address', '$user_type')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = 'Registration successful! You can now login.';
                // Redirect to login after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Create Account</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" id="registerForm">
        <div class="form-group">
            <label for="username">Username </label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email </label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password </label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" placeholder="0771234567">
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" class="form-control"></textarea>
        </div>
        
        <div class="form-group">
            <label for="user_type">I want to </label>
            <select id="user_type" name="user_type" class="form-control" required>
                <option value="buyer">Buy Books</option>
                <option value="seller">Sell Books</option>
            </select>
        </div>
        
        <button type="submit" class="btn-submit">Register</button>
    </form>
    
    <p style="text-align: center; margin-top: 1rem;">
        Already have an account? <a href="login.php" style="color: #667eea;">Login here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>