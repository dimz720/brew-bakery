<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkCustomerAuth();

$customer_id = $_SESSION['customer_id'];
$customer = getCustomerById($customer_id);

// Get featured/popular products
$query_featured = "SELECT p.*, c.nama as category_name, COUNT(r.id) as review_count, AVG(r.rating) as avg_rating
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   LEFT JOIN reviews r ON p.id = r.product_id
                   WHERE p.stok > 0 
                   GROUP BY p.id
                   ORDER BY p.created_at DESC LIMIT 8";
$featured_products = $conn->query($query_featured)->fetch_all(MYSQLI_ASSOC);

// Get recent orders
$query = "SELECT o.*, COUNT(oi.id) as item_count FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id
          WHERE o.customer_id = ?
          GROUP BY o.id
          ORDER BY o.created_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get cart items count
$cart_items = getCartItems($customer_id);
$cart_count = count($cart_items);
$cart_total = getCartTotal($customer_id);

// ← PERBAIKAN: Bright & Warm Bakery Palette - Fresh & Yummy
$banners = [
    [
        'title' => '🎉 Selamat Datang!',
        'subtitle' => 'Roti & Pastry Premium Fresh Setiap Hari',
        'description' => 'Dibuat dengan bahan berkualitas tinggi dan cinta dari tim baker profesional',
        'cta' => 'Jelajahi Sekarang',
        'color' => 'linear-gradient(135deg, #FFF9F0 0%, #F8EDD8 100%)',
        'icon' => '🍞'
    ],
    [
        'title' => '✨ Promo Spesial',
        'subtitle' => 'Diskon Hingga 20% untuk Semua Produk',
        'description' => 'Gratis ongkir untuk pembelian pertama Anda!',
        'cta' => 'Belanja Sekarang',
        'color' => 'linear-gradient(135deg, #F4E4C1 0%, #E8D4B8 100%)',
        'icon' => '🎁'
    ],
    [
        'title' => '⭐ Produk Terlaris',
        'subtitle' => 'Pilihan Favorit 500+ Pelanggan Setia',
        'description' => 'Rasakan kelezatan roti tradisional dengan sentuhan modern',
        'cta' => 'Lihat Sekarang',
        'color' => 'linear-gradient(135deg, #FFF9F0 0%, #F4E4C1 100%)',
        'icon' => '👑'
    ],
    [
        'title' => '🔥 Inovasi Terbaru!',
        'subtitle' => 'Koleksi Roti Edisi Spesial Minggu Ini',
        'description' => 'Resep eksklusif yang dibuat khusus untuk Anda',
        'cta' => 'Jadilah Pertama',
        'color' => 'linear-gradient(135deg, #F8EDD8 0%, #FFF9F0 100%)',
        'icon' => '🚀'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* ← PERBAIKAN: Bright & Warm Bakery Palette */
            --primary: #F4E4C1;
            --secondary: #E8D4B8;
            --accent: #FFF9F0;
            --gold: #D4A574;
            --honey: #C9915D;
            --dark: #2D2D2D;
            --light: #FFFBF7;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .customer-layout {
            min-height: calc(100vh - 70px);
            background: linear-gradient(to bottom, #FEFDFB 0%, #FFF9F5 100%);
        }

        /* ← PERBAIKAN: Banner Slider Full Width & Professional */
        .hero-slider-container {
            position: relative;
            margin-bottom: 3rem;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(62, 39, 35, 0.2);
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
            width: 100vw;
        }

        .hero-slider {
            position: relative;
            height: 520px;
            background: white;
        }

        .slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 5rem 8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
        }

        .slide.active {
            opacity: 1;
            z-index: 1;
        }

        .slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
        }

        .slide-content {
            flex: 1;
            color: white;
            z-index: 2;
            max-width: 750px;
            animation: slideInLeft 0.9s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-60px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .slide-content h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1.2rem;
            line-height: 1.15;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: -1.5px;
            color: var(--honey);
        }

        .slide-content p:first-of-type {
            font-size: 1.6rem;
            margin-bottom: 0.8rem;
            opacity: 0.95;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.08);
            font-weight: 600;
            letter-spacing: 0.3px;
            color: var(--dark);
        }

        .slide-content p:last-of-type {
            font-size: 1.15rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
            line-height: 1.7;
            letter-spacing: 0.2px;
            color: var(--dark);
        }

        .slide-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1.3rem 3rem;
            background: linear-gradient(135deg, var(--gold) 0%, var(--honey) 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.15rem;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 8px 25px rgba(212, 165, 116, 0.3);
            border: 3px solid transparent;
        }

        .slide-cta:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(212, 165, 116, 0.4);
        }

        .slide-icon {
            font-size: 320px;
            opacity: 0.25;
            position: absolute;
            right: -100px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 0;
            animation: float 8s ease-in-out infinite;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.05));
        }

        @keyframes float {
            0%, 100% { transform: translateY(-50%) translateX(0) rotate(0deg); }
            50% { transform: translateY(-50%) translateX(30px) rotate(5deg); }
        }

        .slider-controls {
            position: absolute;
            bottom: 3rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 1.2rem;
            z-index: 10;
        }

        .slider-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: rgba(212, 165, 116, 0.4);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 2.5px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .slider-dot:hover {
            background: rgba(255, 255, 255, 0.7);
        }

        .slider-dot.active {
            background: var(--gold);
            transform: scale(1.5);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            background: rgba(212, 165, 116, 0.3);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .slider-arrow:hover {
            background: rgba(212, 165, 116, 0.5);
            transform: translateY(-50%) scale(1.2);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }

        .slider-arrow.prev {
            left: 3rem;
        }

        .slider-arrow.next {
            right: 3rem;
        }

        /* Container adjustment untuk full width banner */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        @media (max-width: 1200px) {
            .hero-slider {
                height: 450px;
            }

            .slide {
                padding: 4rem 5rem;
            }

            .slide-content h1 {
                font-size: 3.2rem;
            }

            .slide-icon {
                font-size: 250px;
                right: -70px;
            }
        }

        @media (max-width: 992px) {
            .hero-slider {
                height: 400px;
            }

            .slide {
                padding: 3rem 4rem;
            }

            .slide-content h1 {
                font-size: 2.8rem;
            }

            .slide-content p:first-of-type {
                font-size: 1.3rem;
            }

            .slide-icon {
                font-size: 220px;
                right: -60px;
            }
        }

        @media (max-width: 768px) {
            .hero-slider {
                height: 380px;
            }

            .slide {
                padding: 2.5rem 2rem;
                justify-content: center;
                text-align: center;
            }

            .slide-content {
                max-width: 100%;
            }

            .slide-content h1 {
                font-size: 2.2rem;
                margin-bottom: 0.8rem;
            }

            .slide-content p:first-of-type {
                font-size: 1.1rem;
            }

            .slide-content p:last-of-type {
                font-size: 0.95rem;
                margin-bottom: 2rem;
            }

            .slide-cta {
                padding: 1rem 2rem;
                font-size: 1rem;
            }

            .slide-icon {
                font-size: 160px;
                right: -40px;
                opacity: 0.05;
            }

            .slider-arrow {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }

            .slider-arrow.prev {
                left: 1.5rem;
            }

            .slider-arrow.next {
                right: 1.5rem;
            }

            .slider-controls {
                bottom: 2rem;
                gap: 0.8rem;
            }

            .slider-dot {
                width: 12px;
                height: 12px;
                border-width: 2px;
            }
        }

        @media (max-width: 480px) {
            .hero-slider {
                height: 320px;
            }

            .slide {
                padding: 1.5rem 1rem;
            }

            .slide-content h1 {
                font-size: 1.8rem;
                margin-bottom: 0.5rem;
            }

            .slide-content p:first-of-type {
                font-size: 0.95rem;
                margin-bottom: 0.4rem;
            }

            .slide-content p:last-of-type {
                font-size: 0.8rem;
                margin-bottom: 1.5rem;
                line-height: 1.5;
            }

            .slide-cta {
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
                gap: 0.4rem;
            }

            .slide-cta i {
                font-size: 0.8rem;
            }

            .slider-arrow {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .slider-arrow.prev {
                left: 0.75rem;
            }

            .slider-arrow.next {
                right: 0.75rem;
            }

            .slider-controls {
                bottom: 1.5rem;
                gap: 0.6rem;
            }

            .slider-dot {
                width: 10px;
                height: 10px;
            }
        }

        /* Cart Info Bar */
        .cart-info-bar {
            max-width: 1400px;
            margin: -2rem auto 3rem;
            padding: 0 2rem;
            position: relative;
            z-index: 10;
        }

        .cart-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(139, 69, 19, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            border-left: 5px solid var(--primary);
        }

        .cart-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
        }

        .cart-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent) 0%, #FCEAD9 100%);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .cart-details h3 {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .cart-details .amount {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
        }

        .cart-details .items {
            font-size: 0.9rem;
            color: #999;
        }

        .cart-action a {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-decoration: none;
            border-radius: 3rem;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(139, 69, 19, 0.3);
        }

        .cart-action a:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(139, 69, 19, 0.4);
        }

        /* Products Section */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #666;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(139, 69, 19, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(139, 69, 19, 0.2);
            border-color: var(--accent);
        }

        .product-image {
            position: relative;
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, var(--accent) 0%, #FCEAD9 100%);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .product-rating {
            color: #ffc107;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.2);
        }

        .add-to-cart-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 18px rgba(139, 69, 19, 0.3);
        }

        /* Recent Orders */
        .orders-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(139, 69, 19, 0.1);
            border-left: 5px solid var(--primary);
        }

        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--accent);
        }

        .orders-header h3 {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 700;
        }

        .orders-header a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-item {
            padding: 1.5rem;
            background: linear-gradient(to right, rgba(139, 111, 71, 0.05), transparent);
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid var(--primary);
        }

        .order-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.1);
        }

        .order-info .no {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .order-info .date {
            font-size: 0.9rem;
            color: #666;
        }

        .order-status {
            padding: 0.5rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        @media (max-width: 968px) {
            .hero-slider {
                height: 350px;
            }

            .slide-content h1 {
                font-size: 2.5rem;
            }

            .slide-content p {
                font-size: 1.1rem;
            }

            .cart-card {
                flex-direction: column;
                text-align: center;
            }

            .cart-info {
                flex-direction: column;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .container, .cart-info-bar {
                padding: 0 1rem;
            }

            .hero-slider {
                height: 280px;
            }

            .slide-content h1 {
                font-size: 1.8rem;
            }

            .slide-icon {
                font-size: 150px;
                right: -30px;
            }

            .slider-arrow {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .slider-arrow.prev {
                left: 1rem;
            }

            .slider-arrow.next {
                right: 1rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="customer-layout">
        <!-- ← PERBAIKAN: Banner Slider Professional -->
        <div class="hero-slider-container">
            <div class="hero-slider">
                <?php foreach ($banners as $index => $banner): ?>
                <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background: <?php echo $banner['color']; ?>">
                    <div class="slide-icon"><?php echo $banner['icon']; ?></div>
                    <div class="slide-content">
                        <h1><?php echo $banner['title']; ?></h1>
                        <p><?php echo $banner['subtitle']; ?></p>
                        <p><?php echo $banner['description']; ?></p>
                        <a href="<?php echo CUSTOMER_URL; ?>shop.php" class="slide-cta">
                            <?php echo $banner['cta']; ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

                <button class="slider-arrow prev" onclick="prevSlide()" aria-label="Previous Slide">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-arrow next" onclick="nextSlide()" aria-label="Next Slide">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <div class="slider-controls">
                    <?php foreach ($banners as $index => $banner): ?>
                    <div class="slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $index; ?>)" aria-label="Go to slide <?php echo $index + 1; ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cart Info Bar -->
        <?php if ($cart_count > 0): ?>
        <div class="cart-info-bar">
            <div class="cart-card">
                <div class="cart-info">
                    <div class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="cart-details">
                        <h3>Keranjang Belanja Anda</h3>
                        <div class="amount"><?php echo formatCurrency($cart_total); ?></div>
                        <div class="items"><?php echo $cart_count; ?> item di keranjang</div>
                    </div>
                </div>
                <div class="cart-action">
                    <a href="<?php echo CUSTOMER_URL; ?>cart.php">
                        <i class="fas fa-arrow-right"></i>
                        Checkout Sekarang
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Featured Products -->
        <div class="container">
            <div class="section-header">
                <h2>🍞 Produk Pilihan Hari Ini</h2>
                <p>Koleksi terbaik yang wajib Anda coba</p>
            </div>

            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['foto_utama']): ?>
                            <img src="<?php echo PRODUCT_IMG_URL . htmlspecialchars($product['foto_utama']); ?>" alt="<?php echo htmlspecialchars($product['nama']); ?>">
                        <?php else: ?>
                            <span style="font-size: 5rem;">🍞</span>
                        <?php endif; ?>
                        <?php if ($product['review_count'] > 0): ?>
                        <div class="product-badge">⭐ Best Seller</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['nama']); ?></div>
                        <?php if ($product['review_count'] > 0): ?>
                        <div class="product-rating">⭐ <?php echo round($product['avg_rating'], 1); ?> (<?php echo $product['review_count']; ?>)</div>
                        <?php endif; ?>
                        <div class="product-footer">
                            <div class="product-price"><?php echo formatCurrency($product['harga']); ?></div>
                            <button class="add-to-cart-btn" onclick="window.location.href='<?php echo CUSTOMER_URL; ?>product-detail.php?id=<?php echo $product['id']; ?>'">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Orders -->
            <?php if (count($recent_orders) > 0): ?>
            <div class="orders-section">
                <div class="orders-header">
                    <h3>📦 Pesanan Terbaru</h3>
                    <a href="<?php echo CUSTOMER_URL; ?>orders/">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php foreach ($recent_orders as $order): ?>
                <div class="order-item">
                    <div class="order-info">
                        <div class="no"><?php echo htmlspecialchars($order['no_pesanan']); ?></div>
                        <div class="date"><?php echo formatDate($order['created_at']); ?> • <?php echo formatCurrency($order['total_bayar']); ?></div>
                    </div>
                    <div class="order-status">
                        <?php echo ORDER_STATUS[$order['status']] ?? $order['status']; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ← PERBAIKAN: JavaScript untuk slider dengan timing optimal -->
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;
        let autoSlideTimer;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                dots[i].classList.remove('active');
            });
            
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
            resetAutoSlide();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
            resetAutoSlide();
        }

        function goToSlide(index) {
            currentSlide = index;
            showSlide(currentSlide);
            resetAutoSlide();
        }

        function startAutoSlide() {
            autoSlideTimer = setInterval(nextSlide, 6000);
        }

        function resetAutoSlide() {
            clearInterval(autoSlideTimer);
            startAutoSlide();
        }

        // Start auto-slide on load
        startAutoSlide();

        // Pause auto-slide on hover
        document.querySelector('.hero-slider').addEventListener('mouseenter', () => {
            clearInterval(autoSlideTimer);
        });

        document.querySelector('.hero-slider').addEventListener('mouseleave', startAutoSlide);

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') prevSlide();
            if (e.key === 'ArrowRight') nextSlide();
        });
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>