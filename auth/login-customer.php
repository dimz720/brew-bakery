<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (isset($_SESSION['customer_id'])) {
    redirect(CUSTOMER_URL . 'dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $query = "SELECT * FROM customers WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();

        if ($customer && verifyPassword($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_nama'] = $customer['nama'];
            redirect(CUSTOMER_URL . 'dashboard.php');
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Customer - Brew Bakery</title>
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
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
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
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .login-header h1 {
            color: #6B4423;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .login-header p {
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
        .btn-login {
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
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 68, 35, 0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #E0D5C7;
        }
        .login-footer p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }
        .login-footer a {
            color: #8B6F47;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }
        .login-footer a:hover {
            color: #6B4423;
            text-decoration: underline;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 0.6rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dc3545;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>🍞 Brew Bakery</h1>
                <p>Masuk Sebagai Customer</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-login">Masuk Sekarang</button>
            </form>

            <div class="login-footer">
                <p>Belum punya akun? <a href="<?php echo AUTH_URL; ?>register-customer.php">Daftar di sini</a></p>
                <p><a href="<?php echo BASE_URL; ?>">Kembali ke Beranda</a></p>
                <p><a href="<?php echo AUTH_URL; ?>login-admin.php">Masuk sebagai Admin</a></p>
            </div>
        </div>
    </div>
</body>
</html>
