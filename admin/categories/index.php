<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');

    if (empty($nama)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        $insert_query = "INSERT INTO categories (nama, deskripsi) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $nama, $deskripsi);
        
        if ($stmt->execute()) {
            header("Location: " . ADMIN_URL . "categories/?success=Kategori berhasil ditambahkan");
            exit();
        } else {
            $error = 'Terjadi kesalahan saat menambah kategori!';
        }
        $categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
    }
}

// Handle delete - WITH FOREIGN KEY CHECK
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if category is used in products
    $check_query = "SELECT COUNT(*) as product_count FROM products WHERE category_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result()->fetch_assoc();
    
    if ($check_result['product_count'] > 0) {
        $error = "Tidak dapat menghapus kategori! Kategori ini masih digunakan oleh " . $check_result['product_count'] . " produk.";
        $categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
    } else {
        $delete_query = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: " . ADMIN_URL . "categories/?success=Kategori berhasil dihapus");
            exit();
        } else {
            $error = 'Terjadi kesalahan saat menghapus kategori!';
            $categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
        }
    }
}

$page_success = isset($_GET['success']) ? sanitize($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
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
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #8B6F47;
            border-radius: 0.3rem;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6B4423;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        .form-actions {
            display: flex;
            gap: 1rem;
        }
        .btn-submit {
            flex: 1;
            padding: 0.75rem;
            background: #8B6F47;
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 111, 71, 0.3);
            background: #6B4423;
        }
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .categories-table th {
            background: #8B6F47;
            color: #F5E6D3;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        .categories-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .categories-table tbody tr:hover {
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
            display: inline-block;
            margin-right: 0.5rem;
            transition: all 0.3s;
        }
        .btn-edit {
            background-color: #D4A574;
            color: #6B4423;
        }
        .btn-edit:hover {
            background-color: #63482dff;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .btn-delete:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
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
    </style>
</head>
<body>
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>📂 Manajemen Kategori</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($page_success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($page_success); ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3>➕ Tambah Kategori Baru</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="nama">Nama Kategori *</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">➕ Tambah Kategori</button>
                    </div>
                </form>
            </div>

            <div class="form-section">
                <h3>Daftar Kategori</h3>
                <?php if (count($categories) > 0): ?>
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): 
                            // Get product count for each category
                            $prod_query = "SELECT COUNT(*) as prod_count FROM products WHERE category_id = ?";
                            $prod_stmt = $conn->prepare($prod_query);
                            $prod_stmt->bind_param("i", $cat['id']);
                            $prod_stmt->execute();
                            $prod_result = $prod_stmt->get_result()->fetch_assoc();
                            $product_count = $prod_result['prod_count'];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cat['nama']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cat['deskripsi']); ?></td>
                            <td>
                                <span style="<?php echo $product_count > 0 ? 'color: #ffc107; font-weight: bold;' : 'color: #28a745;'; ?>">
                                    <?php echo $product_count; ?> produk
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>categories/edit.php?id=<?php echo $cat['id']; ?>" class="btn-edit">✏️ Edit</a>
                                <a href="?delete=<?php echo $cat['id']; ?>" class="btn-delete" 
                                   onclick="<?php echo $product_count > 0 ? "alert('Kategori ini masih digunakan oleh " . $product_count . " produk'); return false;" : "return confirm('Yakin ingin menghapus kategori ini?');"; ?>"
                                   <?php echo $product_count > 0 ? 'disabled' : ''; ?>>
                                    🗑️ Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: #666; padding: 2rem;">Belum ada kategori. Silakan tambahkan kategori baru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
