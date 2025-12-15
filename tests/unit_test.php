<?php
/**
 * UNIT TEST - WHITE BOX TESTING
 * Brew Bakery E-Commerce System
 * 
 * File ini berisi unit test untuk menguji fungsi-fungsi internal sistem
 * sesuai Test Plan yang telah dibuat.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

// Styling untuk output
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .test-container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #6B4423; border-bottom: 3px solid #8B6F47; padding-bottom: 10px; }
    h2 { color: #8B6F47; margin-top: 30px; background: #F5E6D3; padding: 10px; border-radius: 5px; }
    .test-case { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #fafafa; border-radius: 4px; }
    .test-case.pass { border-left-color: #28a745; background: #d4edda; }
    .test-case.fail { border-left-color: #dc3545; background: #f8d7da; }
    .test-name { font-weight: bold; font-size: 16px; margin-bottom: 8px; }
    .test-result { margin-top: 8px; padding: 8px; border-radius: 4px; font-family: monospace; font-size: 13px; }
    .pass { color: #155724; }
    .fail { color: #721c24; }
    .summary { background: #e7f3ff; padding: 20px; border-radius: 8px; margin-top: 30px; border: 2px solid #0066cc; }
    .summary h3 { color: #0066cc; margin-top: 0; }
    .stat { display: inline-block; margin-right: 30px; font-size: 18px; font-weight: bold; }
    .stat.pass { color: #28a745; }
    .stat.fail { color: #dc3545; }
</style>";

echo "<div class='test-container'>";
echo "<h1>🧪 UNIT TEST - WHITE BOX TESTING</h1>";
echo "<p><strong>Project:</strong> Brew Bakery E-Commerce System</p>";
echo "<p><strong>Test Date:</strong> " . date('d M Y H:i:s') . "</p>";
echo "<p><strong>Tester:</strong> Tim Software Testing</p>";

$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

// ============================================
// HELPER FUNCTION
// ============================================
function runTest($testName, $expected, $actual) {
    global $total_tests, $passed_tests, $failed_tests;
    $total_tests++;
    
    $isPassed = ($expected === $actual);
    if ($isPassed) {
        $passed_tests++;
    } else {
        $failed_tests++;
    }
    
    $status = $isPassed ? 'pass' : 'fail';
    $statusText = $isPassed ? '✓ PASS' : '✗ FAIL';
    
    echo "<div class='test-case $status'>";
    echo "<div class='test-name'>$testName</div>";
    echo "<div class='test-result'>";
    echo "<strong>Expected:</strong> " . var_export($expected, true) . "<br>";
    echo "<strong>Actual:</strong> " . var_export($actual, true) . "<br>";
    echo "<strong class='$status'>Result: $statusText</strong>";
    echo "</div>";
    echo "</div>";
}

// ============================================
// TEST 1: FUNGSI PERHITUNGAN DISKON
// ============================================
echo "<h2>1️⃣ Test Fungsi Perhitungan Harga dengan Diskon</h2>";
echo "<p>Menguji fungsi <code>calculateDiscountedPrice()</code> dan <code>formatPriceWithDiscount()</code></p>";

// Test 1.1: Diskon Persentase 10%
$result = calculateDiscountedPrice(10000, 'persentase', 10, 1);
runTest(
    "Test 1.1: Diskon Persentase 10% dari Rp 10,000",
    9000.0,
    $result
);

// Test 1.2: Diskon Nominal Rp 2,000
$result = calculateDiscountedPrice(10000, 'nominal', 2000, 1);
runTest(
    "Test 1.2: Diskon Nominal Rp 2,000 dari Rp 10,000",
    8000.0,
    $result
);

// Test 1.3: Diskon Tidak Aktif
$result = calculateDiscountedPrice(10000, 'persentase', 10, 0);
runTest(
    "Test 1.3: Diskon Tidak Aktif (harga tetap)",
    10000.0,
    $result
);

// Test 1.4: Diskon Persentase 100% (edge case)
$result = calculateDiscountedPrice(10000, 'persentase', 100, 1);
runTest(
    "Test 1.4: Diskon Persentase 100% (gratis)",
    0.0,
    $result
);

// Test 1.5: Diskon Nominal Lebih Besar dari Harga (edge case)
$result = calculateDiscountedPrice(10000, 'nominal', 15000, 1);
runTest(
    "Test 1.5: Diskon Nominal Rp 15,000 dari Rp 10,000 (harga min 0)",
    0.0,
    $result
);

// Test 1.6: formatPriceWithDiscount() dengan diskon aktif
$result = formatPriceWithDiscount(15000, 'persentase', 20, 1);
$expected = [
    'original' => 15000.0,
    'discounted' => 12000.0,
    'discount_type' => 'persentase',
    'discount_value' => 20.0,
    'discount_display' => '-20%',
    'savings' => 3000.0
];
runTest(
    "Test 1.6: Format harga dengan diskon 20% dari Rp 15,000",
    $expected,
    $result
);

// ============================================
// TEST 2: FUNGSI PASSWORD HASHING & VERIFICATION
// ============================================
echo "<h2>2️⃣ Test Fungsi Password Hashing & Verification</h2>";
echo "<p>Menguji fungsi <code>hashPassword()</code> dan <code>verifyPassword()</code></p>";

// Test 2.1: Hash password
$password = "admin123";
$hashed = hashPassword($password);
$isValidHash = (strlen($hashed) === 60 && strpos($hashed, '$2y$') === 0);
runTest(
    "Test 2.1: Hash password 'admin123' menghasilkan bcrypt hash (60 chars, starts with \$2y\$)",
    true,
    $isValidHash
);

// Test 2.2: Verify correct password
$isValid = verifyPassword($password, $hashed);
runTest(
    "Test 2.2: Verify password 'admin123' dengan hash yang benar",
    true,
    $isValid
);

// Test 2.3: Verify wrong password
$isValid = verifyPassword("wrongpassword", $hashed);
runTest(
    "Test 2.3: Verify password 'wrongpassword' dengan hash yang benar (harus gagal)",
    false,
    $isValid
);

// ============================================
// TEST 3: FUNGSI VALIDASI INPUT
// ============================================
echo "<h2>3️⃣ Test Fungsi Validasi Input</h2>";
echo "<p>Menguji validasi email dan input lainnya</p>";

// Test 3.1: Email valid
$email = "user@example.com";
$isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
runTest(
    "Test 3.1: Email valid 'user@example.com'",
    true,
    $isValid
);

// Test 3.2: Email invalid
$email = "userexample.com";
$isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
runTest(
    "Test 3.2: Email invalid 'userexample.com' (tanpa @)",
    false,
    $isValid
);

// Test 3.3: Password length >= 6
$password = "abc123";
$isValid = strlen($password) >= 6;
runTest(
    "Test 3.3: Password 'abc123' (6 karakter) valid",
    true,
    $isValid
);

// Test 3.4: Password length < 6
$password = "abc";
$isValid = strlen($password) >= 6;
runTest(
    "Test 3.4: Password 'abc' (3 karakter) invalid",
    false,
    $isValid
);

// Test 3.5: Sanitize HTML input
$input = "<script>alert('XSS')</script>";
$sanitized = sanitize($input);
$expected = "&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;";
runTest(
    "Test 3.5: Sanitize XSS input (HTML entities)",
    $expected,
    $sanitized
);

// ============================================
// TEST 4: FUNGSI FORMAT CURRENCY
// ============================================
echo "<h2>4️⃣ Test Fungsi Format Currency</h2>";
echo "<p>Menguji fungsi <code>formatCurrency()</code></p>";

// Test 4.1: Format 10000
$result = formatCurrency(10000);
runTest(
    "Test 4.1: Format Rp 10,000",
    "Rp 10.000",
    $result
);

// Test 4.2: Format 1500000
$result = formatCurrency(1500000);
runTest(
    "Test 4.2: Format Rp 1,500,000",
    "Rp 1.500.000",
    $result
);

// Test 4.3: Format 0
$result = formatCurrency(0);
runTest(
    "Test 4.3: Format Rp 0",
    "Rp 0",
    $result
);

// ============================================
// TEST 5: FUNGSI GENERATE ORDER NUMBER
// ============================================
echo "<h2>5️⃣ Test Fungsi Generate Order Number</h2>";
echo "<p>Menguji fungsi <code>generateOrderNumber()</code></p>";

// Test 5.1: Generate order number format
$orderNo = generateOrderNumber();
$isValid = (strpos($orderNo, 'ORD-') === 0 && strlen($orderNo) === 24);
runTest(
    "Test 5.1: Generate order number format 'ORD-YmdHis-XXXX' (24 chars)",
    true,
    $isValid
);

// Test 5.2: Unique order numbers
$orderNo1 = generateOrderNumber();
usleep(10000); // Wait 0.01 second
$orderNo2 = generateOrderNumber();
$isUnique = ($orderNo1 !== $orderNo2);
runTest(
    "Test 5.2: Generate 2 order numbers harus berbeda (unique)",
    true,
    $isUnique
);

// ============================================
// TEST 6: FUNGSI DATABASE QUERY
// ============================================
echo "<h2>6️⃣ Test Fungsi Database Query</h2>";
echo "<p>Menguji fungsi query database seperti <code>getProductById()</code></p>";

// Test 6.1: Get product yang ada (ID = 1)
$product = getProductById(1);
$exists = ($product !== null && is_array($product));
runTest(
    "Test 6.1: Get product ID=1 (harus return array)",
    true,
    $exists
);

// Test 6.2: Get product yang tidak ada (ID = 99999)
$product = getProductById(99999);
$notExists = ($product === null || $product === false);
runTest(
    "Test 6.2: Get product ID=99999 (tidak ada, harus return null/false)",
    true,
    $notExists
);

// Test 6.3: Get product dengan ID negative (edge case)
$product = getProductById(-1);
$notExists = ($product === null || $product === false);
runTest(
    "Test 6.3: Get product ID=-1 (invalid, harus return null/false)",
    true,
    $notExists
);

// ============================================
// TEST 7: FUNGSI PERHITUNGAN CART
// ============================================
echo "<h2>7️⃣ Test Fungsi Perhitungan Cart Total</h2>";
echo "<p>Menguji logika perhitungan total keranjang</p>";

// Test 7.1: Cart kosong (customer_id yang tidak ada)
$total = getCartTotal(99999);
runTest(
    "Test 7.1: Cart total untuk customer ID=99999 (tidak ada) harus Rp 0",
    0.0,
    (float)$total
);

// Test 7.2: Cart dengan data dummy
// (Catatan: Test ini akan skip jika tidak ada data di database)
echo "<div class='test-case' style='background: #fff3cd; border-left-color: #ffc107;'>";
echo "<div class='test-name'>Test 7.2: Cart total calculation (skipped - requires actual cart data)</div>";
echo "<div class='test-result'>⚠️ Test ini memerlukan data cart aktual di database untuk validasi</div>";
echo "</div>";

// ============================================
// TEST 8: FUNGSI UPLOAD FILE VALIDATION
// ============================================
echo "<h2>8️⃣ Test Validasi Upload File</h2>";
echo "<p>Menguji validasi tipe file dan size untuk upload</p>";

// Test 8.1: Validasi ekstensi file valid
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = 'jpg';
$isValid = in_array($ext, $allowed);
runTest(
    "Test 8.1: Ekstensi 'jpg' valid untuk upload",
    true,
    $isValid
);

// Test 8.2: Validasi ekstensi file invalid
$ext = 'exe';
$isValid = in_array($ext, $allowed);
runTest(
    "Test 8.2: Ekstensi 'exe' invalid untuk upload",
    false,
    $isValid
);

// Test 8.3: Validasi file size (5MB limit)
$fileSize = 4 * 1024 * 1024; // 4MB
$maxSize = 5 * 1024 * 1024;  // 5MB
$isValid = ($fileSize <= $maxSize);
runTest(
    "Test 8.3: File size 4MB <= 5MB (valid)",
    true,
    $isValid
);

// Test 8.4: Validasi file size terlalu besar
$fileSize = 6 * 1024 * 1024; // 6MB
$isValid = ($fileSize <= $maxSize);
runTest(
    "Test 8.4: File size 6MB > 5MB (invalid)",
    false,
    $isValid
);

// ============================================
// TEST SUMMARY
// ============================================
echo "<div class='summary'>";
echo "<h3>📊 TEST SUMMARY</h3>";
echo "<p>";
echo "<span class='stat'>Total Tests: $total_tests</span>";
echo "<span class='stat pass'>Passed: $passed_tests</span>";
echo "<span class='stat fail'>Failed: $failed_tests</span>";
echo "</p>";

$pass_rate = ($total_tests > 0) ? round(($passed_tests / $total_tests) * 100, 2) : 0;
echo "<p><strong>Pass Rate:</strong> $pass_rate%</p>";

if ($failed_tests === 0) {
    echo "<p style='color: #28a745; font-weight: bold; font-size: 18px;'>✅ ALL TESTS PASSED!</p>";
} else {
    echo "<p style='color: #dc3545; font-weight: bold; font-size: 18px;'>⚠️ SOME TESTS FAILED - Review the failed test cases above</p>";
}
echo "</div>";

echo "<p style='margin-top: 30px; text-align: center; color: #666;'>";
echo "<strong>Note:</strong> Hasil test ini harus didokumentasikan dalam Test Summary Report";
echo "</p>";

echo "</div>"; // End test-container
?>