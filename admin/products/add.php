<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);

    if (!$category_id || empty($nama) || $harga <= 0 || $stok < 0) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        // Handle photo upload - PERBAIKAN
        $foto_utama = '';
        if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] === UPLOAD_ERR_OK) {
            // Debug upload
            error_log("Upload attempt - file size: " . $_FILES['foto_utama']['size']);
            error_log("Upload attempt - file type: " . $_FILES['foto_utama']['type']);
            error_log("Upload attempt - error code: " . $_FILES['foto_utama']['error']);
            
            $filename = uploadImage($_FILES['foto_utama'], 'products/');
            if (!$filename) {
                $error = 'Gagal upload foto! Silakan cek ukuran file.';
                error_log("Photo upload failed");
            } else {
                $foto_utama = $filename;
                error_log("Photo uploaded successfully: " . $filename);
            }
        } else {
            // Handle upload error
            $upload_error = $_FILES['foto_utama']['error'] ?? 'unknown';
            error_log("Upload error code: " . $upload_error);
            
            switch ($upload_error) {
                case UPLOAD_ERR_INI_SIZE:
                    $error = 'File terlalu besar (melampaui upload_max_filesize di PHP)';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error = 'File terlalu besar (melampaui MAX_FILE_SIZE di form)';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error = 'File hanya terupload sebagian';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error = 'Tidak ada file yang dipilih';
                    break;
                default:
                    $error = 'Error upload file: ' . $upload_error;
            }
        }

        if (empty($error)) {
            $insert_query = "INSERT INTO products (category_id, nama, deskripsi, harga, stok, foto_utama) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issdis", $category_id, $nama, $deskripsi, $harga, $stok, $foto_utama);
            
            if ($stmt->execute()) {
                $success = 'Produk berhasil ditambahkan!';
                error_log("Product added with photo: " . $foto_utama);
            } else {
                $error = 'Gagal menambahkan produk!';
                error_log("Failed to insert product: " . $stmt->error);
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
    <title>Tambah Produk - Brew Bakery Admin</title>
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
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .form-section h3 {
            margin-bottom: 1rem;
            color: var(--primary);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            font-family: inherit;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .image-upload {
            border: 2px dashed var(--primary);
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            background-color: rgba(139, 111, 71, 0.05);
            transition: all 0.3s;
        }
        .image-upload:hover {
            border-color: var(--dark);
            background-color: rgba(139, 111, 71, 0.1);
        }
        .image-upload input {
            display: none;
        }
        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .preview-item {
            width: 100px;
            height: 100px;
            background-color: var(--accent);
            border-radius: 0.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn-submit {
            flex: 1;
            padding: 1rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: var(--dark);
        }
        .btn-cancel {
            flex: 1;
            padding: 1rem;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            background-color: var(--primary);
        }
    </style>
</head>
<body>
    
    
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="form-card">
                <h1>➕ Tambah Produk Baru</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3>Informasi Dasar</h3>
                        <div class="form-group">
                            <label for="nama">Nama Produk *</label>
                            <input type="text" id="nama" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Kategori *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi produk..."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Harga & Stok</h3>
                        <div class="form-group">
                            <label for="harga">Harga (Rp) *</label>
                            <input type="number" id="harga" name="harga" min="0" step="1000" required>
                        </div>
                        <div class="form-group">
                            <label for="stok">Stok (pcs) *</label>
                            <input type="number" id="stok" name="stok" min="0" value="0" required>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Foto Produk</h3>
                        <div class="form-group">
                            <label for="foto_utama">Foto Utama</label>
                            <div class="image-upload" onclick="document.getElementById('foto_utama').click()">
                                <p>📷 Klik untuk upload atau drag file di sini</p>
                                <p style="color: #666; font-size: 0.9rem;">Format: JPG, PNG, GIF (max 5MB)</p>
                            </div>
                            <input type="file" id="foto_utama" name="foto_utama" accept="image/*" onchange="previewImage(this)">
                            <div id="mainImagePreview" class="image-preview"></div>
                        </div>


                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Simpan Produk</button>
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
