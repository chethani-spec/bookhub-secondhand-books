<?php
// Show ALL errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>BookHub Debug</title>";
echo "<style>body{font-family:Arial;padding:20px;} h2{color:#667eea;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";

echo "<h1>🔍 BookHub Debug Report</h1>";
echo "<hr>";

// Test 1: PHP Version
echo "<h2>Test 1: PHP Environment</h2>";
echo "<div class='success'>✅ PHP is working!</div>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . getcwd() . "<br><br>";

// Test 2: Check required files
echo "<h2>Test 2: File Structure Check</h2>";
$required_files = [
    'includes/config.php',
    'includes/header.php', 
    'includes/footer.php',
    'css/style.css',
    'js/validation.js',
    'index.php',
    'login.php',
    'register.php'
];

$all_exist = true;
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ $file</div>";
    } else {
        echo "<div class='error'>❌ $file MISSING!</div>";
        $all_exist = false;
    }
}
echo "<br>";

// Test 3: Load config.php
echo "<h2>Test 3: Loading config.php</h2>";
try {
    if (file_exists('includes/config.php')) {
        require_once 'includes/config.php';
        echo "<div class='success'>✅ config.php loaded successfully</div><br>";
        
        // Test 4: Database connection
        echo "<h2>Test 4: Database Connection</h2>";
        if (isset($conn) && $conn) {
            echo "<div class='success'>✅ Database connected!</div>";
            echo "Database Name: " . DB_NAME . "<br>";
            echo "Database Host: " . DB_HOST . "<br><br>";
            
            // Test 5: Check tables
            echo "<h2>Test 5: Database Tables</h2>";
            $tables_result = mysqli_query($conn, "SHOW TABLES");
            if ($tables_result) {
                echo "<div class='success'>✅ Tables found:</div>";
                while ($row = mysqli_fetch_row($tables_result)) {
                    // Count rows in each table
                    $table_name = $row[0];
                    $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table_name`");
                    $count = mysqli_fetch_assoc($count_result)['count'];
                    echo "- <strong>$table_name</strong> ($count rows)<br>";
                }
                echo "<br>";
            } else {
                echo "<div class='error'>❌ Error: " . mysqli_error($conn) . "</div><br>";
            }
        } else {
            echo "<div class='error'>❌ Database connection failed!</div>";
            echo "Error: " . mysqli_connect_error() . "<br><br>";
        }
    } else {
        echo "<div class='error'>❌ config.php not found!</div><br>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error loading config.php:</div>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 6: Try to simulate index.php load
echo "<h2>Test 6: Simulating index.php Load</h2>";
if (file_exists('index.php')) {
    echo "<div class='success'>✅ index.php exists</div>";
    
    // Check file permissions
    if (is_readable('index.php')) {
        echo "<div class='success'>✅ index.php is readable</div>";
        
        // Try to read first few lines
        $lines = file('index.php', FILE_IGNORE_NEW_LINES);
        if ($lines) {
            echo "<div class='success'>✅ Can read index.php content</div>";
            echo "First line: <code>" . htmlspecialchars($lines[0]) . "</code><br>";
            
            // Check for PHP opening tag
            if (trim($lines[0]) === '<?php') {
                echo "<div class='success'>✅ PHP opening tag correct</div>";
            } else {
                echo "<div class='warning'>⚠️ First line is not '<?php'</div>";
            }
        }
    } else {
        echo "<div class='error'>❌ index.php is not readable</div>";
    }
    echo "<br>";
} else {
    echo "<div class='error'>❌ index.php not found!</div><br>";
}

// Test 7: Check header.php
echo "<h2>Test 7: Checking header.php</h2>";
if (file_exists('includes/header.php')) {
    echo "<div class='success'> header.php exists</div>";
    
    // Try to include header (in output buffer to catch errors)
    ob_start();
    try {
        include 'includes/header.php';
        $header_output = ob_get_clean();
        echo "<div class='success'> header.php loads without errors</div>";
        echo "<div class='warning'>Note: Header HTML captured (not displayed to avoid breaking this page)</div>";
    } catch (Exception $e) {
        ob_end_clean();
        echo "<div class='error'> Error in header.php: " . $e->getMessage() . "</div>";
    }
    echo "<br>";
} else {
    echo "<div class='error'> header.php not found!</div><br>";
}

// Summary
echo "<hr>";
echo "<h2>📊 Summary</h2>";
if ($all_exist && isset($conn) && $conn) {
    echo "<div class='success'><strong>✅ All basic checks passed!</strong></div>";
    echo "<p>If website still shows error, the issue might be in:</p>";
    echo "<ul>";
    echo "<li>header.php or footer.php content</li>";
    echo "<li>CSS/JS file paths</li>";
    echo "<li>Session handling</li>";
    echo "</ul>";
    echo "<p><a href='index.php' style='background:#667eea;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Try Loading index.php →</a></p>";
} else {
    echo "<div class='error'><strong>❌ Issues found! Check errors above.</strong></div>";
}

echo "</body></html>";
?>