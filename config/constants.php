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

// ← PERBAIKAN: Bright & Warm Bakery Palette - Fresh, Yummy, Premium
define('COLOR_PRIMARY', '#F4E4C1');           // Vanilla Cream (Utama - Cerah & Lembut)
define('COLOR_SECONDARY', '#E8D4B8');         // Soft Butter (Secondary - Hangat)
define('COLOR_ACCENT', '#6e685fff');            // Milk White Premium (Background)
define('COLOR_GOLD', '#D4A574');              // Caramel Gold (Accent Premium)
define('COLOR_HONEY', '#C9915D');             // Honey Brown (Dark Accent)
define('COLOR_DARK', '#2D2D2D');              // Dark Text (Kontras)
define('COLOR_LIGHT', '#FFFBF7');             // Very Light Cream (Light BG)
?>