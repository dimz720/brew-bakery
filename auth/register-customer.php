<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Sekarang isLoggedIn() sudah terdefinisi
if (isset($_SESSION['customer_id'])) {
    redirect(CUSTOMER_URL . 'dashboard.php');
}

$error = '';
$success = '';
$email = '';
$nama = '';
$no_hp = '';  // ← TAMBAHKAN INI

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $no_hp = sanitize($_POST['no_hp'] ?? '');

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password harus diisi!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM customers WHERE email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hashed_password = hashPassword($password);
            
            $insert_query = "INSERT INTO customers (nama, email, password, no_hp) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssss", $nama, $email, $hashed_password, $no_hp);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
                $nama = '';
                $email = '';
                $no_hp = '';
            } else {
                $error = 'Terjadi kesalahan saat registrasi!';
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
    <title>Daftar Customer - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6B4423 0%, #8B6F47 100%);
            min-height: 100vh;
        }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .register-card {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 3rem 2.5rem;
            border-radius: 1.2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .register-header h1 {
            color: #6B4423;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .register-header p {
            color: #8B6F47;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #6B4423;
            font-size: 0.95rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 2px solid #D4A574;
            border-radius: 0.6rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #FFF;
        }
        .form-group input:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 0 0 4px rgba(139, 111, 71, 0.1);
            background: #FFFBF8;
        }
        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #8B6F47 0%, #6B4423 100%);
            color: white;
            border: none;
            border-radius: 0.6rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            box-shadow: 0 4px 15px rgba(107, 68, 35, 0.3);
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 68, 35, 0.4);
        }
        .btn-register:active {
            transform: translateY(0);
        }
        .register-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #E0D5C7;
        }
        .register-footer p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }
        .register-footer a {
            color: #8B6F47;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }
        .register-footer a:hover {
            color: #6B4423;
            text-decoration: underline;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.6rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            font-weight: 500;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>🍞 Brew Bakery</h1>
                <p>Daftar Sebagai Customer</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor HP</label>
                    <input type="tel" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($no_hp); ?>" placeholder="082xxxxxxxxx">
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Password *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                </div>

                <button type="submit" class="btn-register">Daftar Sekarang</button>
            </form>

            <div class="register-footer">
                <p>Sudah punya akun? <a href="<?php echo AUTH_URL; ?>login-customer.php">Login di sini</a></p>
                <p><a href="<?php echo BASE_URL; ?>">Kembali ke Beranda</a></p>
            </div>
        </div>
    </div>
</body>
</html>
