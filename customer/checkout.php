<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkCustomerAuth();

$customer_id = $_SESSION['customer_id'];
$customer = getCustomerById($customer_id);

// Initialize customer data dengan default values
$customer_nama = $customer['nama'] ?? '';
$customer_email = $customer['email'] ?? '';
$customer_no_hp = $customer['no_hp'] ?? '';
$customer_alamat = $customer['alamat'] ?? '';

// ← TAMBAHAN: Handle direct checkout dari product detail
$direct_checkout = isset($_GET['direct']) && $_GET['direct'] === '1';
$temp_cart_item = $_SESSION['temp_cart'] ?? null;

if ($direct_checkout && !$temp_cart_item) {
    redirect(CUSTOMER_URL . 'shop.php');
}

// Get cart items
if ($direct_checkout && $temp_cart_item) {
    // Gunakan temporary cart item
    $cart_items = [$temp_cart_item];
    $subtotal = $temp_cart_item['harga'] * $temp_cart_item['jumlah'];
} else {
    // Gunakan cart normal
    $cart_items = getCartItems($customer_id);
    
    if (count($cart_items) === 0) {
        redirect(CUSTOMER_URL . 'cart.php');
    }
    
    $subtotal = getCartTotal($customer_id);
}

$ongkir = 0;
$total = $subtotal;

// Get shipping costs
$shipping_costs = $conn->query("SELECT * FROM shipping_costs ORDER BY wilayah")->fetch_all(MYSQLI_ASSOC);

// Payment methods dengan detail
$payment_methods = [
    'transfer_bank' => [
        'nama' => 'Transfer Bank', 
        'icon' => '🏦',
        'details' => [
            ['bank' => 'BCA', 'no_rek' => '1234567890', 'atas_nama' => 'Brew Bakery'],
            ['bank' => 'Mandiri', 'no_rek' => '0987654321', 'atas_nama' => 'Brew Bakery'],
            ['bank' => 'BNI', 'no_rek' => '5556667778', 'atas_nama' => 'Brew Bakery']
        ]
    ],
    'e_wallet' => [
        'nama' => 'E-Wallet', 
        'icon' => '📱',
        'details' => [
            ['platform' => 'GoPay', 'nomor' => '081234567890', 'atas_nama' => 'Brew Bakery'],
            ['platform' => 'OVO', 'nomor' => '081234567890', 'atas_nama' => 'Brew Bakery'],
            ['platform' => 'DANA', 'nomor' => '081234567890', 'atas_nama' => 'Brew Bakery']
        ]
    ],
    'qris' => [
        'nama' => 'QRIS', 
        'icon' => '📲',
        'qris_image' => BASE_URL . 'customer/assets/images/qris-brewbakery.png', // Path ke gambar QRIS
        'details' => []
    ]
];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wilayah = sanitize($_POST['wilayah'] ?? '');
    $alamat = sanitize($_POST['alamat'] ?? '');
    $no_hp = sanitize($_POST['no_hp'] ?? '');
    $payment_method = sanitize($_POST['payment_method'] ?? '');

    if (empty($wilayah) || empty($alamat) || empty($no_hp) || empty($payment_method)) {
        $error = 'Semua field harus diisi!';
    } elseif (!isset($payment_methods[$payment_method])) {
        $error = 'Metode pembayaran tidak valid!';
    } else {
        // Get shipping cost
        $shipping = $conn->query("SELECT ongkir FROM shipping_costs WHERE wilayah = '$wilayah'")->fetch_assoc();
        if (!$shipping) {
            $error = 'Wilayah tidak ditemukan!';
        } else {
            $ongkir = $shipping['ongkir'];
            $total = $subtotal + $ongkir;
            
            // Create order
            $no_pesanan = generateOrderNumber();
            $status = 'menunggu_bukti'; // ← PENTING: Status ini
            
            $insert_query = "INSERT INTO orders (customer_id, no_pesanan, total_harga, ongkir, total_bayar, wilayah, alamat_lengkap, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isdddsss", $customer_id, $no_pesanan, $subtotal, $ongkir, $total, $wilayah, $alamat, $status);
            
            if ($stmt->execute()) {
                $order_id = $conn->insert_id;
                
                // Insert order items dan kurangi stok
                foreach ($cart_items as $item) {
                    $insert_item_query = "INSERT INTO order_items (order_id, product_id, jumlah, harga) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_item_query);
                    $stmt->bind_param("iiii", $order_id, $item['product_id'], $item['jumlah'], $item['harga']);
                    $stmt->execute();
                    
                    // Kurangi stok produk
                    $update_stock_query = "UPDATE products SET stok = stok - ? WHERE id = ?";
                    $stock_stmt = $conn->prepare($update_stock_query);
                    $stock_stmt->bind_param("ii", $item['jumlah'], $item['product_id']);
                    $stock_stmt->execute();
                }
                
                // Clear cart
                if (!$direct_checkout) {
                    // Hanya clear cart database jika bukan direct checkout
                    $delete_cart_query = "DELETE FROM carts WHERE customer_id = ?";
                    $stmt = $conn->prepare($delete_cart_query);
                    $stmt->bind_param("i", $customer_id);
                    $stmt->execute();
                }
                
                // ← TAMBAHAN: Clear temp cart dari session
                unset($_SESSION['temp_cart']);
                
                // Create notification
                createNotification($customer_id, $order_id, 'Pesanan Berhasil Dibuat', 
                    'Pesanan #' . $no_pesanan . ' berhasil dibuat. Silakan upload bukti pembayaran.');
                
                error_log("Checkout success - redirecting to payment upload. Order ID: $order_id");
                redirect(CUSTOMER_URL . 'payment-upload.php?order_id=' . $order_id);
            } else {
                $error = 'Gagal membuat pesanan!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .checkout-layout {
            min-height: calc(100vh - 70px);
            background-color: var(--light);
            padding: 2rem 0;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        .checkout-form {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .checkout-form h1 {
            margin-bottom: 1.5rem;
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .form-section h3 {
            margin-bottom: 1rem;
            color: var(--dark);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        .payment-option {
            display: none;
        }
        .payment-option + label {
            padding: 1.5rem;
            border: 2px solid #ddd;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            text-align: center;
        }
        .payment-option:checked + label {
            background-color: rgba(139, 111, 71, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        .payment-icon {
            font-size: 2rem;
        }
        .payment-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
        }
        .payment-details {
            display: none;
            margin-top: 1rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            border: 2px solid var(--primary);
        }
        .payment-details.active {
            display: block;
        }
        .payment-details h4 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .payment-info {
            background: white;
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 0.75rem;
            border-left: 3px solid var(--primary);
        }
        .payment-info:last-child {
            margin-bottom: 0;
        }
        .payment-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .payment-info-row:last-child {
            margin-bottom: 0;
        }
        .payment-info-label {
            font-weight: 600;
            color: #666;
        }
        .payment-info-value-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .payment-info-value {
            font-weight: 700;
            color: var(--dark);
            user-select: all;
            padding: 0.25rem 0.5rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }
        .copy-btn {
            padding: 0.25rem 0.5rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .copy-btn:hover {
            background-color: var(--dark);
        }
        .copy-btn.copied {
            background-color: #28a745;
        }
        .payment-note {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 1rem;
            border-radius: 0.3rem;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #856404;
        }
        .qris-container {
            text-align: center;
            padding: 1rem;
        }
        .qris-image {
            max-width: 300px;
            width: 100%;
            height: auto;
            border: 2px solid #ddd;
            border-radius: 0.5rem;
            margin: 1rem auto;
            display: block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .qris-info {
            background: white;
            padding: 1rem;
            border-radius: 0.3rem;
            border-left: 3px solid var(--primary);
            margin-top: 1rem;
            text-align: left;
        }

.qris-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.qris-modal-content {
    position: relative;
    margin: auto;
    padding: 20px;
    width: 90%;
    max-width: 600px;
    top: 50%;
    transform: translateY(-50%);
    text-align: center;
}

.qris-modal-image {
    width: 100%;
    max-width: 500px;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    background: white;
    padding: 20px;
}

.qris-close {
    position: absolute;
    top: 10px;
    right: 25px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    z-index: 10000;
}

.qris-close:hover,
.qris-close:focus {
    color: #f44336;
}

.qris-modal-title {
    color: white;
    margin-bottom: 20px;
    font-size: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.qris-zoom-hint {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 15px;
    background-color: rgba(139, 111, 71, 0.9);
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s;
}

.qris-zoom-hint:hover {
    background-color: var(--primary);
    transform: scale(1.05);
}

/* Update style untuk gambar QRIS yang bisa diklik */
.qris-image {
    max-width: 300px;
    width: 100%;
    height: auto;
    border: 2px solid #ddd;
    border-radius: 0.5rem;
    margin: 1rem auto;
    display: block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer; /* Tambahkan cursor pointer */
    transition: all 0.3s;
}

.qris-image:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .qris-modal-content {
        width: 95%;
        padding: 10px;
    }
    
    .qris-close {
        font-size: 30px;
        right: 15px;
    }
}

        .order-summary {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .summary-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            font-weight: 600;
        }
        .summary-row.total {
            font-size: 1.25rem;
            color: var(--primary);
            border-top: 2px solid var(--primary);
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        .btn-checkout:hover {
            background-color: var(--dark);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            .order-summary {
                position: static;
            }
            .payment-info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .payment-info-value-wrapper {
                width: 100%;
            }
            .payment-info-value {
                flex: 1;
            }
        }
    </style>

</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="checkout-layout">
        <div class="checkout-container">
            <a href="<?php echo CUSTOMER_URL; ?>cart.php" style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Kembali ke Keranjang</a>
            
            <div class="checkout-grid">
                <div class="checkout-form">
                    <h1>💳 Checkout</h1>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" id="checkout-form">
                        <div class="form-section">
                            <h3>📦 Data Pengiriman</h3>
                            
                            <div class="form-group">
                                <label for="wilayah">Wilayah Pengiriman *</label>
                                <select id="wilayah" name="wilayah" required onchange="updateShippingCost()">
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <?php foreach ($shipping_costs as $cost): ?>
                                    <option value="<?php echo htmlspecialchars($cost['wilayah']); ?>" data-cost="<?php echo $cost['ongkir']; ?>">
                                        <?php echo htmlspecialchars($cost['wilayah']); ?> (Rp <?php echo number_format($cost['ongkir'], 0, ',', '.'); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="alamat">Alamat Lengkap *</label>
                                <textarea id="alamat" name="alamat" placeholder="Jl. ..., Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos" required><?php echo htmlspecialchars($customer_alamat); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="no_hp">Nomor HP (Penerima) *</label>
                                <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxx" value="<?php echo htmlspecialchars($customer_no_hp); ?>" required>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>💰 Metode Pembayaran</h3>
                            <div class="payment-methods">
                                <?php foreach ($payment_methods as $key => $method): ?>
                                <div>
                                    <input type="radio" id="payment_<?php echo $key; ?>" name="payment_method" value="<?php echo $key; ?>" class="payment-option" required>
                                    <label for="payment_<?php echo $key; ?>">
                                        <span class="payment-icon"><?php echo $method['icon']; ?></span>
                                        <span class="payment-name"><?php echo $method['nama']; ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Detail Pembayaran Transfer Bank -->
                            <div id="detail-transfer_bank" class="payment-details">
                                <h4>🏦 Informasi Rekening Bank</h4>
                                <?php foreach ($payment_methods['transfer_bank']['details'] as $bank): ?>
                                <div class="payment-info">
                                    <div class="payment-info-row">
                                        <span class="payment-info-label">Bank:</span>
                                        <span class="payment-info-value"><?php echo $bank['bank']; ?></span>
                                    </div>
                                    <div class="payment-info-row">
                                        <span class="payment-info-label">No. Rekening:</span>
                                        <div class="payment-info-value-wrapper">
                                            <span class="payment-info-value" id="norek-<?php echo strtolower($bank['bank']); ?>"><?php echo $bank['no_rek']; ?></span>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('norek-<?php echo strtolower($bank['bank']); ?>', this)">📋 Copy</button>
                                        </div>
                                    </div>
                                    <div class="payment-info-row">
                                        <span class="payment-info-label">Atas Nama:</span>
                                        <span class="payment-info-value"><?php echo $bank['atas_nama']; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="payment-note">
                                    ⚠️ <strong>Penting:</strong> Setelah transfer, silakan upload bukti pembayaran di halaman berikutnya.
                                </div>
                            </div>

                            <!-- Detail Pembayaran E-Wallet -->
                            <div id="detail-e_wallet" class="payment-details">
                                <h4>📱 Informasi E-Wallet</h4>
                                <?php foreach ($payment_methods['e_wallet']['details'] as $ewallet): ?>
                                <div class="payment-info">
                                    <div class="payment-info-row">
                                        <span class="payment-info-label">Platform:</span>
                                        <span class="payment-info-value"><?php echo $ewallet['platform']; ?></span>
                                    </div>
                                    <div class="payment-info-row">
                                        <span class="payment-info-label">Nomor:</span>
                                        <div class="payment-info-value-wrapper">
                                            <span class="payment-info-value" id="nomor-<?php echo strtolower($ewallet['platform']); ?>"><?php echo $ewallet['nomor']; ?></span>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('nomor-<?php echo strtolower($ewallet['platform']); ?>', this)">📋 Copy</button>
                                        </div>
                                    </div>
                                    <div class="payment-info-row">
                                        <span class="payment-info-label">Atas Nama:</span>
                                        <span class="payment-info-value"><?php echo $ewallet['atas_nama']; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="payment-note">
                                    ⚠️ <strong>Penting:</strong> Setelah transfer, silakan upload bukti pembayaran di halaman berikutnya.
                                </div>
                            </div>

                            <!-- Detail Pembayaran QRIS -->
                           <div id="detail-qris" class="payment-details">
    <h4>📲 Pembayaran QRIS</h4>
    <div class="qris-container">
        <p style="margin-bottom: 1rem; color: #666;">Scan QR Code di bawah ini untuk melakukan pembayaran</p>
        
        <!-- Gambar QRIS yang bisa diklik -->
        <img src="<?php echo $payment_methods['qris']['qris_image']; ?>" 
             alt="QRIS Brew Bakery" 
             class="qris-image"
             onclick="openQrisModal('<?php echo $payment_methods['qris']['qris_image']; ?>')">
        
        <!-- Hint untuk zoom -->
        <div class="qris-zoom-hint" onclick="openQrisModal('<?php echo $payment_methods['qris']['qris_image']; ?>')">
            🔍 Klik gambar untuk memperbesar
        </div>
        
        <div class="qris-info">
            <p style="margin: 0; line-height: 1.6; font-size: 0.9rem;">
                <strong>Cara Pembayaran:</strong><br>
                1. Buka aplikasi e-wallet atau mobile banking Anda<br>
                2. Pilih menu "Scan QR" atau "QRIS"<br>
                3. Scan QR Code di atas<br>
                4. Masukkan nominal pembayaran sesuai total<br>
                5. Konfirmasi pembayaran
            </p>
        </div>
    </div>
    <div class="payment-note">
        ⚠️ <strong>Penting:</strong> Setelah pembayaran berhasil, silakan screenshot bukti pembayaran dan upload di halaman berikutnya.
    </div>
</div>
                        
                        <div id="qrisModal" class="qris-modal" onclick="closeQrisModal(event)">
    <span class="qris-close" onclick="closeQrisModal()">&times;</span>
    <div class="qris-modal-content">
        <h2 class="qris-modal-title">📲 Scan QR Code untuk Pembayaran</h2>
        <img id="qrisModalImage" class="qris-modal-image" src="" alt="QRIS Brew Bakery">
        <p style="color: white; margin-top: 15px; font-size: 0.9rem;">
            💡 Scan QR Code ini dengan aplikasi e-wallet atau mobile banking Anda
        </p>
    </div>
</div>

                        <div class="form-section">
                            <h3>📝 Data Pribadi</h3>
                            
                            <div class="form-group">
                                <label for="nama">Nama Pemesan</label>
                                <input type="text" id="nama" value="<?php echo htmlspecialchars($customer_nama); ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($customer_email); ?>" disabled>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="order-summary">
                    <div class="summary-title">📋 Ringkasan Pesanan</div>
                    
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #eee;">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <span><?php echo htmlspecialchars($item['nama']); ?> x<?php echo $item['jumlah']; ?></span>
                            <span><?php echo formatCurrency($item['harga'] * $item['jumlah']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkir</span>
                        <span id="ongkir-display"><?php echo formatCurrency($ongkir); ?></span>
                        <input type="hidden" id="ongkir-value" value="<?php echo $ongkir; ?>">
                    </div>
                    <div class="summary-row total">
                        <span>Total Bayar</span>
                        <span id="total-display"><?php echo formatCurrency($total); ?></span>
                    </div>

                    <button type="submit" form="checkout-form" class="btn-checkout">✓ Lanjut ke Pembayaran</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateShippingCost() {
            const select = document.getElementById('wilayah');
            const selectedOption = select.options[select.selectedIndex];
            const cost = parseInt(selectedOption.getAttribute('data-cost')) || 0;
            
            document.getElementById('ongkir-value').value = cost;
            document.getElementById('ongkir-display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(cost);
            
            const subtotal = <?php echo $subtotal; ?>;
            const total = subtotal + cost;
            document.getElementById('total-display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }

        // Show payment details when payment method is selected
        document.addEventListener('DOMContentLoaded', function() {
            const paymentOptions = document.querySelectorAll('.payment-option');
            const paymentDetails = document.querySelectorAll('.payment-details');

            paymentOptions.forEach(option => {
                option.addEventListener('change', function() {
                    // Hide all payment details
                    paymentDetails.forEach(detail => {
                        detail.classList.remove('active');
                    });

                    // Show selected payment detail
                    if (this.checked) {
                        const detailId = 'detail-' + this.value;
                        const selectedDetail = document.getElementById(detailId);
                        if (selectedDetail) {
                            selectedDetail.classList.add('active');
                        }
                    }
                });
            });
        });

        // Copy to clipboard function
        function copyToClipboard(elementId, button) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            // Modern clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess(button);
                }).catch(function(err) {
                    // Fallback method
                    copyTextFallback(text, button);
                });
            } else {
                // Fallback method for older browsers
                copyTextFallback(text, button);
            }
        }

        function copyTextFallback(text, button) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showCopySuccess(button);
            } catch (err) {
                console.error('Fallback: Could not copy text', err);
                alert('Gagal menyalin. Silakan copy manual.');
            }
            
            document.body.removeChild(textArea);
        }

        function showCopySuccess(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '✓ Tersalin!';
            button.classList.add('copied');
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('copied');
            }, 2000);
        }

        // Fungsi untuk membuka modal QRIS
        function openQrisModal(imageSrc) {
            const modal = document.getElementById('qrisModal');
            const modalImg = document.getElementById('qrisModalImage');
            
            modal.style.display = 'block';
            modalImg.src = imageSrc;
            
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
        }

        // Fungsi untuk menutup modal QRIS
        function closeQrisModal(event) {
            const modal = document.getElementById('qrisModal');
            
            // Close jika klik di luar gambar atau tombol close
            if (!event || event.target === modal || event.target.className === 'qris-close') {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal dengan tombol ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('qrisModal');
                if (modal && modal.style.display === 'block') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
        });
    </script>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>