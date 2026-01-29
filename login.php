<?php
require_once 'includes/config.php';

// If already logged in, redirect to home
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if ($user['user_type'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Login to BookHub</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" id="loginForm">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <button type="submit" class="btn-submit">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 1rem;">
        Don't have an account? <a href="register.php" style="color: #667eea;">Register here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>