<?php
define('BASE_URL', 'http://localhost/brew-bakery/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('CUSTOMER_URL', BASE_URL . 'customer/');
define('AUTH_URL', BASE_URL . 'auth/');
define('API_URL', BASE_URL . 'api/');

// ============================================
// URL CONSTANTS (untuk <img src="">)
// ============================================
define('PRODUCT_IMG_URL', BASE_URL . 'uploads/products/');
define('ARTICLE_IMG_URL', BASE_URL . 'uploads/articles/');
define('PROFILE_IMG_URL', BASE_URL . 'uploads/profiles/');
define('PAYMENT_PROOF_URL', BASE_URL . 'uploads/payment-proofs/');

// ============================================
// PATH CONSTANTS (untuk uploadImage())
// ============================================
define('PRODUCT_IMG_DIR', 'products/');
define('ARTICLE_IMG_DIR', 'articles/');
define('PROFILE_IMG_DIR', 'profiles/');
define('PAYMENT_PROOF_DIR', 'payment-proofs/');

define('ALLOWED_IMG_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_IMG_SIZE', 5 * 1024 * 1024);
define('SESSION_TIMEOUT', 3600);

define('ORDER_STATUS', [
    'menunggu_bukti' => 'Menunggu Bukti Pembayaran',
    'menunggu_verifikasi' => 'Menunggu Verifikasi',
    'diterima' => 'Pembayaran Diterima',
    'ditolak' => 'Pembayaran Ditolak',
    'siap_kirim' => 'Siap Dikirim',
    'selesai' => 'Selesai'
]);

// ← PERBAIKAN: Consistent Brown & Warm Bakery Palette
define('COLOR_PRIMARY', '#8B6F47');           // Coklat Terang (Utama)
define('COLOR_SECONDARY', '#D4A574');         // Coklat Golden (Secondary)
define('COLOR_ACCENT', '#F5E6D3');            // Krem Muda (Accent)
define('COLOR_GOLD', '#D4A574');              // Coklat Golden
define('COLOR_HONEY', '#6B4423');             // Coklat Gelap (Dark)
define('COLOR_DARK', '#6B4423');              // Coklat Gelap (Text)
define('COLOR_LIGHT', '#F5F1ED');             // Krem Sangat Muda (Light BG)
?>