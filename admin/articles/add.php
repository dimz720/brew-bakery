<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $isi = $_POST['isi'] ?? '';
    $created_by = $_SESSION['admin_id'];  // ← UBAH dari user_id ke admin_id

    if (empty($judul) || empty($isi)) {
        $error = 'Judul dan isi artikel harus diisi!';
    } else {
        $foto = '';
        
        // Handle image upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = uploadImage($_FILES['foto'], ARTICLE_IMG_DIR);
            if (!$foto) {
                $error = 'Format foto tidak didukung atau ukuran terlalu besar!';
            }
        }

        if (!$error) {
            $insert_query = "INSERT INTO articles (judul, deskripsi, isi, foto, created_by) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssssi", $judul, $deskripsi, $isi, $foto, $created_by);

            if ($stmt->execute()) {
                $success = 'Artikel berhasil ditambahkan!';
                header("Location: " . ADMIN_URL . "articles/?success=" . urlencode($success));
                exit();
            } else {
                $error = 'Terjadi kesalahan saat menyimpan artikel!';
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
    <title>Tambah Artikel - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
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
            max-width: 900px;
        }
        .form-card h1 {
            margin-bottom: 1.5rem;
            color: #6B4423;
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
            color: #6B4423;
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
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            font-family: inherit;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .image-upload {
            border: 2px dashed #8B6F47;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            background-color: rgba(139, 111, 71, 0.05);
            transition: all 0.3s;
        }
        .image-upload:hover {
            border-color: #6B4423;
            background-color: rgba(139, 111, 71, 0.1);
        }
        .image-upload input {
            display: none;
        }
        .image-preview {
            margin-top: 1rem;
            text-align: center;
        }
        .preview-img {
            max-width: 300px;
            max-height: 300px;
            border-radius: 0.3rem;
        }
        .ck-editor__editable {
            min-height: 400px;
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
                <h1>➕ Tambah Artikel Baru</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3>Informasi Dasar</h3>
                        <div class="form-group">
                            <label for="judul">Judul Artikel *</label>
                            <input type="text" id="judul" name="judul" required>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Singkat</label>
                            <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi singkat artikel..."></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Konten Artikel</h3>
                        <div class="form-group">
                            <label for="isi">Isi Artikel *</label>
                            <textarea id="isi" name="isi" required></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Gambar Sampul</h3>
                        <div class="form-group">
                            <label for="foto">Foto Sampul</label>
                            <div class="image-upload" onclick="document.getElementById('foto').click()">
                                <p>📷 Klik untuk upload atau drag file di sini</p>
                                <p style="color: #666; font-size: 0.9rem;">Format: JPG, PNG, GIF (max 5MB)</p>
                            </div>
                            <input type="file" id="foto" name="foto" accept="image/*" onchange="previewImage(this)">
                            <div id="imagePreview" class="image-preview"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Simpan Artikel</button>
                        <a href="<?php echo ADMIN_URL; ?>articles/" class="btn-cancel">← Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        ClassicEditor.create(document.getElementById('isi'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo']
        }).catch(error => console.error(error));

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-img';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

   
</body>
</html>
