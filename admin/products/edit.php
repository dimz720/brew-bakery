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
    
    // UPDATED: Ambil dari form baru
    $diskon_tipe = sanitize($_POST['diskon_tipe'] ?? 'persentase');
    $diskon_nilai = (float)($_POST['diskon_nilai'] ?? 0);
    $diskon_aktif = isset($_POST['diskon_aktif']) ? 1 : 0;

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
            // UPDATED: Gunakan kolom baru
            $update_query = "UPDATE products SET category_id = ?, nama = ?, deskripsi = ?, harga = ?, stok = ?, diskon_tipe = ?, diskon_nilai = ?, diskon_aktif = ?, foto_utama = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            // FIX: Type string yang benar: "issdissidsi" punya 11 karakter
            // Seharusnya: "issdisdisi" = 10 karakter untuk 10 variabel
            // Urutan: category_id(i), nama(s), deskripsi(s), harga(d), stok(i), diskon_tipe(s), diskon_nilai(d), diskon_aktif(i), foto_utama(s), product_id(i)
            $stmt->bind_param("issdisdisi", $category_id, $nama, $deskripsi, $harga, $stok, $diskon_tipe, $diskon_nilai, $diskon_aktif, $foto_utama, $product_id);

            if ($stmt->execute()) {
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
            font-size: 16px;
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
            max-width: 1000px;
            width: 100%;
        }
        .form-card h1 {
            margin-bottom: 1.5rem;
            color: #6B4423;
            font-size: 2rem;
        }
        
        .form-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            align-items: start;
            margin-bottom: 2rem;
        }
        
        .form-section h3 {
            grid-column: 1 / -1;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
        
        .form-section .form-group {
            margin-bottom: 0;
        }
        
        .form-section .form-group label {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #6B4423;
        }
        
        .form-section input[type="text"],
        .form-section input[type="number"],
        .form-section select,
        .form-section textarea {
            font-size: 1rem;
            padding: 0.75rem;
            line-height: 1.5;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            width: 100%;
        }
        
        .form-section textarea {
            min-height: 150px;
        }
        
        .form-section:nth-of-type(3) {
            grid-template-columns: 1fr;
        }
        
        .image-upload p {
            font-size: 1rem;
        }
        
        .image-upload p:first-child {
            font-size: 1.1rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn-submit {
            flex: 1;
            padding: 1rem;
            background-color: #8B6F47;
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #6B4423;
        }
        .btn-cancel {
            flex: 1;
            padding: 1rem;
            background-color: #D4A574;
            color: #6B4423;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            background-color: #8B6F47;
            color: white;
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
                        <div class="form-group">
                            <label for="diskon_aktif">
                                <input type="checkbox" id="diskon_aktif" name="diskon_aktif" <?php echo ($product['diskon_aktif'] ?? 0) ? 'checked' : ''; ?>> Aktifkan Diskon
                            </label>
                        </div>

                        <div class="form-group" id="diskon-fields" style="display: <?php echo ($product['diskon_aktif'] ?? 0) ? 'block' : 'none'; ?>;">
                            <label for="diskon_tipe">Tipe Diskon *</label>
                            <select id="diskon_tipe" name="diskon_tipe" onchange="updateDiskonLabel()">
                                <option value="persentase" <?php echo ($product['diskon_tipe'] ?? '') === 'persentase' ? 'selected' : ''; ?>>Persentase (%)</option>
                                <option value="nominal" <?php echo ($product['diskon_tipe'] ?? '') === 'nominal' ? 'selected' : ''; ?>>Nominal (Rp)</option>
                            </select>
                        </div>

                        <div class="form-group" id="diskon-nilai-group" style="display: <?php echo ($product['diskon_aktif'] ?? 0) ? 'block' : 'none'; ?>;">
                            <label for="diskon_nilai">Nilai Diskon <span id="diskon-label"><?php echo ($product['diskon_tipe'] ?? 'persentase') === 'persentase' ? '(%)' : '(Rp)'; ?></span> *</label>
                            <input type="number" id="diskon_nilai" name="diskon_nilai" min="0" step="0.01" value="<?php echo $product['diskon_nilai'] ?? 0; ?>">
                            <small style="color: #666; display: block; margin-top: 0.3rem;" id="diskon-preview">
                                Harga diskon: <?php 
                                $discounted = calculateDiscountedPrice($product['harga'], $product['diskon_tipe'], $product['diskon_nilai'], $product['diskon_aktif']);
                                echo formatCurrency($discounted);
                                ?>
                            </small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Foto Produk</h3>
                        <div class="form-group">
                            <label for="foto_utama">Foto Utama</label>
                            <?php if ($product['foto_utama']): ?>
                            <div class="mb-2">
                                <img src="<?php echo PRODUCT_IMG_URL . $product['foto_utama']; ?>" alt="" style="max-width: 200px; border-radius: 0.3rem;">
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

        const diskonAktifCheckbox = document.getElementById('diskon_aktif');
        const diskonFields = document.getElementById('diskon-fields');
        const diskonNilaiGroup = document.getElementById('diskon-nilai-group');
        const hargaInput = document.getElementById('harga');
        const diskonTipeSelect = document.getElementById('diskon_tipe');
        const diskonNilaiInput = document.getElementById('diskon_nilai');

        function toggleDiskonFields() {
            if (diskonAktifCheckbox.checked) {
                diskonFields.style.display = 'block';
                diskonNilaiGroup.style.display = 'block';
            } else {
                diskonFields.style.display = 'none';
                diskonNilaiGroup.style.display = 'none';
            }
        }

        function updateDiskonLabel() {
            const label = diskonTipeSelect.value === 'persentase' ? '(%)' : '(Rp)';
            document.getElementById('diskon-label').textContent = label;
            updateDiskonPreview();
        }

        function updateDiskonPreview() {
            const harga = parseFloat(hargaInput.value) || 0;
            const diskonNilai = parseFloat(diskonNilaiInput.value) || 0;
            const diskonTipe = diskonTipeSelect.value;

            let discounted = harga;
            if (diskonTipe === 'persentase') {
                discounted = harga - (harga * (diskonNilai / 100));
            } else {
                discounted = harga - diskonNilai;
            }

            document.getElementById('diskon-preview').textContent = 
                'Harga diskon: Rp ' + Math.max(0, Math.round(discounted)).toLocaleString('id-ID');
        }

        diskonAktifCheckbox.addEventListener('change', toggleDiskonFields);
        hargaInput.addEventListener('input', updateDiskonPreview);
        diskonNilaiInput.addEventListener('input', updateDiskonPreview);
        diskonTipeSelect.addEventListener('change', updateDiskonLabel);
    </script>

    
</body>
</html>