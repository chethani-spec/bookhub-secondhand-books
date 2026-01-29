<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Loading Test</title>
    
    <!-- Try loading CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background: #f5f5f5;
        }
        
        .test-box {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        
        .status {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-weight: bold;
        }
        
        .success { background: #d3f9d8; color: #2f9e44; }
        .error { background: #ffe0e0; color: #c92a2a; }
        
        .info-box {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="test-box">
        <h1>🔍 CSS Loading Test</h1>
        <hr style="margin: 1rem 0;">
        
        <h2>Test 1: File Location Check</h2>
        <?php
        $css_file = 'css/style.css';
        if (file_exists($css_file)) {
            $file_size = filesize($css_file);
            echo "<div class='status success'> CSS file exists!</div>";
            echo "<div class='info-box'>";
            echo "📁 File: $css_file<br>";
            echo "📦 Size: " . number_format($file_size) . " bytes (" . round($file_size/1024, 2) . " KB)<br>";
            echo "📝 Expected size: 25-30 KB";
            echo "</div>";
            
            if ($file_size < 1000) {
                echo "<div class='status error'>File is too small! It might be corrupt.</div>";
            }
        } else {
            echo "<div class='status error'>CSS file NOT found at: $css_file</div>";
            echo "<div class='info-box'>";
            echo "Current directory: " . getcwd() . "<br>";
            echo "Looking for: " . realpath('.') . "/$css_file";
            echo "</div>";
        }
        ?>
        
        <h2>Test 2: CSS Content Check</h2>
        <?php
        if (file_exists($css_file)) {
            $css_content = file_get_contents($css_file);
            $has_gradient = strpos($css_content, 'gradient') !== false;
            $has_modern_css = strpos($css_content, 'var(--') !== false;
            
            echo "<div class='info-box'>";
            echo "File contains:<br>";
            echo "- Gradients: " . ($has_gradient ? "Yes" : " No") . "<br>";
            echo "- CSS Variables: " . ($has_modern_css ? "Yes" : "No") . "<br>";
            echo "- Lines: " . count(file($css_file)) . "<br>";
            echo "- Characters: " . strlen($css_content);
            echo "</div>";
            
            if (!$has_gradient || !$has_modern_css) {
                echo "<div class='status error'> CSS file might be old or incomplete!</div>";
            } else {
                echo "<div class='status success'> CSS file looks correct!</div>";
            }
        }
        ?>
        
        <h2>Test 3: Visual Test</h2>
        <p>If CSS is loading, this box should have a colorful gradient:</p>
        <div class="stat-card stat-card-purple" style="padding: 2rem; border-radius: 15px; color: white; text-align: center; margin: 1rem 0;">
            <h3 style="font-size: 3rem; margin: 0;">42</h3>
            <p style="margin: 0;">Test Card</p>
        </div>
        
        <div class='info-box'>
            <strong>Expected:</strong> Purple gradient background with white text<br>
            <strong>If plain:</strong> CSS is NOT loading
        </div>
        
        <h2>Test 4: Path Test</h2>
        <?php
        echo "<div class='info-box'>";
        echo "🌐 Website URL: http://" . $_SERVER['HTTP_HOST'] . "<br>";
        echo "📁 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
        echo "📄 Current Script: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
        echo "🔗 CSS URL should be: http://" . $_SERVER['HTTP_HOST'] . "/css/style.css";
        echo "</div>";
        ?>
        
        <h2>Test 5: Direct CSS Link Test</h2>
        <p>Click this link to check if CSS loads directly:</p>
        <a href="css/style.css" target="_blank" style="display: inline-block; padding: 1rem 2rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
            🔗 Open CSS File
        </a>
        <div class='info-box' style="margin-top: 1rem;">
            <strong>Expected:</strong> CSS code should display<br>
            <strong>If 404 error:</strong> File path is wrong<br>
            <strong>If downloads:</strong> Server config issue
        </div>
        
        <hr style="margin: 2rem 0;">
        
        <h2>📋 Fix Instructions:</h2>
        <ol style="line-height: 2;">
            <li><strong>If CSS file not found:</strong> Upload style.css to htdocs/css/ folder</li>
            <li><strong>If file too small:</strong> Re-upload the correct CSS file</li>
            <li><strong>If file exists but not loading:</strong> Check header.php path</li>
            <li><strong>After fixing:</strong> Clear browser cache (Ctrl + Shift + Delete)</li>
            <li><strong>Test again:</strong> Visit homepage</li>
        </ol>
        
        <a href="index.php" style="display: inline-block; margin-top: 1rem; padding: 1rem 2rem; background: #51cf66; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
            ← Back to Homepage
        </a>
    </div>
</body>
</html>