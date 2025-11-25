<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// ← FIX: Gunakan customer_id, bukan user_id
if (!isset($_SESSION['customer_id'])) {
    jsonResponse('error', 'Unauthorized');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    // Handle form submission
    $product_id = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $ulasan = sanitize($_POST['ulasan'] ?? '');
} else {
    $product_id = $data['product_id'] ?? 0;
    $rating = $data['rating'] ?? 0;
    $ulasan = $data['ulasan'] ?? '';
}

$customer_id = $_SESSION['customer_id'];

if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    jsonResponse('error', 'Data tidak valid');
}

// Check if customer has purchased this product and order is completed
$purchase_query = "SELECT o.id FROM order_items oi 
                   JOIN orders o ON oi.order_id = o.id 
                   WHERE oi.product_id = ? AND o.customer_id = ? AND o.status = 'selesai'
                   LIMIT 1";
$stmt = $conn->prepare($purchase_query);
$stmt->bind_param("ii", $product_id, $customer_id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();

if (!$purchase) {
    jsonResponse('error', 'Anda hanya bisa memberi ulasan untuk produk yang telah dibeli dan pesanannya selesai');
}

// Check if already reviewed
$check_query = "SELECT id FROM reviews WHERE product_id = ? AND customer_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $product_id, $customer_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    jsonResponse('error', 'Anda sudah memberikan ulasan untuk produk ini');
}

$order_id = $purchase['id'];

// Insert review
// FIX: Pastikan urutan parameter sesuai dengan tipe string
$insert_query = "INSERT INTO reviews (product_id, customer_id, order_id, rating, ulasan) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);

// Urutan: product_id(i), customer_id(i), order_id(i), rating(i), ulasan(s)
// Tipe string: "iiis" = 4 karakter untuk 5 variabel = ERROR!
// Seharusnya: "iiiis" = 5 karakter untuk 5 variabel
$stmt->bind_param("iiiis", $product_id, $customer_id, $order_id, $rating, $ulasan);

if ($stmt->execute()) {
    // Jika form submission, redirect dengan success
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header("Location: " . CUSTOMER_URL . "orders/detail.php?id=" . $order_id . "&success=Review berhasil ditambahkan!");
        exit();
    }
    jsonResponse('success', 'Ulasan berhasil ditambahkan');
} else {
    jsonResponse('error', 'Terjadi kesalahan saat menyimpan ulasan');
}
?>
