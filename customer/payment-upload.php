<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkCustomerAuth();

$customer_id = $_SESSION['customer_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Get order
$order = getOrderById($order_id);

if (!$order || $order['customer_id'] !== $customer_id) {
    redirect(CUSTOMER_URL . 'orders/');
}

$error = '';
$success = '';

// Check if payment proof already exists
$payment_proof = $conn->query("SELECT * FROM payment_proofs WHERE order_id = $order_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['bukti_bayar']) || $_FILES['bukti_bayar']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Pilih file bukti pembayaran!';
    } else {
        // Delete old file if exists
        if ($payment_proof && !empty($payment_proof['bukti_file'])) {
            deleteImage($payment_proof['bukti_file'], PAYMENT_PROOF_DIR);
        }
        
        // Upload new file
        $new_file = uploadImage($_FILES['bukti_bayar'], PAYMENT_PROOF_DIR);
        
        if (!$new_file) {
            $error = 'Format file tidak didukung atau ukuran terlalu besar!';
        } else {
            if ($payment_proof) {
                // Update existing
                $update_query = "UPDATE payment_proofs SET bukti_file = ? WHERE order_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("si", $new_file, $order_id);
            } else {
                // Insert new
                $insert_query = "INSERT INTO payment_proofs (order_id, bukti_file) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("is", $order_id, $new_file);
            }
            
            if ($stmt->execute()) {
                // Update order status
                $update_order_query = "UPDATE orders SET status = 'menunggu_verifikasi' WHERE id = ?";
                $stmt = $conn->prepare($update_order_query);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                
                // Create notification
                createNotification($customer_id, $order_id, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.');
                
                $success = 'Bukti pembayaran berhasil diupload! Admin akan memverifikasi dalam 1x24 jam.';
                $payment_proof = $conn->query("SELECT * FROM payment_proofs WHERE order_id = $order_id")->fetch_assoc();
                
                // ← TAMBAHAN: Auto-redirect setelah 2 detik
                header("Refresh: 2; url=" . CUSTOMER_URL . "orders/detail.php?id=" . $order_id);
            } else {
                $error = 'Gagal mengupload bukti pembayaran!';
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
    <title>Upload Bukti Pembayaran - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .payment-layout {
            min-height: calc(100vh - 70px);
            background-color: var(--light);
            padding: 2rem 0;
        }
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .payment-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .payment-card h1 {
            margin-bottom: 1rem;
        }
        .order-info {
            background-color: var(--light);
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 2rem;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
        }
        .info-value {
            color: var(--primary);
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .file-upload {
            border: 2px dashed var(--primary);
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            background-color: rgba(139, 111, 71, 0.05);
            transition: all 0.3s;
        }
        .file-upload:hover {
            border-color: var(--dark);
            background-color: rgba(139, 111, 71, 0.1);
        }
        .file-upload input {
            display: none;
        }
        .file-upload.dragover {
            border-color: var(--dark);
            background-color: rgba(139, 111, 71, 0.1);
        }
        .uploaded-image {
            margin-top: 1rem;
        }
        .uploaded-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 0.3rem;
        }
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        .btn-submit:hover {
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
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-verified {
            background-color: #d4edda;
            color: #155724;
        }
        .instructions {
            background-color: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.3rem;
        }
        .instructions h3 {
            margin-top: 0;
            color: #004085;
        }
        .instructions ol {
            margin: 0;
            padding-left: 1.5rem;
        }
        .instructions li {
            margin-bottom: 0.5rem;
            color: #004085;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="payment-layout">
        <div class="payment-container">
            <a href="<?php echo CUSTOMER_URL; ?>orders/" style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Kembali ke Pesanan</a>
            
            <div class="payment-card">
                <h1>💳 Upload Bukti Pembayaran</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                            <a href="<?php echo CUSTOMER_URL; ?>orders/detail.php?id=<?php echo $order_id; ?>" class="btn btn-primary">
                                📋 Lihat Detail Order
                            </a>
                            <a href="<?php echo CUSTOMER_URL; ?>orders/tracking.php?id=<?php echo $order_id; ?>" class="btn btn-primary" style="background-color: var(--secondary);">
                                📍 Tracking Pesanan
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="order-info">
                    <h3 style="margin-top: 0;">📦 Informasi Pesanan</h3>
                    <div class="info-row">
                        <span class="info-label">No. Pesanan:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['no_pesanan']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Pembayaran:</span>
                        <span class="info-value"><?php echo formatCurrency($order['total_bayar']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="status-badge <?php echo $order['status'] === 'menunggu_bukti' ? 'status-pending' : 'status-verified'; ?>">
                            <?php echo ORDER_STATUS[$order['status']] ?? $order['status']; ?>
                        </span>
                    </div>
                </div>

                <div class="instructions">
                    <h3>📝 Petunjuk Pembayaran</h3>
                    <ol>
                        <li><strong>Transfer ke rekening kami</strong> sesuai dengan total yang tertera</li>
                        <li><strong>Ambil screenshot atau foto</strong> dari bukti transfer/pembayaran</li>
                        <li><strong>Upload file bukti</strong> melalui form di bawah (JPG, PNG, atau GIF)</li>
                        <li><strong>Admin akan verifikasi</strong> dalam waktu 1x24 jam</li>
                        <li><strong>Pesanan akan diproses</strong> setelah pembayaran dikonfirmasi</li>
                    </ol>
                </div>

                <?php if ($payment_proof && !empty($payment_proof['bukti_file'])): ?>
                <div style="background-color: #d4edda; padding: 1rem; border-radius: 0.3rem; margin-bottom: 1.5rem;">
                    <p style="margin: 0; color: #155724;"><strong>✓ Bukti pembayaran sudah diupload</strong></p>
                    <p style="margin: 0.5rem 0 0 0; color: #155724; font-size: 0.9rem;">
                        Uploaded: <?php echo formatDate($payment_proof['created_at']); ?>
                        <?php if (!empty($payment_proof['verified_at'])): ?>
                        <br>Diverifikasi: <?php echo formatDate($payment_proof['verified_at']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Upload Bukti Pembayaran *</label>
                        <div class="file-upload" id="fileUpload" onclick="document.getElementById('bukti_bayar').click()">
                            <p style="margin: 0; font-size: 2rem;">📷</p>
                            <p style="margin: 0.5rem 0 0 0;">Klik atau drag file di sini</p>
                            <p style="margin: 0; font-size: 0.85rem; color: #666;">Format: JPG, PNG, GIF (Max 5MB)</p>
                        </div>
                        <input type="file" id="bukti_bayar" name="bukti_bayar" accept="image/*" required onchange="previewFile(this)">
                        <div id="preview" class="uploaded-image"></div>
                    </div>

                    <button type="submit" class="btn-submit">✓ Upload Bukti Pembayaran</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const fileUpload = document.getElementById('fileUpload');
        const fileInput = document.getElementById('bukti_bayar');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUpload.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            fileUpload.classList.add('dragover');
        }

        function unhighlight(e) {
            fileUpload.classList.remove('dragover');
        }

        fileUpload.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;
            fileInput.files = files;
            previewFile(fileInput);
        }

        function previewFile(input) {
            const preview = document.getElementById('preview');   reader.readAsDataURL(input.files[0]);
            preview.innerHTML = '';            }
            
            if (input.files && input.files[0]) {cript>
                const reader = new FileReader();
                reader.onload = function(e) {    <?php include __DIR__ . '/../includes/footer.php'; ?>













</html></body>    <?php include __DIR__ . '/../includes/footer.php'; ?>    </script>        }            }                reader.readAsDataURL(input.files[0]);                };                    preview.appendChild(img);                    img.src = e.target.result;                    const img = document.createElement('img');</body>
</html>
