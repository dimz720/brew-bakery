<?php
require_once __DIR__ . '/../../includes/auth-check.php';

checkCustomerAuth();

$customer = getCurrentCustomer();
$customer_id = $_SESSION['customer_id'];
$unread_notif = getUnreadNotifications($customer_id);
$cart_items = getCartItems($customer_id);
$cart_count = count($cart_items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brew Bakery</title>
    <style>
        :root {
            /* ← PERBAIKAN: Bright & Warm Bakery Colors */
            --primary: #F4E4C1;              /* Vanilla Cream */
            --primary-light: #E8D4B8;        /* Lighter Vanilla - untuk navbar */
            --secondary: #E8D4B8;            /* Soft Butter */
            --accent: #FFF9F0;               /* Milk White */
            --gold: #D4A574;                 /* Caramel Gold */
            --honey: #C9915D;                /* Honey Brown */
            --text-dark: #2D2D2D;            /* Dark Text */
            --text-light: #FFFFFF;           /* White Text */
            --bg-light: #FFFBF7;             /* Light Cream */
            --border: #E6CEB3;               /* Light Border */
        }

        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, #D4A574 0%, #C9915D 100%);
            color: var(--text-dark);
            padding: 1rem 0;
            box-shadow: 0 4px 15px rgba(201, 145, 93, 0.25);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #B8860B;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Brand */
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.15);
        }

        /* Nav list */
        .navbar-nav {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            list-style: none;
        }

        /* Link */
        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.75rem 1.2rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Badge */
        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #FFC107;
            color: #2D2D2D;
            padding: 0.2rem 0.5rem;
            border-radius: 50%;
            font-size: 0.65rem;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
        }

        /* Profile Dropdown */
        .nav-item {
            position: relative;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            padding: 0.75rem 1.2rem;
        }

        .dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 220px;
            border-radius: 0.75rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            padding: 0.75rem 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1001;
            margin-top: 0.5rem;
            border: 1px solid #E6CEB3;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1.5rem;
            color: #2D2D2D;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #FFF9F0;
            color: #C9915D;
            padding-left: 2rem;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #E6CEB3;
            margin: 0.5rem 0;
        }

        /* Hamburger */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
        }

        .hamburger span {
            width: 25px;
            height: 2.5px;
            background-color: white;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .navbar-container {
                padding: 0 1rem;
            }

            .navbar-nav {
                position: fixed;
                top: 60px;
                left: 0;
                width: 100%;
                background: linear-gradient(135deg, #D4A574 0%, #C9915D 100%);
                flex-direction: column;
                padding: 1rem 0;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                z-index: 999;
            }

            .navbar-nav.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .nav-item {
                width: 100%;
            }

            .nav-link {
                padding: 1rem;
                justify-content: center;
                color: white;
            }

            .dropdown-menu {
                position: static;
                background: rgba(255, 255, 255, 0.15);
                box-shadow: none;
                opacity: 1;
                visibility: visible;
                transform: none;
                display: none;
                margin-top: 0;
                border: none;
            }

            .dropdown.active .dropdown-menu {
                display: block;
            }

            .dropdown-item {
                justify-content: center;
                color: white;
            }

            .dropdown-item:hover {
                background-color: rgba(255, 255, 255, 0.2);
                padding-left: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?php echo CUSTOMER_URL; ?>dashboard.php" class="navbar-brand">
                <span>🍞</span>
                <span>Brew Bakery</span>
            </a>

            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <ul class="navbar-nav" id="navbarNav">
                <li class="nav-item">
                    <a href="<?php echo CUSTOMER_URL; ?>dashboard.php" class="nav-link">
                        <span>🏠</span>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo CUSTOMER_URL; ?>shop.php" class="nav-link">
                        <span>🍞</span>
                        <span>Belanja</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo CUSTOMER_URL; ?>articles.php" class="nav-link">
                        <span>📰</span>
                        <span>Artikel</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo CUSTOMER_URL; ?>cart.php" class="nav-link">
                        <span>🛒</span>
                        <span>Keranjang</span>
                        <?php if ($cart_count > 0): ?>
                        <span class="badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Dropdown Profil -->
                <li class="nav-item dropdown" id="profileDropdown">
                    <div class="dropdown-toggle">
                        <span>👤</span>
                        <span><?php echo htmlspecialchars(substr($customer['nama'], 0, 10)); ?></span>
                        <span>▼</span>
                    </div>
                    <div class="dropdown-menu">
                        <a href="<?php echo CUSTOMER_URL; ?>profile.php" class="dropdown-item">
                            <span>👤</span>
                            <span>Profil Saya</span>
                        </a>
                        <a href="<?php echo CUSTOMER_URL; ?>orders/" class="dropdown-item">
                            <span>📦</span>
                            <span>Riwayat Pesanan</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo AUTH_URL; ?>logout.php" class="dropdown-item">
                            <span>🚪</span>
                            <span>Keluar</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <script>
        // Toggle menu mobile
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('navbarNav').classList.toggle('active');
        });

        // Toggle dropdown di mobile
        document.getElementById('profileDropdown').addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                this.classList.toggle('active');
            }
        });

        // Tutup menu saat klik di luar
        document.addEventListener('click', function(e) {
            const navbarNav = document.getElementById('navbarNav');
            const hamburger = document.getElementById('hamburger');
            
            if (!navbarNav.contains(e.target) && !hamburger.contains(e.target)) {
                navbarNav.classList.remove('active');
            }
        });
    </script>
</body>
</html>