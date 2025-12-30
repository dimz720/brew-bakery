<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    redirect(ADMIN_URL . 'categories/');
}

// Get category
$category = $conn->query("SELECT * FROM categories WHERE id = $category_id")->fetch_assoc();

if (!$category) {
    redirect(ADMIN_URL . 'categories/');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');

    if (empty($nama)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        $update_query = "UPDATE categories SET nama = ?, deskripsi = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $nama, $deskripsi, $category_id);
        
        if ($stmt->execute()) {
            $success = 'Kategori berhasil diperbarui!';
            $category['nama'] = $nama;
            $category['deskripsi'] = $deskripsi;
        } else {
            $error = 'Gagal memperbarui kategori!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - Brew Bakery Admin</title>
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #8B6F47;
            border-radius: 0.3rem;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6B4423;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
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
            background: #6B4423;
            transform: translateY(-2px);
        }

        .btn-cancel {
            flex: 1;
            padding: 0.75rem;
            background: #D4A574;
            color: #6B4423;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #8B6F47;
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
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="edit-card">
                <h1>✏️ Edit Kategori</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="info-box">
                    <p>📌 <strong>ID Kategori:</strong> <?php echo $category_id; ?></p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nama">Nama Kategori *</label>
                        <input 
                            type="text" 
                            id="nama" 
                            name="nama" 
                            value="<?php echo htmlspecialchars($category['nama']); ?>" 
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea 
                            id="deskripsi" 
                            name="deskripsi"
                        ><?php echo htmlspecialchars($category['deskripsi']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
                        <a href="<?php echo ADMIN_URL; ?>categories/" class="btn-cancel">← Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
