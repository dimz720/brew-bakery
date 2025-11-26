<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkCustomerAuth();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer_id = $_SESSION['customer_id'];  // ← FIX: user_id → customer_id

if ($order_id <= 0) {
    redirect(CUSTOMER_URL . 'orders/');
}

$order = getOrderById($order_id);
if (!$order || $order['customer_id'] !== $customer_id) {
    redirect(CUSTOMER_URL . 'orders/');
}

// Get order items
$query = "SELECT oi.*, p.nama, p.foto_utama FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE oi.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get payment proof
$payment_query = "SELECT * FROM payment_proofs WHERE order_id = ?";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

// Handle cancel order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if ($order['status'] === 'menunggu_bukti' || $order['status'] === 'menunggu_verifikasi') {
        $cancel_status = 'ditolak';
        $update_query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $cancel_status, $order_id);
        
        if ($stmt->execute()) {
            // Return stock
            foreach ($items as $item) {
                $stock_query = "UPDATE products SET stok = stok + ? WHERE id = ?";
                $stock_stmt = $conn->prepare($stock_query);
                $stock_stmt->bind_param("ii", $item['jumlah'], $item['product_id']);
                $stock_stmt->execute();
            }
            
            createNotification($customer_id, $order_id, 'Pesanan Dibatalkan', 'Pesanan Anda berhasil dibatalkan.');
            $success = 'Pesanan berhasil dibatalkan!';
            sleep(1);
            redirect(CUSTOMER_URL . 'orders/');
        }
    }
}

// Handle mark as received/complete - TAMBAHAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    
    if ($action === 'mark_received') {
        // Update status menjadi selesai
        $new_status = 'selesai';
        $update_query = "UPDATE orders SET status = ? WHERE id = ? AND customer_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sii", $new_status, $order_id, $customer_id);
        
        if ($stmt->execute()) {
            $success = '✓ Terima kasih! Pesanan Anda sudah ditandai sebagai selesai.';
            $order = getOrderById($order_id);
            createNotification($customer_id, $order_id, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.');
        } else {
            $error = 'Gagal memperbarui status pesanan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .detail-layout {
            min-height: calc(100vh - 70px);
            background-color: var(--light);
            padding: 2rem 0;
        }
        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .detail-header {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .detail-section {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .detail-section h2 {
            margin-bottom: 1.5rem;
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
        }
        .detail-item {
            margin-bottom: 1rem;
        }
        .detail-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }
        .detail-value {
            color: #666;
            line-height: 1.6;
        }
        .status-badge {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.3rem;
            font-weight: 600;
            font-size: 1rem;
        }
        .status-menunggu_bukti {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-menunggu_verifikasi {
            background-color: #cfe2ff;
            color: #084298;
        }
        .status-diterima {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-ditolak {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-siap_kirim {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-selesai {
            background-color: #d4edda;
            color: #155724;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .items-table th {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
        }
        .items-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .items-table tbody tr:last-child td {
            border-bottom: none;
        }
        .item-image {
            width: 60px;
            height: 60px;
            background-color: var(--accent);
            border-radius: 0.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--primary);
        }
        .payment-proof {
            background-color: var(--light);
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
        }
        .payment-proof-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 0.3rem;
            margin-bottom: 1rem;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        .btn-cancel:hover {
            opacity: 0.9;
        }
        .btn-back {
            background-color: var(--secondary);
            color: white;
        }
        .btn-back:hover {
            opacity: 0.9;
        }
        .btn-received {
            background-color: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .btn-received:hover {
            background-color: #218838;
        }
        .received-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-left: 4px solid #0c5460;
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 1rem;
        }
        .received-info p {
            margin: 0;
            color: #0c5460;
        }
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .detail-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="detail-layout">
        <div class="detail-container">
            <div class="detail-header">
                <div>
                    <h1><?php echo htmlspecialchars($order['no_pesanan']); ?></h1>
                    <p style="color: #666; margin-top: 0.5rem;"><?php echo formatDate($order['created_at']); ?></p>
                </div>
                <span class="status-badge status-<?php echo str_replace('_', '-', $order['status']); ?>">
                    <?php echo ORDER_STATUS[$order['status']]; ?>
                </span>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="detail-grid">
                <div>
                    <div class="detail-section">
                        <h2>📦 Produk Pesanan</h2>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <div class="item-image">
                                                <?php if ($item['foto_utama']): ?>
                                                    <img src="<?php echo PRODUCT_IMG_URL . $item['foto_utama']; ?>" alt="">
                                                <?php else: ?>
                                                    <span>🍞</span>
                                                <?php endif; ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($item['nama']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $item['jumlah']; ?></td>
                                    <td><?php echo formatCurrency($item['harga']); ?></td>
                                    <td><?php echo formatCurrency($item['harga'] * $item['jumlah']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="detail-section">
                        <h2>📍 Alamat Pengiriman</h2>
                        <div class="detail-item">
                            <div class="detail-label">Wilayah</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['wilayah']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Alamat Lengkap</div>
                            <div class="detail-value"><?php echo nl2br(htmlspecialchars($order['alamat_lengkap'])); ?></div>
                        </div>
                    </div>

                    <?php if ($order['status'] === 'ditolak'): ?>
                    <div class="detail-section">
                        <h2>⚠️ Alasan Penolakan</h2>
                        <div class="detail-value" style="color: #dc3545; font-weight: 500;">
                            <?php echo htmlspecialchars($order['alasan_penolakan']); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($payment): ?>
                    <div class="detail-section">
                        <h2>💳 Bukti Pembayaran</h2>
                        <div class="payment-proof">
                            <img src="<?php echo PAYMENT_PROOF_URL . $payment['bukti_file']; ?>" alt="Bukti Pembayaran" class="payment-proof-image">
                            <div class="detail-item">
                                <div class="detail-label">Diupload pada</div>
                                <div class="detail-value"><?php echo formatDate($payment['created_at']); ?></div>
                            </div>
                            <?php if ($payment['verified_at']): ?>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value" style="color: #28a745; font-weight: 500;">✓ Terverifikasi</div>
                            </div>
                            <?php else: ?>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value" style="color: #ffc107; font-weight: 500;">⏳ Menunggu Verifikasi</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php elseif ($order['status'] === 'menunggu_bukti'): ?>
                    <div class="detail-section" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
                        <h2>⚠️ Bukti Pembayaran Belum Diunggah</h2>
                        <p style="color: #856404; margin-bottom: 1rem;">Silakan upload bukti pembayaran untuk melanjutkan pemesanan Anda.</p>
                        <a href="<?php echo CUSTOMER_URL; ?>payment-upload.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">Upload Bukti Pembayaran</a>
                    </div>
                    <?php endif; ?>

                    <!-- TAMBAHAN: Section untuk konfirmasi penerimaan barang -->
                    <?php if ($order['status'] === 'siap_kirim'): ?>
                    <div class="detail-section">
                        <div class="received-info">
                            <p>📦 <strong>Paket Anda sedang dalam perjalanan!</strong></p>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Jika Anda sudah menerima barang, silakan klik tombol di bawah untuk mengkonfirmasi.</p>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="mark_received">
                            <button type="submit" class="btn-received" onclick="return confirm('Apakah Anda sudah menerima barang ini?');">
                                ✓ Saya Sudah Terima Barang
                            </button>
                        </form>
                    </div>
                    <?php elseif ($order['status'] === 'selesai'): ?>
                    <div class="detail-section">
                        <div class="received-info" style="background-color: #d4edda; border-left-color: #155724; color: #155724;">
                            <p>✓ <strong>Pesanan Selesai!</strong></p>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Terima kasih telah berbelanja di Brew Bakery. Kami tunggu pemesanan Anda berikutnya!</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="detail-section">
                        <h2>💰 Ringkasan Pembayaran</h2>
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span><?php echo formatCurrency($order['total_harga']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Ongkir (<?php echo htmlspecialchars($order['wilayah']); ?>)</span>
                            <span><?php echo formatCurrency($order['ongkir']); ?></span>
                        </div>
                        <div class="summary-total">
                            <span>Total Bayar</span>
                            <span><?php echo formatCurrency($order['total_bayar']); ?></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h2>📋 Informasi Pesanan</h2>
                        <div class="detail-item">
                            <div class="detail-label">No. Pesanan</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['no_pesanan']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tanggal Pesanan</div>
                            <div class="detail-value"><?php echo formatDate($order['created_at']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <span class="status-badge status-<?php echo str_replace('_', '-', $order['status']); ?>">
                                <?php echo ORDER_STATUS[$order['status']]; ?>
                            </span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <?php if ($order['status'] !== 'selesai' && $order['status'] !== 'ditolak'): ?>
                            <a href="<?php echo CUSTOMER_URL; ?>orders/tracking.php?id=<?php echo $order_id; ?>" class="btn-action btn-back">📍 Tracking</a>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'menunggu_bukti' || $order['status'] === 'menunggu_verifikasi'): ?>
                            <form method="POST" action="" style="flex: 1;">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn-action btn-cancel" style="width: 100%;" onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">✕ Batalkan</button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="<?php echo CUSTOMER_URL; ?>orders/" class="btn-action btn-back" style="flex: 1; text-align: center; text-decoration: none;">← Kembali</a>
                    </div>
                </div>
            </div>

            <!-- TAMBAHAN: Section untuk review produk -->
            <?php if ($order['status'] === 'selesai'): ?>
            <div class="detail-section">
                <h2>⭐ Berikan Rating & Review</h2>
                <p style="color: #666; margin-bottom: 1.5rem;">Bagikan pengalaman Anda dengan produk yang telah diterima</p>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($items as $item): 
                        // Check if already reviewed - PERBAIKAN: Gunakan order_id, bukan hanya product_id
                        $review_check = $conn->query("SELECT id FROM reviews WHERE product_id = {$item['product_id']} AND order_id = {$order_id}")->fetch_assoc();
                        $already_reviewed = !empty($review_check);
                    ?>
                    <div style="background-color: var(--light); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1rem; border-left: 4px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h4 style="margin: 0; color: var(--dark);"><?php echo htmlspecialchars($item['nama']); ?></h4>
                                <small style="color: #666;">Qty: <?php echo $item['jumlah']; ?></small>
                            </div>
                            <?php if ($already_reviewed): ?>
                            <span style="background-color: #28a745; color: white; padding: 0.5rem 1rem; border-radius: 0.3rem; font-size: 0.85rem; font-weight: 600;">✓ Sudah di-review</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$already_reviewed): ?>
                        <form method="POST" action="<?php echo API_URL; ?>add-review.php" style="display: flex; flex-direction: column; gap: 1rem;">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            
                            <!-- Rating Stars -->
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Rating:</label>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <div class="star-rating" style="display: flex; gap: 0.25rem;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating-<?php echo $item['product_id']; ?>-<?php echo $i; ?>" style="display: none;" required>
                                        <label for="rating-<?php echo $item['product_id']; ?>-<?php echo $i; ?>" class="star-label" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;">⭐</label>
                                        <?php endfor; ?>
                                    </div>
                                    <span id="rating-text-<?php echo $item['product_id']; ?>" style="color: #666; font-weight: 600;">Pilih rating</span>
                                </div>
                            </div>
                            
                            <!-- Review Text -->
                            <div>
                                <label for="ulasan-<?php echo $item['product_id']; ?>" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Ulasan (Opsional):</label>
                                <textarea name="ulasan" id="ulasan-<?php echo $item['product_id']; ?>" placeholder="Bagikan pengalaman Anda tentang produk ini..." style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.3rem; resize: vertical; min-height: 80px;"></textarea>
                            </div>
                            
                            <button type="submit" class="btn-action btn-verify" style="width: 100%; margin: 0;">✓ Kirim Review</button>
                        </form>
                        
                        <script>
                        (function() {
                            const productId = <?php echo $item['product_id']; ?>;
                            const ratingInputs = document.querySelectorAll('input[name="rating"][value]');
                            const starLabels = document.querySelectorAll(`label[for^="rating-${productId}-"]`);
                            const ratingText = document.getElementById(`rating-text-${productId}`);
                            
                            const ratingTexts = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Bagus', 'Sangat Bagus'];
                            
                            starLabels.forEach((label, index) => {
                                label.addEventListener('click', function() {
                                    const rating = index + 1;
                                    ratingText.textContent = ratingTexts[rating];
                                    
                                    // Update star colors
                                    starLabels.forEach((l, i) => {
                                        if (i < rating) {
                                            l.style.color = '#FFD700';
                                        } else {
                                            l.style.color = '#ddd';
                                        }
                                    });
                                });
                                
                                // Hover effect
                                label.addEventListener('mouseover', function() {
                                    const hoverRating = index + 1;
                                    starLabels.forEach((l, i) => {
                                        if (i < hoverRating) {
                                            l.style.color = '#FFD700';
                                        } else {
                                            l.style.color = '#ddd';
                                        }
                                    });
                                });
                            });
                            
                            // Reset on mouse leave
                            const container = document.querySelector(`.star-rating`);
                            if (container) {
                                container.addEventListener('mouseleave', function() {
                                    const checkedInput = document.querySelector(`input[name="rating"]:checked`);
                                    if (checkedInput) {
                                        const rating = parseInt(checkedInput.value);
                                        starLabels.forEach((l, i) => {
                                            l.style.color = i < rating ? '#FFD700' : '#ddd';
                                        });
                                    } else {
                                        starLabels.forEach(l => l.style.color = '#ddd');
                                    }
                                });
                            }
                        })();
                        </script>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
