<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect(ADMIN_URL . 'orders/');
}

$order = getOrderById($order_id);
if (!$order) {
    redirect(ADMIN_URL . 'orders/');
}

// Get order items
$query = "SELECT oi.*, p.nama FROM order_items oi 
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

// ============================================
// FIX #1: Define valid status transitions
// ============================================
$valid_next_statuses = [
    'menunggu_bukti' => [],
    'menunggu_verifikasi' => ['diterima', 'ditolak'],
    'diterima' => ['siap_kirim'],
    'ditolak' => [],
    'siap_kirim' => ['selesai'],
    'selesai' => []
];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'verify_payment') {
        $status = 'diterima';
        $update_query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            $verify_query = "UPDATE payment_proofs SET verified_at = NOW(), verified_by = ? WHERE order_id = ?";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $_SESSION['admin_id'], $order_id);
            $verify_stmt->execute();
            
            createNotification($order['customer_id'], $order_id, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.');
            $success = 'Pembayaran berhasil diverifikasi!';
        }
    } elseif ($action === 'reject_payment') {
        $alasan = sanitize($_POST['alasan'] ?? '');
        if (empty($alasan)) {
            $error = 'Alasan penolakan harus diisi!';
        } else {
            $status = 'ditolak';
            $update_query = "UPDATE orders SET status = ?, alasan_penolakan = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $status, $alasan, $order_id);
            
            if ($stmt->execute()) {
                createNotification($order['customer_id'], $order_id, 'Pembayaran Ditolak', 'Alasan: ' . $alasan);
                $success = 'Pembayaran berhasil ditolak!';
            }
        }
    } elseif ($action === 'update_status') {
        $new_status = sanitize($_POST['status'] ?? '');
        
        // ============================================
        // FIX #1: Validate status transition
        // ============================================
        if (!in_array($new_status, $valid_next_statuses[$order['status']])) {
            $error = 'Status tidak valid! Status hanya bisa maju ke tahap selanjutnya.';
        } else {
            $update_query = "UPDATE orders SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_status, $order_id);
            
            if ($stmt->execute()) {
                createNotification($order['customer_id'], $order_id, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: ' . ORDER_STATUS[$new_status]);
                $success = 'Status pesanan berhasil diperbarui!';
            }
        }
    }
    
    if ($success) {
        $order = getOrderById($order_id);
    }
}

// Get customer info
$customer = getCustomerById($order['customer_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-content {
            padding: 2rem;
            flex: 1;
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
            color: #6B4423;
            border-bottom: 2px solid #8B6F47;
            padding-bottom: 0.5rem;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            background-color: #8B6F47;
            color: #F5E6D3;
            padding: 0.75rem;
            text-align: left;
        }
        .items-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .status-badge {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.3rem;
            font-weight: 600;
            background-color: #8B6F47;
            color: white;
        }
        .payment-proof-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 0.3rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .payment-proof-image:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .action-form {
            background-color: #F5F1ED;
            padding: 1.5rem;
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
        }
        .btn-verify {
            background-color: #28a745;
            color: white;
        }
        .btn-verify:hover {
            background-color: #218838;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        .btn-reject:hover {
            background-color: #c82333;
        }
        
        /* Lightbox Modal */
        .lightbox-modal {
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
        
        .lightbox-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lightbox-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
            animation: zoomIn 0.3s;
        }
        
        .lightbox-image {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 0.5rem;
        }
        
        .lightbox-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            transition: transform 0.2s;
        }
        
        .lightbox-close:hover {
            transform: scale(1.2);
        }
        
        .lightbox-hint {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 14px;
            text-align: center;
            white-space: nowrap;
        }
        
        /* FIX #2: Verified payment proof style */
        .verified-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .verified-info {
            background-color: #d1e7dd;
            border-left: 4px solid #28a745;
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 1rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.8); }
            to { transform: scale(1); }
        }
    </style>
</head>
<body>
   
    
    <div style="display: flex;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <h1>📦 Detail Pesanan</h1>
            <p style="color: #666; margin-bottom: 2rem;"><?php echo htmlspecialchars($order['no_pesanan']); ?></p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="detail-grid">
                <div>
                    <div class="detail-section">
                        <h2>📦 Item Pesanan</h2>
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
                                    <td><?php echo htmlspecialchars($item['nama']); ?></td>
                                    <td><?php echo $item['jumlah']; ?></td>
                                    <td><?php echo formatCurrency($item['harga']); ?></td>
                                    <td><?php echo formatCurrency($item['harga'] * $item['jumlah']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="detail-section">
                        <h2>👤 Data Pelanggan</h2>
                        <div style="margin-bottom: 1rem;">
                            <strong>Nama:</strong> <?php echo htmlspecialchars($customer['nama']); ?>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>No. HP:</strong> <?php echo htmlspecialchars($customer['no_hp']); ?>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h2>📍 Alamat Pengiriman</h2>
                        <div style="margin-bottom: 1rem;">
                            <strong>Wilayah:</strong> <?php echo htmlspecialchars($order['wilayah']); ?>
                        </div>
                        <div>
                            <strong>Alamat Lengkap:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['alamat_lengkap'])); ?>
                        </div>
                    </div>

                    <?php 
                    // ============================================
                    // FIX #2: Show payment proof even after verification
                    // ============================================
                    if ($payment): 
                    ?>
                    <div class="detail-section">
                        <h2>💳 Bukti Pembayaran</h2>
                        
                        <?php if ($payment['verified_at']): ?>
                            <div class="verified-info">
                                <span class="verified-badge">✓ Terverifikasi</span>
                                <p style="margin: 0.5rem 0 0 0; color: #0f5132;">
                                    <strong>Diverifikasi pada:</strong> <?php echo formatDate($payment['verified_at']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <div style="position: relative;">
                            <img src="<?php echo PAYMENT_PROOF_URL . $payment['bukti_file']; ?>" 
                                 alt="Bukti Pembayaran" 
                                 class="payment-proof-image" 
                                 id="paymentProofImg"
                                 title="Klik untuk memperbesar">
                            <p style="text-align: center; color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
                                💡 Klik gambar untuk memperbesar
                            </p>
                        </div>
                        
                        <?php if (!$payment['verified_at'] && $order['status'] === 'menunggu_verifikasi'): ?>
                        <form method="POST" action="" class="action-form">
                            <input type="hidden" name="action" value="verify_payment">
                            <button type="submit" class="btn-action btn-verify" style="width: 100%; margin-bottom: 0.5rem;">✓ Verifikasi Pembayaran</button>
                        </form>

                        <form method="POST" action="" class="action-form" style="margin-bottom: 0;">
                            <input type="hidden" name="action" value="reject_payment">
                            <textarea name="alasan" placeholder="Alasan penolakan..." style="width: 100%; padding: 0.75rem; margin-bottom: 0.5rem; border: 1px solid #ddd; border-radius: 0.3rem;" required></textarea>
                            <button type="submit" class="btn-action btn-reject" style="width: 100%;">✕ Tolak Pembayaran</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="detail-section">
                        <h2>📋 Ringkasan</h2>
                        <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Subtotal</span>
                                <span><?php echo formatCurrency($order['total_harga']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Ongkir</span>
                                <span><?php echo formatCurrency($order['ongkir']); ?></span>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1.5rem; font-weight: bold; color: var(--primary); margin-bottom: 1rem;">
                            <span>Total</span>
                            <span><?php echo formatCurrency($order['total_bayar']); ?></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h2>📊 Status</h2>
                        <span class="status-badge" style="background-color: var(--primary); color: white; margin-bottom: 1rem; display: block; text-align: center;">
                            <?php echo ORDER_STATUS[$order['status']]; ?>
                        </span>

                        <?php if ($order['status'] !== 'selesai' && $order['status'] !== 'ditolak'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_status">
                            <div style="margin-bottom: 1rem;">
                                <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Ubah Status</label>
                                <select name="status" id="status" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.3rem;">
                                    <?php 
                                    // ============================================
                                    // FIX #1: Only show valid next statuses
                                    // ============================================
                                    $next_statuses = $valid_next_statuses[$order['status']];
                                    if (empty($next_statuses)): 
                                    ?>
                                        <option value="">Tidak ada status selanjutnya</option>
                                    <?php else: ?>
                                        <?php foreach ($next_statuses as $status_key): ?>
                                        <option value="<?php echo $status_key; ?>"><?php echo ORDER_STATUS[$status_key]; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <?php if (!empty($next_statuses)): ?>
                            <button type="submit" class="btn-action btn-verify" style="width: 100%;">Update Status</button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>

                    <div class="detail-section">
                        <h2>🔗 Aksi</h2>
                        <a href="<?php echo ADMIN_URL; ?>orders/" style="display: block; padding: 0.75rem; background-color: var(--secondary); color: white; text-align: center; text-decoration: none; border-radius: 0.3rem; font-weight: 600;">← Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal">
        <div class="lightbox-content">
            <button class="lightbox-close" id="closeBtn">&times;</button>
            <img id="lightboxImage" class="lightbox-image" src="" alt="Bukti Pembayaran">
            <div class="lightbox-hint">Klik di luar gambar atau tombol × untuk menutup</div>
        </div>
    </div>

    <script>
        // Lightbox functionality
        const paymentImg = document.getElementById('paymentProofImg');
        const lightboxModal = document.getElementById('lightboxModal');
        const lightboxImage = document.getElementById('lightboxImage');
        const closeBtn = document.getElementById('closeBtn');

        if (paymentImg) {
            // Open lightbox when clicking the image
            paymentImg.addEventListener('click', function() {
                lightboxImage.src = this.src;
                lightboxModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Close lightbox when clicking the close button
        closeBtn.addEventListener('click', function() {
            lightboxModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Close lightbox when clicking outside the image
        lightboxModal.addEventListener('click', function(e) {
            if (e.target === lightboxModal) {
                lightboxModal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });

        // Close lightbox with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightboxModal.classList.contains('active')) {
                lightboxModal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    </script>
   
</body>
</html>