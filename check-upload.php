<?php
/**
 * DIAGNOSTIC TOOL - Upload Configuration Checker
 * Simpan file ini sebagai: brew-bakery/check-upload.php
 * Akses via: http://localhost/brew-bakery/check-upload.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.success { color: #28a745; }
.error { color: #dc3545; }
.warning { color: #ffc107; }
h2 { color: #333; border-bottom: 2px solid #8B6F47; padding-bottom: 10px; }
pre { background: #f8f9fa; padding: 10px; border-left: 3px solid #8B6F47; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
table td { padding: 8px; border-bottom: 1px solid #eee; }
table td:first-child { font-weight: bold; width: 250px; }
</style>";

echo "<h1>🔍 Upload Configuration Diagnostic Tool</h1>";

// ============================================
// 1. PHP Upload Settings
// ============================================
echo "<div class='box'>";
echo "<h2>1. PHP Upload Configuration</h2>";
echo "<table>";
echo "<tr><td>file_uploads</td><td>" . (ini_get('file_uploads') ? '<span class="success">✓ Enabled</span>' : '<span class="error">✗ Disabled</span>') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>max_file_uploads</td><td>" . ini_get('max_file_uploads') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . " seconds</td></tr>";
echo "<tr><td>max_input_time</td><td>" . ini_get('max_input_time') . " seconds</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>upload_tmp_dir</td><td>" . (ini_get('upload_tmp_dir') ?: 'Default (system temp)') . "</td></tr>";
echo "</table>";
echo "</div>";

// ============================================
// 2. Directory Checks
// ============================================
echo "<div class='box'>";
echo "<h2>2. Directory Structure & Permissions</h2>";

$base_dir = __DIR__;
$upload_dir = $base_dir . '/uploads';
$articles_dir = $upload_dir . '/articles';

echo "<table>";

// Check base directory
echo "<tr><td>Base Directory</td><td>";
echo "<code>$base_dir</code><br>";
echo is_dir($base_dir) ? '<span class="success">✓ Exists</span>' : '<span class="error">✗ Not Found</span>';
echo "</td></tr>";

// Check uploads directory
echo "<tr><td>Uploads Directory</td><td>";
echo "<code>$upload_dir</code><br>";
if (is_dir($upload_dir)) {
    echo '<span class="success">✓ Exists</span><br>';
    echo "Permission: " . substr(sprintf('%o', fileperms($upload_dir)), -4);
    echo is_writable($upload_dir) ? ' <span class="success">(Writable)</span>' : ' <span class="error">(Not Writable)</span>';
} else {
    echo '<span class="error">✗ Not Found</span><br>';
    echo '<span class="warning">⚠ Creating directory...</span><br>';
    if (@mkdir($upload_dir, 0755, true)) {
        echo '<span class="success">✓ Created successfully</span>';
    } else {
        echo '<span class="error">✗ Failed to create</span>';
    }
}
echo "</td></tr>";

// Check articles directory
echo "<tr><td>Articles Directory</td><td>";
echo "<code>$articles_dir</code><br>";
if (is_dir($articles_dir)) {
    echo '<span class="success">✓ Exists</span><br>';
    echo "Permission: " . substr(sprintf('%o', fileperms($articles_dir)), -4);
    echo is_writable($articles_dir) ? ' <span class="success">(Writable)</span>' : ' <span class="error">(Not Writable)</span>';
} else {
    echo '<span class="error">✗ Not Found</span><br>';
    echo '<span class="warning">⚠ Creating directory...</span><br>';
    if (@mkdir($articles_dir, 0755, true)) {
        echo '<span class="success">✓ Created successfully</span>';
    } else {
        echo '<span class="error">✗ Failed to create</span>';
    }
}
echo "</td></tr>";

echo "</table>";
echo "</div>";

// ============================================
// 3. Write Test
// ============================================
echo "<div class='box'>";
echo "<h2>3. Write Permission Test</h2>";

if (is_dir($articles_dir)) {
    $test_file = $articles_dir . '/test_' . time() . '.txt';
    $test_content = 'Upload test at ' . date('Y-m-d H:i:s');
    
    if (@file_put_contents($test_file, $test_content)) {
        echo '<span class="success">✓ Write test PASSED</span><br>';
        echo "Test file created: <code>$test_file</code><br>";
        
        // Clean up
        if (@unlink($test_file)) {
            echo '<span class="success">✓ Test file deleted successfully</span>';
        } else {
            echo '<span class="warning">⚠ Could not delete test file</span>';
        }
    } else {
        echo '<span class="error">✗ Write test FAILED</span><br>';
        echo '<span class="error">Cannot write to articles directory!</span><br>';
        echo "<strong>Solution:</strong> Run this command:<br>";
        echo "<pre>chmod -R 755 $upload_dir</pre>";
    }
} else {
    echo '<span class="error">✗ Articles directory does not exist</span>';
}
echo "</div>";

// ============================================
// 4. Upload Form Test
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_upload'])) {
    echo "<div class='box'>";
    echo "<h2>4. Upload Test Result</h2>";
    
    echo "<pre>";
    echo "File Information:\n";
    echo "- Name: " . $_FILES['test_upload']['name'] . "\n";
    echo "- Type: " . $_FILES['test_upload']['type'] . "\n";
    echo "- Size: " . $_FILES['test_upload']['size'] . " bytes\n";
    echo "- Tmp Name: " . $_FILES['test_upload']['tmp_name'] . "\n";
    echo "- Error: " . $_FILES['test_upload']['error'];
    
    $error_messages = [
        UPLOAD_ERR_OK => 'No error',
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
        UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload'
    ];
    
    echo " (" . ($error_messages[$_FILES['test_upload']['error']] ?? 'Unknown error') . ")\n";
    echo "</pre>";
    
    if ($_FILES['test_upload']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['test_upload']['name'], PATHINFO_EXTENSION));
        $new_name = 'test_' . time() . '.' . $ext;
        $destination = $articles_dir . '/' . $new_name;
        
        echo "<strong>Attempting to move file...</strong><br>";
        echo "From: <code>" . $_FILES['test_upload']['tmp_name'] . "</code><br>";
        echo "To: <code>$destination</code><br><br>";
        
        if (move_uploaded_file($_FILES['test_upload']['tmp_name'], $destination)) {
            echo '<span class="success">✓ Upload SUCCESSFUL!</span><br>';
            echo "File saved as: <code>$new_name</code><br>";
            
            // Show uploaded image if it's an image
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $url = '/brew-bakery/uploads/articles/' . $new_name;
                echo "<br><img src='$url' style='max-width: 300px; border: 2px solid #28a745; border-radius: 5px;'>";
            }
        } else {
            echo '<span class="error">✗ Upload FAILED!</span><br>';
            echo '<span class="error">move_uploaded_file() returned false</span>';
        }
    } else {
        echo '<span class="error">✗ File upload error</span>';
    }
    echo "</div>";
}

// Upload Form
echo "<div class='box'>";
echo "<h2>5. Test File Upload</h2>";
echo "<form method='POST' enctype='multipart/form-data' style='margin-top: 20px;'>";
echo "<input type='file' name='test_upload' accept='image/*' required style='padding: 10px; border: 1px solid #ddd; border-radius: 3px;'>";
echo "<button type='submit' style='padding: 10px 20px; background: #8B6F47; color: white; border: none; border-radius: 3px; cursor: pointer; margin-left: 10px;'>Test Upload</button>";
echo "</form>";
echo "<p style='color: #666; margin-top: 10px;'>Upload any image file to test if the upload system is working.</p>";
echo "</div>";

// ============================================
// 6. System Information
// ============================================
echo "<div class='box'>";
echo "<h2>6. System Information</h2>";
echo "<table>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Operating System</td><td>" . php_uname() . "</td></tr>";
echo "<tr><td>Document Root</td><td><code>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</code></td></tr>";
echo "</table>";
echo "</div>";

echo "<div class='box' style='background: #fff3cd; border-left: 4px solid #ffc107;'>";
echo "<h3>📝 Next Steps:</h3>";
echo "<ol>";
echo "<li>Check all items above - everything should be <span class='success'>green ✓</span></li>";
echo "<li>Use the <strong>Test File Upload</strong> form above to test uploading</li>";
echo "<li>If upload works here but not in your admin panel, the issue is in your application code</li>";
echo "<li>If upload fails here, fix the directory permissions or PHP settings</li>";
echo "</ol>";
echo "</div>";

echo "<div class='box' style='background: #f8d7da; border-left: 4px solid #dc3545;'>";
echo "<h3>⚠️ Common Issues & Solutions:</h3>";
echo "<ul>";
echo "<li><strong>Directory not writable:</strong> Run <code>chmod -R 755 uploads/</code></li>";
echo "<li><strong>file_uploads disabled:</strong> Edit php.ini and set <code>file_uploads = On</code></li>";
echo "<li><strong>upload_max_filesize too small:</strong> Edit php.ini and increase value (e.g., <code>upload_max_filesize = 50M</code>)</li>";
echo "<li><strong>SELinux blocking:</strong> Run <code>chcon -R -t httpd_sys_rw_content_t uploads/</code></li>";
echo "</ul>";
echo "</div>";
?>