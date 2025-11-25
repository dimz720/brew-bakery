<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $nama = sanitize($_POST['nama'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');

    if (empty($nama)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        if ($action === 'add') {
            $insert_query = "INSERT INTO categories (nama, deskripsi) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ss", $nama, $deskripsi);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil ditambahkan!';
                $categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
            } else {
                $error = 'Terjadi kesalahan saat menambah kategori!';
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $update_query = "UPDATE categories SET nama = ?, deskripsi = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $nama, $deskripsi, $id);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil diperbarui!';
                $categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
            } else {
                $error = 'Terjadi kesalahan saat memperbarui kategori!';
            }
        }
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
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_category = null;
if ($edit_id > 0) {
    foreach ($categories as $cat) {
        if ($cat['id'] === $edit_id) {
            $edit_category = $cat;
            break;
        }
    }
}
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
            color: var(--primary);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
        }
        .btn-submit {
            flex: 1;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-reset {
            flex: 1;
            padding: 0.75rem;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
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
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        .categories-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .categories-table tbody tr:hover {
            background-color: var(--light);
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
            background-color: var(--secondary);
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
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
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($page_success): ?>
                <div class="alert alert-success"><?php echo $page_success; ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3><?php echo $edit_category ? '✏️ Edit Kategori' : '➕ Tambah Kategori Baru'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                    <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nama">Nama Kategori *</label>
                        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($edit_category['nama'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($edit_category['deskripsi'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <?php echo $edit_category ? '💾 Simpan Perubahan' : '➕ Tambah Kategori'; ?>
                        </button>
                        <?php if ($edit_category): ?>
                        <a href="<?php echo ADMIN_URL; ?>categories/" class="btn-reset" style="text-align: center; text-decoration: none;">← Batal Edit</a>
                        <?php endif; ?>
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
                            <td style="display: flex; gap: 0.5rem;">
                                <a href="?edit=<?php echo $cat['id']; ?>" class="btn-edit">Edit</a>
                                <a href="?delete=<?php echo $cat['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: #666;">Belum ada kategori.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
