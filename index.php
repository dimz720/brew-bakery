<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

// Get featured products
$featured_query = "SELECT p.*, c.nama as category_name, COUNT(r.id) as review_count, AVG(r.rating) as avg_rating
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   LEFT JOIN reviews r ON p.id = r.product_id
                   WHERE p.stok > 0
                   GROUP BY p.id
                   ORDER BY p.created_at DESC 
                   LIMIT 6";
$featured_products = $conn->query($featured_query)->fetch_all(MYSQLI_ASSOC);

// Get latest articles
$articles_query = "SELECT a.*, ad.nama FROM articles a 
                   JOIN admins ad ON a.created_by = ad.id 
                   ORDER BY a.created_at DESC 
                   LIMIT 3";
$articles = $conn->query($articles_query)->fetch_all(MYSQLI_ASSOC);

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY nama LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brew Bakery - Roti & Pastry Premium</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Hero Section with Background */
        .hero-section {
            position: relative;
            background: linear-gradient(135deg, rgba(131, 70, 27, 0.95) 0%, rgba(101, 67, 33, 0.95) 100%),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%23f5deb3" width="1200" height="600"/><circle cx="200" cy="150" r="80" fill="%23daa520" opacity="0.3"/><circle cx="800" cy="400" r="100" fill="%23cd853f" opacity="0.2"/><path d="M 0 300 Q 300 250 600 300 T 1200 300 L 1200 600 L 0 600 Z" fill="%23f4a460" opacity="0.2"/></svg>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 8rem 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="50" font-size="60" opacity="0.1">🍞</text></svg>');
            background-size: 150px;
            animation: float 20s linear infinite;
            opacity: 0.3;
        }

        @keyframes float {
            from { background-position: 0 0; }
            to { background-position: 100px 100px; }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .hero-section h1 {
            color: white;
            font-size: 4rem;
            margin-bottom: 1rem;
            font-weight: 800;
            letter-spacing: -1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-section p {
            font-size: 1.4rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-btn {
            padding: 1.2rem 2.5rem;
            background-color: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            font-size: 1rem;
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .hero-btn.secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .hero-btn.secondary:hover {
            background-color: white;
            color: var(--primary);
        }

        /* Stats Section */
        .stats-section {
            background: white;
            padding: 3rem 1rem;
            margin-top: -3rem;
            position: relative;
            z-index: 2;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 3rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-item {
            text-align: center;
            color: white;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* About Section */
        .about-section {
            padding: 5rem 1rem;
            background: linear-gradient(to bottom, #ffffff 0%, #fef9f3 100%);
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-image {
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .about-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.3) 0%, rgba(101, 67, 33, 0.3) 100%);
            z-index: 1;
        }

        .about-image-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #f5deb3 0%, #daa520 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8rem;
            position: relative;
        }

        .about-content h2 {
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-weight: 800;
        }

        .about-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 1.5rem;
        }

        .about-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .feature-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }

        .feature-text h4 {
            font-size: 1.1rem;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }

        .feature-text p {
            font-size: 0.95rem;
            color: #666;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
            padding-top: 4rem;
        }

        .section-title h2 {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .section-title p {
            color: #666;
            font-size: 1.2rem;
        }

        .products-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .product-item {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, #f5deb3 0%, #daa520 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-item:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #8B6F47;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .product-data {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            color: #8B6F47;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .product-name {
            font-weight: 700;
            color: #2D2D2D;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            line-height: 1.4;
            min-height: 2.2em;
        }

        .product-price {
            font-size: 1.6rem;
            color: #8B6F47;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .product-rating {
            color: #ffc107;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .btn-product {
            display: block;
            width: 100%;
            padding: 0.8rem 1rem;
            background-color: #8B6F47;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            margin-top: auto;
        }

        .btn-product:hover {
            background-color: #6B4423;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 111, 71, 0.3);
        }

        .categories-section {
            background: white;
            padding: 5rem 1rem;
            margin-bottom: 4rem;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .category-card {
            text-align: center;
            padding: 2.5rem 2rem;
            background: linear-gradient(135deg, #fff 0%, #fef9f3 100%);
            border-radius: 1rem;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .category-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .category-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .category-name {
            font-weight: 700;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .articles-section {
            margin-bottom: 4rem;
            padding: 4rem 0;
            background: linear-gradient(to bottom, #fef9f3 0%, #ffffff 100%);
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .article-item {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .article-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .article-image {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #f5deb3 0%, #b8953dff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .article-item:hover .article-image img {
            transform: scale(1.1);
        }

        .article-data {
            padding: 2rem;
        }

        .article-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            min-height: 3em;
            line-height: 1.4;
        }

        .article-excerpt {
            color: #666;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.6;
        }

        .article-meta {
            font-size: 0.9rem;
            color: #999;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-article {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-article:hover {
            gap: 1rem;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
            border-radius: 1rem;
            margin: 4rem auto;
            max-width: 1200px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .cta-section h2 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 800;
        }

        .cta-section p {
            margin-bottom: 2rem;
            opacity: 0.95;
            font-size: 1.2rem;
        }

        @media (max-width: 992px) {
            .about-container {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .about-features {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 5rem 1rem;
            }

            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section p {
                font-size: 1.1rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-btn {
                width: 100%;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                padding: 2rem 1rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .about-content h2 {
                font-size: 2rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .products-showcase,
            .articles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['customer_id'])): ?>
        <?php include __DIR__ . '/customer/includes/navbar.php'; ?>
 
    <?php else: ?>
    <nav style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; padding: 1rem 0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="color: white; margin: 0; font-weight: 800;">🍞 Brew Bakery</h2>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <a href="<?php echo AUTH_URL; ?>login-customer.php" style="color: white; text-decoration: none; font-weight: 600;">Login</a>
                <a href="<?php echo AUTH_URL; ?>register-customer.php" style="background-color: white; color: var(--primary); padding: 0.6rem 1.5rem; border-radius: 50px; text-decoration: none; font-weight: 700;">Daftar</a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">✨ SEJAK 2025</div>
            <h1>Brew Bakery</h1>
            <p>Roti dan Pastry Premium Dibuat Fresh Setiap Hari dengan Bahan Berkualitas Tinggi</p>
            <div class="hero-buttons">
                <a href="<?php echo isset($_SESSION['customer_id']) ? CUSTOMER_URL . 'shop.php' : AUTH_URL . 'register-customer.php'; ?>" class="hero-btn">Belanja Sekarang</a>
                <a href="#about" class="hero-btn secondary">Tentang Kami</a>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-number">500+</span>
                <span class="stat-label">Pelanggan Puas</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">50+</span>
                <span class="stat-label">Varian Produk</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">100%</span>
                <span class="stat-label">Bahan Fresh</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">8/7</span>
                <span class="stat-label">Layanan Order</span>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div id="about" class="about-section">
        <div class="about-container">
            <div class="about-image">
                <div class="about-image-placeholder">
                    🥖
                </div>
            </div>
            <div class="about-content">
                <h2>Tentang Brew Bakery</h2>
                <p>Brew Bakery adalah toko roti premium yang berkomitmen menghadirkan produk berkualitas tinggi dengan bahan-bahan pilihan terbaik. Sejak 2020, kami telah melayani ribuan pelanggan dengan produk roti dan pastry yang dibuat fresh setiap hari.</p>
                <p>Dengan tim baker profesional dan resep rahasia turun temurun, setiap produk kami dibuat dengan penuh cinta dan perhatian terhadap detail untuk menghadirkan rasa terbaik untuk Anda dan keluarga.</p>
                
                <div class="about-features">
                    <div class="feature-item">
                        <div class="feature-icon">🌾</div>
                        <div class="feature-text">
                            <h4>Bahan Premium</h4>
                            <p>100% bahan pilihan berkualitas tinggi</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">👨‍🍳</div>
                        <div class="feature-text">
                            <h4>Baker Profesional</h4>
                            <p>Tim berpengalaman 10+ tahun</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🔥</div>
                        <div class="feature-text">
                            <h4>Fresh Daily</h4>
                            <p>Dipanggang fresh setiap hari</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✅</div>
                        <div class="feature-text">
                            <h4>Higienis</h4>
                            <p>Standar kebersihan internasional</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="container">
        <div id="featured" class="section-title">
            <h2> Produk Unggulan</h2>
            <p>Koleksi roti dan pastry terbaik kami yang paling disukai pelanggan</p>
        </div>

        <?php if (count($featured_products) > 0): ?>
        <div class="products-showcase">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-item">
                <div class="product-image">
                    <?php if ($product['review_count'] > 0): ?>
                        <div class="product-badge">⭐ Best Seller</div>
                    <?php endif; ?>
                    <?php if ($product['foto_utama']): ?>
                   <img src="<?php echo PRODUCT_IMG_URL . $product['foto_utama']; ?>" 
                        alt="<?php echo htmlspecialchars($product['nama']); ?>">
                    <?php else: ?>
                        <span style="font-size: 5rem;">🍞</span>
                    <?php endif; ?>
                </div>
                <div class="product-data">
                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <div class="product-name"><?php echo htmlspecialchars($product['nama']); ?></div>
                    <?php if ($product['review_count'] > 0): ?>
                    <div class="product-rating">⭐ <?php echo round($product['avg_rating'], 1); ?> (<?php echo $product['review_count']; ?> ulasan)</div>
                    <?php endif; ?>
                    <div class="product-price"><?php echo formatCurrency($product['harga']); ?></div>
                    <a href="<?php echo isset($_SESSION['customer_id']) ? CUSTOMER_URL . 'product-detail.php?id=' . $product['id'] : AUTH_URL . 'login-customer.php'; ?>" class="btn-product">Lihat Detail</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

   
    <!-- Articles Section -->
    <div class="articles-section">
        <div class="container">
            <div class="section-title" style="padding-top: 0;">
                <h2>Artikel & Tips</h2>
                <p>Baca artikel menarik seputar roti, resep, dan tips baking</p>
            </div>

            <?php if (count($articles) > 0): ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                <div class="article-item">
                    <div class="article-image">
                        <?php if ($article['foto']): ?>
                            <img src="<?php echo ARTICLE_IMG_URL . $article['foto']; ?>" alt="<?php echo htmlspecialchars($article['judul']); ?>">
                        <?php else: ?>
                            <span style="font-size: 5rem;">📝</span>
                        <?php endif; ?>
                    </div>
                    <div class="article-data">
                        <h3 class="article-title"><?php echo htmlspecialchars($article['judul']); ?></h3>
                        <div class="article-excerpt"><?php echo htmlspecialchars(substr(strip_tags($article['isi']), 0, 120)); ?>...</div>
                        <div class="article-meta">
                            👤 <?php echo htmlspecialchars($article['nama']); ?>
                        </div>
                        <a href="<?php echo isset($_SESSION['customer_id']) ? CUSTOMER_URL . 'article-detail.php?id=' . $article['id'] : AUTH_URL . 'login-customer.php'; ?>" class="btn-article">Baca Selengkapnya →</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="container">
        <div class="cta-section">
            <h2>🎉 Siap Menikmati Roti Segar Kami?</h2>
            <p>Pesan sekarang dan rasakan kelezatan roti premium yang dibuat dengan penuh cinta. Gratis ongkir untuk pembelian pertama!</p>
            <a href="<?php echo isset($_SESSION['customer_id']) ? CUSTOMER_URL . 'shop.php' : AUTH_URL . 'register-customer.php'; ?>" class="hero-btn">Mulai Belanja Sekarang</a>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>