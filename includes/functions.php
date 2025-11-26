<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// ============================================
// DATABASE FUNCTIONS
// ============================================

function getProductById($id) {
    global $conn;
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCustomerById($id) {
    global $conn;
    $query = "SELECT * FROM customers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserById($id) {
    global $conn;
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getOrderById($id) {
    global $conn;
    $query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCartItems($customer_id) {
    global $conn;
    // PERBAIKAN: Tambah kolom diskon dari tabel products
    $query = "SELECT c.id, c.product_id, c.jumlah, p.nama, p.harga, p.stok, p.foto_utama,
                     p.diskon_tipe, p.diskon_nilai, p.diskon_aktif
              FROM carts c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getCartTotal($customer_id) {
    global $conn;
    // PERBAIKAN: Hitung total dengan mempertimbangkan diskon
    $query = "SELECT SUM(
                CASE 
                    WHEN p.diskon_aktif = 1 THEN
                        CASE 
                            WHEN p.diskon_tipe = 'persentase' THEN c.jumlah * (p.harga - (p.harga * (p.diskon_nilai / 100)))
                            ELSE c.jumlah * (p.harga - p.diskon_nilai)
                        END
                    ELSE c.jumlah * p.harga
                END
              ) as total 
              FROM carts c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

function getUnreadNotifications($user_id) {
    global $conn;
    
    // PERBAIKAN: Gunakan column yang benar sesuai schema
    // Cek tabel notifications di database - gunakan column yang ada
    // Option 1: Jika column adalah 'customer_id'
    // Option 2: Jika column adalah 'user_id'
    // Kita akan try/catch untuk handle error
    
    try {
        $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND dibaca = FALSE";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            // Jika gagal, coba dengan customer_id
            error_log("getUnreadNotifications: user_id column failed, trying customer_id");
            $query = "SELECT COUNT(*) as count FROM notifications WHERE customer_id = ? AND dibaca = FALSE";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                error_log("Query error: " . $conn->error);
                return 0;
            }
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
        
    } catch (Exception $e) {
        error_log("getUnreadNotifications ERROR: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date) {
    return date('d M Y H:i', strtotime($date));
}

function generateOrderNumber() {
    return 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
}

function jsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    $response = ['status' => $status, 'message' => $message];
    if ($data) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

function createNotification($customer_id, $order_id, $judul, $pesan) {
    global $conn;
    
    // PERBAIKAN: Gunakan customer_id, bukan user_id
    $insert_query = "INSERT INTO notifications (customer_id, order_id, judul, pesan) 
                     VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    
    if (!$stmt) {
        error_log("createNotification ERROR: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("iiss", $customer_id, $order_id, $judul, $pesan);
    return $stmt->execute();
}

function deleteImage($filename, $directory) {
    $path = __DIR__ . '/../uploads/' . ltrim($directory, '/') . $filename;
    if (file_exists($path)) {
        return unlink($path);
    }
    return false;
}

// ============================================
// IMAGE UPLOAD FUNCTION - FINAL FIX
// ============================================

function uploadImage($file, $directory = 'products/') {
    // STEP 1: Validate input
    if (!is_array($file) || !isset($file['error'], $file['type'], $file['size'], $file['name'], $file['tmp_name'])) {
        error_log("uploadImage ERROR: Invalid file array structure");
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("uploadImage ERROR: Upload error code " . $file['error']);
        return false;
    }

    // STEP 2: Get extension DULU sebelum validasi type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    if (empty($extension) || !in_array($extension, $allowed_extensions)) {
        error_log("uploadImage ERROR: Invalid extension: " . $extension);
        return false;
    }

    // STEP 3: Validasi MIME type (LEBIH FLEKSIBEL)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detected_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp', 'image/gif'];
    
    // Log untuk debugging
    error_log("uploadImage DEBUG: Uploaded type (browser): " . $file['type']);
    error_log("uploadImage DEBUG: Detected type (server): " . $detected_type);
    error_log("uploadImage DEBUG: Extension: " . $extension);
    
    if (!in_array($detected_type, $allowed_types)) {
        error_log("uploadImage ERROR: Invalid detected MIME type: " . $detected_type);
        return false;
    }

    // STEP 4: Validate file size (PERBAIKAN: Naikkan limit ke 50MB)
    // 33KB seharusnya jauh di bawah limit ini
    $max_size = 50 * 1024 * 1024;  // 50MB (sebelumnya 10MB)
    
    error_log("uploadImage DEBUG: file size = " . $file['size']);
    error_log("uploadImage DEBUG: max size = " . $max_size);
    
    if ($file['size'] > $max_size) {
        error_log("uploadImage ERROR: File too large: " . $file['size'] . " bytes (max: $max_size)");
        return false;
    }
    
    if ($file['size'] <= 0) {
        error_log("uploadImage ERROR: File size is zero or negative");
        return false;
    }

    // STEP 5: Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;

    // STEP 6: Define upload directory paths
    $base_path = __DIR__ . '/../';
    $upload_subfolder = ltrim($directory, '/');
    $full_upload_path = $base_path . 'uploads/' . $upload_subfolder;

    error_log("uploadImage DEBUG: base_path = $base_path");
    error_log("uploadImage DEBUG: full_upload_path = $full_upload_path");

    // STEP 7: Create uploads base directory
    $base_uploads = $base_path . 'uploads/';
    if (!is_dir($base_uploads)) {
        if (!@mkdir($base_uploads, 0755, true)) {
            error_log("uploadImage ERROR: Cannot create base uploads dir: $base_uploads");
            return false;
        }
        error_log("uploadImage INFO: Created base uploads dir: $base_uploads");
    }

    // STEP 8: Create subdirectory
    if (!is_dir($full_upload_path)) {
        if (!@mkdir($full_upload_path, 0755, true)) {
            error_log("uploadImage ERROR: Cannot create subdirectory: $full_upload_path");
            return false;
        }
        error_log("uploadImage INFO: Created subdirectory: $full_upload_path");
    }

    // STEP 9: Verify directory is writable
    if (!is_writable($full_upload_path)) {
        error_log("uploadImage ERROR: Directory not writable: $full_upload_path");
        @chmod($full_upload_path, 0755);
        
        if (!is_writable($full_upload_path)) {
            error_log("uploadImage ERROR: Still not writable after chmod");
            return false;
        }
    }

    // STEP 10: Define final file path
    $file_path = $full_upload_path . $filename;

    // STEP 11: Verify temp file exists
    if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        error_log("uploadImage ERROR: Temp file not found: " . $file['tmp_name']);
        return false;
    }

    // STEP 12: Move uploaded file
    if (!@move_uploaded_file($file['tmp_name'], $file_path)) {
        error_log("uploadImage ERROR: Failed to move uploaded file");
        error_log("uploadImage DEBUG: From: " . $file['tmp_name']);
        error_log("uploadImage DEBUG: To: " . $file_path);
        return false;
    }

    // STEP 13: Verify file was created
    if (!file_exists($file_path)) {
        error_log("uploadImage ERROR: File not found after move: $file_path");
        return false;
    }

    // STEP 14: Set proper permissions
    @chmod($file_path, 0644);

    error_log("uploadImage SUCCESS: File uploaded - $filename");

    return $filename;
}

/**
 * Hitung harga setelah diskon (UPDATED)
 */
function calculateDiscountedPrice($harga, $diskon_tipe, $diskon_nilai, $diskon_aktif) {
    if (!$diskon_aktif || !$diskon_nilai || $diskon_nilai <= 0) {
        return $harga;
    }
    
    if ($diskon_tipe === 'persentase') {
        // Diskon persentase
        if ($diskon_nilai > 100) $diskon_nilai = 100;
        return $harga - ($harga * ($diskon_nilai / 100));
    } else {
        // Diskon nominal
        $discounted = $harga - $diskon_nilai;
        return $discounted > 0 ? $discounted : 0;
    }
}

/**
 * Format harga dengan diskon (UPDATED)
 */
function formatPriceWithDiscount($harga, $diskon_tipe, $diskon_nilai, $diskon_aktif) {
    if (!$diskon_aktif || !$diskon_nilai || $diskon_nilai <= 0) {
        return [
            'original' => $harga,
            'discounted' => $harga,
            'discount_type' => null,
            'discount_value' => 0,
            'savings' => 0
        ];
    }
    
    $discountedPrice = calculateDiscountedPrice($harga, $diskon_tipe, $diskon_nilai, $diskon_aktif);
    $savings = $harga - $discountedPrice;
    
    if ($diskon_tipe === 'persentase') {
        $display = '-' . $diskon_nilai . '%';
    } else {
        $display = '-Rp ' . number_format($diskon_nilai, 0, ',', '.');
    }
    
    return [
        'original' => $harga,
        'discounted' => $discountedPrice,
        'discount_type' => $diskon_tipe,
        'discount_value' => $diskon_nilai,
        'discount_display' => $display,
        'savings' => $savings
    ];
}
?>
