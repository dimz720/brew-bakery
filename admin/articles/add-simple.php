<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $isi = $_POST['isi'] ?? '';
    $created_by = $_SESSION['admin_id'];
    
    // Validate
    if (empty($judul)) {
        $error = "Judul harus diisi!";
    } elseif (empty($isi)) {
        $error = "Isi artikel harus diisi!";
    } else {
        $foto = '';
        
        // Handle file upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($ext, $allowed)) {
                $error = "Format file tidak didukung! Gunakan: JPG, PNG, GIF, WEBP";
            } else {
                // Create upload directory if not exists
                $upload_dir = __DIR__ . '/../../uploads/articles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $foto = uniqid() . '_' . time() . '.' . $ext;
                $upload_path = $upload_dir . $foto;
                
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $error = "Gagal upload file!";
                    $foto = '';
                }
            }
        } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error_codes = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (MAX_FILE_SIZE)',
                UPLOAD_ERR_PARTIAL => 'File terupload sebagian',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temp tidak ada',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis ke disk',
                UPLOAD_ERR_EXTENSION => 'PHP extension menghentikan upload'
            ];
            $error = "Upload error: " . ($error_codes[$_FILES['foto']['error']] ?? 'Unknown');
        }
        
        // Insert to database
        if (!$error) {
            $sql = "INSERT INTO articles (judul, deskripsi, isi, foto, created_by) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                $error = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param("ssssi", $judul, $deskripsi, $isi, $foto, $created_by);
                
                if ($stmt->execute()) {
                    // Redirect to index with success message
                    header("Location: " . ADMIN_URL . "articles/index.php?success=" . urlencode("Artikel berhasil ditambahkan!"));
                    exit;
                } else {
                    $error = "Gagal menyimpan artikel: " . $stmt->error;
                }
                
                $stmt->close();
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 0.3rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 900px;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.3rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        
        .form-group label .required {
            color: #dc3545;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .file-label {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background-color: #f8f9fa;
            border: 2px dashed #8B6F47;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-label:hover {
            background-color: #F5E6D3;
            border-color: #6B4423;
        }
        
        .file-label-text {
            margin-left: 0.5rem;
            color: #6B4423;
        }
        
        .preview-container {
            margin-top: 1rem;
        }
        
        .preview-container img {
            max-width: 300px;
            max-height: 300px;
            border: 2px solid #8B6F47;
            border-radius: 0.3rem;
            display: block;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn-submit {
            background-color: #8B6F47;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.3rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #6B4423;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 0.75rem 2rem;
            text-decoration: none;
            border-radius: 0.3rem;
            font-size: 1rem;
            font-weight: 600;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        
        .form-help {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>📝 Tambah Artikel Baru</h1>
                <a href="<?php echo ADMIN_URL; ?>articles/index.php" class="btn-back">← Kembali</a>
            </div>

            <div class="form-container">
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="judul">
                            Judul Artikel <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="judul" 
                            name="judul" 
                            class="form-control" 
                            required 
                            placeholder="Masukkan judul artikel"
                            value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Singkat</label>
                        <textarea 
                            id="deskripsi" 
                            name="deskripsi" 
                            class="form-control" 
                            placeholder="Deskripsi singkat artikel (opsional)"
                            style="min-height: 100px;"
                        ><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                        <div class="form-help">Deskripsi singkat yang akan muncul di preview artikel</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="isi">
                            Isi Artikel <span class="required">*</span>
                        </label>
                        <textarea 
                            id="isi" 
                            name="isi" 
                            class="form-control" 
                            required 
                            placeholder="Tulis isi artikel di sini..."
                            style="min-height: 300px;"
                        ><?php echo htmlspecialchars($_POST['isi'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="foto">Foto Artikel</label>
                        <div class="file-input-wrapper">
                            <input 
                                type="file" 
                                id="foto" 
                                name="foto" 
                                accept="image/*" 
                                onchange="previewImage(this)"
                            >
                            <label for="foto" class="file-label">
                                <span style="font-size: 1.5rem;">📷</span>
                                <span class="file-label-text" id="file-label-text">Pilih foto artikel (JPG, PNG, GIF, WEBP)</span>
                            </label>
                        </div>
                        <div class="form-help">Maksimal ukuran file: 5MB. Format yang didukung: JPG, PNG, GIF, WEBP</div>
                        <div id="preview" class="preview-container"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Simpan Artikel</button>
                        <a href="<?php echo ADMIN_URL; ?>articles/index.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const labelText = document.getElementById('file-label-text');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                labelText.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                labelText.textContent = 'Pilih foto artikel (JPG, PNG, GIF, WEBP)';
            }
        }
    </script>
</body>
</html>