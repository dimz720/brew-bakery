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

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: " . ADMIN_URL . "categories/?success=Kategori berhasil dihapus");
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
        .btn-delete {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: #dc3545;
            color: white;
            transition: all 0.3s;
        }
        .btn-delete:hover {
            background-color: #c82333;
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cat['nama']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cat['deskripsi']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $cat['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus kategori ini?')">🗑️ Hapus</a>
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
