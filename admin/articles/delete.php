<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

// Validasi ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . ADMIN_URL . "articles/?error=" . urlencode("ID artikel tidak valid"));
    exit;
}

$article_id = (int)$_GET['id'];

// Cek apakah artikel ada
$check_query = "SELECT id, foto FROM articles WHERE id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $article_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . ADMIN_URL . "articles/?error=" . urlencode("Artikel tidak ditemukan"));
    exit;
}

$article = $result->fetch_assoc();

// Mulai transaksi
$conn->begin_transaction();

try {
    // Hapus foto jika ada
    if (!empty($article['foto'])) {
        $image_path = __DIR__ . '/../../' . $article['foto'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Hapus artikel dari database
    $delete_query = "DELETE FROM articles WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $article_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Gagal menghapus artikel dari database");
    }
    
    // Commit transaksi
    $conn->commit();
    
    // Redirect dengan pesan sukses
    header("Location: " . ADMIN_URL . "articles/?success=" . urlencode("Artikel berhasil dihapus"));
    exit;
    
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();
    
    // Redirect dengan pesan error
    header("Location: " . ADMIN_URL . "articles/?error=" . urlencode("Gagal menghapus artikel: " . $e->getMessage()));
    exit;
}
?>