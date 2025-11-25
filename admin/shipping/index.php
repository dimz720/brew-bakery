<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$shipping_costs = $conn->query("SELECT * FROM shipping_costs ORDER BY wilayah")->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

// Handle add only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'add') {
        $wilayah = sanitize($_POST['wilayah'] ?? '');
        $ongkir = (float)($_POST['ongkir'] ?? 0);
        
        if (empty($wilayah) || $ongkir <= 0) {
            $error = 'Wilayah dan ongkir harus diisi dengan benar!';
        } else {
            // Check if wilayah already exists
            $check = $conn->query("SELECT id FROM shipping_costs WHERE wilayah = '$wilayah'");
            if ($check->num_rows > 0) {
                $error = 'Wilayah sudah ada!';
            } else {
                $insert_query = "INSERT INTO shipping_costs (wilayah, ongkir) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("sd", $wilayah, $ongkir);
                
                if ($stmt->execute()) {
                    $success = 'Ongkir berhasil ditambahkan!';
                    $shipping_costs = $conn->query("SELECT * FROM shipping_costs ORDER BY wilayah")->fetch_all(MYSQLI_ASSOC);
                } else {
                    $error = 'Gagal menambah ongkir!';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM shipping_costs WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: " . ADMIN_URL . "shipping/?success=Ongkir%20berhasil%20dihapus");
        exit();
    }
}

$page_success = isset($_GET['success']) ? sanitize($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ongkir - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-content {
            padding: 2rem;
            flex: 1;
        }
        .content-header {
            margin-bottom: 2rem;
        }
        .form-section {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .form-section h3 {
            margin-bottom: 1rem;
            color: #6B4423;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 1rem;
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
        }
        .form-group input:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        .form-actions {
            display: flex;
            gap: 1rem;
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
        }
        .btn-submit:hover {
            background-color: #6B4423;
        }
        .shipping-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .shipping-table th {
            background-color: #8B6F47;
            color: #F5E6D3;
            padding: 1rem;
            text-align: left;
        }
        .shipping-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .shipping-table tbody tr:hover {
            background-color: #F5F1ED;
        }
        .btn-edit,
        .btn-delete {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .btn-edit {
            background-color: #D4A574;
            color: #6B4423;
        }
        .btn-edit:hover {
            background-color: #8B6F47;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
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
        .empty-state {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
   
    <div style="display: flex;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>🚚 Manajemen Ongkir</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($page_success): ?>
                <div class="alert alert-success"><?php echo $page_success; ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3>➕ Tambah Ongkir Baru</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label for="wilayah">Nama Wilayah/Kecamatan </label>
                        <input type="text" id="wilayah" name="wilayah" required placeholder="Contoh: dalam kota,dll">
                    </div>

                    <div class="form-group">
                        <label for="ongkir">Ongkir (Rp) *</label>
                        <input type="number" id="ongkir" name="ongkir" required min="1000" step="1000" placeholder="Contoh: 10000">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">➕ Tambah Ongkir</button>
                    </div>
                </form>
            </div>

            <div class="form-section">
                <h3>Daftar Ongkir</h3>
                <?php if (count($shipping_costs) > 0): ?>
                    <table class="shipping-table">
                        <thead>
                            <tr>
                                <th>Wilayah</th>
                                <th>Ongkir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shipping_costs as $cost): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cost['wilayah']); ?></strong></td>
                                <td><?php echo formatCurrency($cost['ongkir']); ?></td>
                                <td style="display: flex; gap: 0.5rem;">
                                    <a href="<?php echo ADMIN_URL; ?>shipping/edit.php?id=<?php echo $cost['id']; ?>" class="btn-edit">✏️ Edit</a>
                                    <a href="?delete=<?php echo $cost['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus ongkir ini?')">🗑️ Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Belum ada ongkir yang ditambahkan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

   
</body>
</html>