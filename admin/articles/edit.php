<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    redirect(ADMIN_URL . 'articles/');
}

$query = "SELECT * FROM articles WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    redirect(ADMIN_URL . 'articles/');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $isi = $_POST['isi'] ?? '';

    if (empty($judul) || empty($isi)) {
        $error = 'Judul dan isi artikel harus diisi!';
    } else {
        $foto = $article['foto'];
        
        // Handle image upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            if ($article['foto']) {
                deleteImage($article['foto'], ARTICLE_IMG_DIR);
            }
            $foto = uploadImage($_FILES['foto'], ARTICLE_IMG_DIR);
            if (!$foto) {
                $error = 'Format foto tidak didukung atau ukuran terlalu besar!';
            }
        }

        if (!$error) {
            $update_query = "UPDATE articles SET judul = ?, deskripsi = ?, isi = ?, foto = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssi", $judul, $deskripsi, $isi, $foto, $article_id);

            if ($stmt->execute()) {
                $success = 'Artikel berhasil diperbarui!';
                $query = "SELECT * FROM articles WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $article_id);
                $stmt->execute();
                $article = $stmt->get_result()->fetch_assoc();
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
    <title>Edit Artikel - Brew Bakery Admin</title>
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
            background: #fff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-card h1 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #6B4423;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #6B4423;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #6B4423;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }

        .image-upload {
            border: 2px dashed #8B6F47;
            padding: 1rem;
            text-align: center;
            border-radius: 0.3rem;
            cursor: pointer;
            background-color: rgba(139, 111, 71, 0.05);
            transition: all 0.3s;
        }

        .image-upload:hover {
            border-color: #6B4423;
            background-color: rgba(139, 111, 71, 0.1);
        }

        .image-preview {
            margin-top: 1rem;
        }

        .preview-img {
            max-width: 100%;
            height: auto;
            border-radius: 0.3rem;
        }

        .btn-submit,
        .btn-cancel {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.3rem;
            font-size: 1rem;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-submit {
            background: #8B6F47;
            color: white;
        }

        .btn-submit:hover {
            background: #6B4423;
        }

        .btn-cancel {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.3rem;
            font-size: 1rem;
            background: #D4A574;
            color: #6B4423;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
            margin-left: 1rem;
        }

        .btn-cancel:hover {
            background: #8B6F47;
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
   
    
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="form-card">
                <h1>✏️ Edit Artikel</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3>Informasi Dasar</h3>
                        <div class="form-group">
                            <label for="judul">Judul Artikel *</label>
                            <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($article['judul']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Singkat</label>
                            <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($article['deskripsi']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Konten Artikel</h3>
                        <div class="form-group">
                            <label for="isi">Isi Artikel *</label>
                            <textarea id="isi" name="isi"><?php echo htmlspecialchars($article['isi']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Gambar Sampul</h3>
                        <div class="form-group">
                            <label for="foto">Foto Sampul</label>
                            <?php if ($article['foto']): ?>
                            <div style="margin-bottom: 1rem;">
                                <img src="<?php echo ARTICLE_IMG_DIR . $article['foto']; ?>" alt="" style="max-width: 300px; border-radius: 0.3rem;">
                                <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">Upload foto baru untuk mengganti</p>
                            </div>
                            <?php endif; ?>
                            <div class="image-upload" onclick="document.getElementById('foto').click()">
                                <p>📷 Klik untuk upload atau drag file di sini</p>
                                <p style="color: #666; font-size: 0.9rem;">Format: JPG, PNG, GIF (max 5MB)</p>
                            </div>
                            <input type="file" id="foto" name="foto" accept="image/*" onchange="previewImage(this)">
                            <div id="imagePreview" class="image-preview"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
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