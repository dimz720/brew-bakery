<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$shipping_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($shipping_id <= 0) {
    redirect(ADMIN_URL . 'shipping/');
}

// Get shipping cost
$shipping = $conn->query("SELECT * FROM shipping_costs WHERE id = $shipping_id")->fetch_assoc();

if (!$shipping) {
    redirect(ADMIN_URL . 'shipping/');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wilayah = sanitize($_POST['wilayah'] ?? '');
    $ongkir = (float)($_POST['ongkir'] ?? 0);
    
    if (empty($wilayah) || $ongkir <= 0) {
        $error = 'Wilayah dan ongkir harus diisi dengan benar!';
    } else {
        $update_query = "UPDATE shipping_costs SET wilayah = ?, ongkir = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sdi", $wilayah, $ongkir, $shipping_id);
        
        if ($stmt->execute()) {
            $success = 'Ongkir berhasil diperbarui!';
            $shipping['wilayah'] = $wilayah;
            $shipping['ongkir'] = $ongkir;
        } else {
            $error = 'Gagal memperbarui ongkir!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ongkir - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-content {
            padding: 2rem;
            flex: 1;
        }
        .content-header {
            margin-bottom: 2rem;
        }
        .edit-card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        .edit-card h1 {
            color: #6B4423;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #6B4423;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            box-sizing: border-box;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn-submit {
            flex: 1;
            padding: 0.75rem;
            background-color: #8B6F47;
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #6B4423;
        }
        .btn-cancel {
            flex: 1;
            padding: 0.75rem;
            background-color: #D4A574;
            color: #6B4423;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }
        .btn-cancel:hover {
            background-color: #8B6F47;
            color: white;
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
        .info-box {
            background-color: #F5F1ED;
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #8B6F47;
        }
        .info-box p {
            margin: 0;
            color: var(--dark);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
   
    
    <div style="display: flex;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>✏️ Edit Ongkir</h1>
            </div>

            <div class="edit-card">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="info-box">
                    <p>📌 <strong>ID Ongkir:</strong> <?php echo $shipping_id; ?></p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="wilayah">Nama Wilayah/kecamatan *</label>
                        <input 
                            type="text" 
                            id="wilayah" 
                            name="wilayah" 
                            value="<?php echo htmlspecialchars($shipping['wilayah']); ?>" 
                            required 
                            placeholder="Contoh: Jakarta Pusat, Bandung, dll"
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="ongkir">Ongkir (Rp) *</label>
                        <input 
                            type="number" 
                            id="ongkir" 
                            name="ongkir" 
                            value="<?php echo $shipping['ongkir']; ?>" 
                            required 
                            min="1000" 
                            step="1000" 
                            placeholder="Contoh: 10000"
                        >
                        <small style="color: #666; display: block; margin-top: 0.3rem;">
                            Harga saat ini: <strong><?php echo formatCurrency($shipping['ongkir']); ?></strong>
                        </small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
                        <a href="<?php echo ADMIN_URL; ?>shipping/" class="btn-cancel">← Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

  
</body>
</html>
