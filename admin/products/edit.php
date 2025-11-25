<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect(ADMIN_URL . 'products/');
}

$product = getProductById($product_id);
if (!$product) {
    redirect(ADMIN_URL . 'products/');
}

// Get product photos
$photos_query = "SELECT * FROM product_photos WHERE product_id = ?";
$stmt = $conn->prepare($photos_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $nama = sanitize($_POST['nama'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);

    if (!$category_id || empty($nama) || $harga <= 0 || $stok < 0) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        // Handle main image upload
        $foto_utama = $product['foto_utama'];
        if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] === UPLOAD_ERR_OK) {
            if ($product['foto_utama']) {
                deleteImage($product['foto_utama'], PRODUCT_IMG_DIR);
            }
            $foto_utama = uploadImage($_FILES['foto_utama'], PRODUCT_IMG_DIR);
            if (!$foto_utama) {
                $error = 'Format foto tidak didukung atau ukuran terlalu besar!';
            }
        }

        if (!$error) {
            $update_query = "UPDATE products SET category_id = ?, nama = ?, deskripsi = ?, harga = ?, stok = ?, foto_utama = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("issdisi", $category_id, $nama, $deskripsi, $harga, $stok, $foto_utama, $product_id);

            if ($stmt->execute()) {
                // Handle additional photos
                if (isset($_FILES['photos'])) {
                    for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
                        if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['photos']['name'][$i],
                                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                                'error' => $_FILES['photos']['error'][$i],
                                'size' => $_FILES['photos']['size'][$i]
                            ];
                            
                            $photo_filename = uploadImage($file, PRODUCT_IMG_DIR);
                            if ($photo_filename) {
                                $photo_query = "INSERT INTO product_photos (product_id, foto) VALUES (?, ?)";
                                $photo_stmt = $conn->prepare($photo_query);
                                $photo_stmt->bind_param("is", $product_id, $photo_filename);
                                $photo_stmt->execute();
                            }
                        }
                    }
                }

                $success = 'Produk berhasil diperbarui!';
                $product = getProductById($product_id);
            } else {
                $error = 'Terjadi kesalahan saat menyimpan produk!';
            }
        }
    }
}

// Handle delete photo
if (isset($_GET['delete_photo'])) {
    $photo_id = (int)$_GET['delete_photo'];
    $photo_query = "SELECT foto FROM product_photos WHERE id = ? AND product_id = ?";
    $stmt = $conn->prepare($photo_query);
    $stmt->bind_param("ii", $photo_id, $product_id);
    $stmt->execute();
    $photo = $stmt->get_result()->fetch_assoc();
    
    if ($photo) {
        deleteImage($photo['foto'], PRODUCT_IMG_DIR);
        $delete_query = "DELETE FROM product_photos WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        header("Location: " . ADMIN_URL . "products/edit.php?id=" . $product_id . "&success=Foto berhasil dihapus");
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
    <title>Edit Produk - Brew Bakery Admin</title>
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
        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
        }
        .form-card h1 {
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
    </style>
</head>
<body>
    
    
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="form-card">
                <h1>✏️ Edit Produk</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($page_success): ?>
                    <div class="alert alert-success"><?php echo $page_success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3>Informasi Dasar</h3>
                        <div class="form-group">
                            <label for="nama">Nama Produk *</label>
                            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($product['nama']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Kategori *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] === $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nama']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Harga & Stok</h3>
                        <div class="form-group">
                            <label for="harga">Harga (Rp) *</label>
                            <input type="number" id="harga" name="harga" value="<?php echo $product['harga']; ?>" min="0" step="1000" required>
                        </div>
                        <div class="form-group">
                            <label for="stok">Stok (pcs) *</label>
                            <input type="number" id="stok" name="stok" value="<?php echo $product['stok']; ?>" min="0" required>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Foto Produk</h3>
                        <div class="form-group">
                            <label for="foto_utama">Foto Utama</label>
                            <?php if ($product['foto_utama']): ?>
                            <div style="margin-bottom: 1rem;">
                                <img src="<?php echo PRODUCT_IMG_DIR . $product['foto_utama']; ?>" alt="" style="max-width: 200px; border-radius: 0.3rem;">
                                <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">Upload foto baru untuk mengganti</p>
                            </div>
                            <?php endif; ?>
                            <div class="image-upload" onclick="document.getElementById('foto_utama').click()">
                                <p>📷 Klik untuk upload atau drag file di sini</p>
                                <p style="color: #666; font-size: 0.9rem;">Format: JPG, PNG, GIF (max 5MB)</p>
                            </div>
                            <input type="file" id="foto_utama" name="foto_utama" accept="image/*" onchange="previewImage(this)">
                            <div id="mainImagePreview" class="image-preview"></div>
                        </div>

                     

                        <div class="form-group">
                            <label for="photos">Tambah Foto Baru</label>
                            <div class="image-upload" onclick="document.getElementById('photos').click()">
                                <p>📷 Klik untuk upload atau drag file di sini</p>
                                <p style="color: #666; font-size: 0.9rem;">Bisa upload multiple files</p>
                            </div>
                            <input type="file" id="photos" name="photos[]" accept="image/*" multiple onchange="previewImages(this)">
                            <div id="additionalImagesPreview" class="image-preview"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
                        <a href="<?php echo ADMIN_URL; ?>products/" class="btn-cancel">← Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('mainImagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                    preview.appendChild(div);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewImages(input) {
            const preview = document.getElementById('additionalImagesPreview');
            preview.innerHTML = '';
            
            if (input.files) {
                for (let i = 0; i < input.files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                        preview.appendChild(div);
                    };
                    reader.readAsDataURL(input.files[i]);
                }
            }
        }
    </script>

    
</body>
</html>