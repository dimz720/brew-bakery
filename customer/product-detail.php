<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkCustomerAuth();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    redirect(CUSTOMER_URL . 'shop.php');
}

$customer_id = $_SESSION['customer_id'];

// Get product details
$product = getProductById($product_id);

if (!$product) {
    redirect(CUSTOMER_URL . 'shop.php');
}

// Get product images
$images_query = "SELECT foto FROM product_photos WHERE product_id = ?";
$stmt = $conn->prepare($images_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get reviews (FIX: customers bukan users)
$reviews_query = "SELECT r.*, c.nama FROM reviews r 
                  JOIN customers c ON r.customer_id = c.id 
                  WHERE r.product_id = ? 
                  ORDER BY r.created_at DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get category
$category = $conn->query("SELECT nama FROM categories WHERE id = " . $product['category_id'])->fetch_assoc();

$error = '';
$success = '';
$quantity = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $action = isset($_POST['action']) ? sanitize($_POST['action']) : 'add_cart';
    
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    if ($quantity > $product['stok']) {
        $error = 'Jumlah melebihi stok yang tersedia!';
    } else {
        if ($action === 'buy_now') {
            // ← TAMBAHAN: Checkout langsung dengan temporary cart
            $_SESSION['temp_cart'] = [
                'product_id' => $product_id,
                'jumlah' => $quantity,
                'nama' => $product['nama'],
                'harga' => $product['harga'],
                'foto_utama' => $product['foto_utama']
            ];
            redirect(CUSTOMER_URL . 'checkout.php?direct=1');
        } else {
            // Add to cart normally
            $check_query = "SELECT id FROM carts WHERE customer_id = ? AND product_id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ii", $customer_id, $product_id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            
            if ($existing) {
                // Update quantity
                $update_query = "UPDATE carts SET jumlah = jumlah + ? WHERE customer_id = ? AND product_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("iii", $quantity, $customer_id, $product_id);
            } else {
                // Insert new cart item
                $insert_query = "INSERT INTO carts (customer_id, product_id, jumlah) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iii", $customer_id, $product_id, $quantity);
            }
            
            if ($stmt->execute()) {
                $success = 'Produk berhasil ditambahkan ke keranjang!';
                $quantity = 1;
            } else {
                $error = 'Gagal menambahkan ke keranjang!';
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
    <title><?php echo htmlspecialchars($product['nama']); ?> - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .detail-layout {
            min-height: calc(100vh - 70px);
            background-color: var(--light);
            padding: 2rem 0;
        }
        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .detail-content {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .product-gallery {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .main-image {
            width: 100%;
            height: 400px;
            background-color: var(--accent);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .thumbnail-gallery {
            display: flex;
            gap: 0.5rem;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            background-color: var(--accent);
            border-radius: 0.3rem;
            cursor: pointer;
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: border-color 0.3s;
        }
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--primary);
        }
        .product-details h1 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .product-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .product-rating {
            color: #ffc107;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        .product-price {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .product-stock {
            padding: 0.75rem;
            border-radius: 0.3rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .stock-available {
            background-color: #d4edda;
            color: #155724;
        }
        .stock-low {
            background-color: #fff3cd;
            color: #856404;
        }
        .stock-out {
            background-color: #f8d7da;
            color: #721c24;
        }
        .product-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .quantity-input {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }
        .quantity-input button {
            padding: 0.5rem 0.75rem;
            border: none;
            background-color: var(--light);
            cursor: pointer;
        }
        .quantity-input input {
            width: 50px;
            border: none;
            text-align: center;
            padding: 0.5rem;
        }
        .btn-cart-add {
            flex: 1;
            min-width: 150px;
            padding: 1rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-cart-add:hover {
            background-color: var(--dark);
        }
        .btn-cart-add:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .btn-buy-now {
            flex: 1;
            min-width: 150px;
            padding: 1rem;
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .btn-buy-now:hover {
            background-color: #ee5a52;
        }
        .btn-buy-now:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .description {
            border-top: 1px solid #eee;
            padding-top: 2rem;
            margin-top: 2rem;
        }
        .description h3 {
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .reviews-section {
            border-top: 1px solid #eee;
            padding-top: 2rem;
            margin-top: 2rem;
        }
        .reviews-section h3 {
            color: var(--dark);
            margin-bottom: 1.5rem;
        }
        .review-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            background-color: var(--light);
            border-radius: 0.3rem;
            margin-bottom: 1rem;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .review-author {
            font-weight: 600;
        }
        .review-date {
            font-size: 0.85rem;
            color: #666;
        }
        .review-rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        .review-text {
            color: #333;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .product-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="detail-layout">
        <div class="detail-container">
            <a href="<?php echo CUSTOMER_URL; ?>shop.php" style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Kembali ke Belanja</a>
            
            <div class="detail-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="detail-grid">
                    <div class="product-gallery">
                        <div class="main-image">
                            <?php if (!empty($product['foto_utama'])): ?>
                                <img id="mainImage" src="<?php echo PRODUCT_IMG_URL . htmlspecialchars($product['foto_utama']); ?>" alt="<?php echo htmlspecialchars($product['nama']); ?>">
                            <?php else: ?>
                                <span style="font-size: 3rem;">🍞</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($images) > 0 || !empty($product['foto_utama'])): ?>
                        <div class="thumbnail-gallery">
                            <?php if (!empty($product['foto_utama'])): ?>
                            <div class="thumbnail active" onclick="changeImage(this)">
                                <img src="<?php echo PRODUCT_IMG_URL . htmlspecialchars($product['foto_utama']); ?>" alt="Thumbnail">
                            </div>
                            <?php endif; ?>
                            
                            <?php foreach ($images as $img): ?>
                            <div class="thumbnail" onclick="changeImage(this)">
                                <img src="<?php echo PRODUCT_IMG_URL . htmlspecialchars($img['foto']); ?>" alt="Thumbnail">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-details">
                        <h1><?php echo htmlspecialchars($product['nama']); ?></h1>
                        <div class="product-category"><?php echo htmlspecialchars($category['nama']); ?></div>
                        
                      
                        
                        <!-- MODIFIKASI: Harga dengan diskon -->
                        <div class="product-price">
                            <?php 
                            $priceInfo = formatPriceWithDiscount($product['harga'], $product['diskon_tipe'], $product['diskon_nilai'], $product['diskon_aktif']);
                            if ($priceInfo['savings'] > 0): 
                            ?>
                                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                    <div>
                                        <div style="text-decoration: line-through; color: #999; font-size: 1rem; margin-bottom: 0.25rem;">
                                            <?php echo formatCurrency($priceInfo['original']); ?>
                                        </div>
                                        <div style="color: #28a745; font-weight: 900; font-size: 2rem;">
                                            <?php echo formatCurrency($priceInfo['discounted']); ?>
                                        </div>
                                    </div>
                                    <div style="background: #dc3545; color: white; padding: 0.75rem 1rem; border-radius: 0.5rem; font-weight: 700; text-align: center;">
                                        <div style="font-size: 1.5rem;"><?php echo $priceInfo['discount_display']; ?></div>
                                        <div style="font-size: 0.85rem; opacity: 0.9;">Hemat <?php echo formatCurrency($priceInfo['savings']); ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php echo formatCurrency($priceInfo['original']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-stock <?php echo $product['stok'] > 10 ? 'stock-available' : ($product['stok'] > 0 ? 'stock-low' : 'stock-out'); ?>">
                            <?php if ($product['stok'] > 10): ?>
                                ✓ Stok tersedia (<?php echo $product['stok']; ?>)
                            <?php elseif ($product['stok'] > 0): ?>
                                ⚠ Stok terbatas (<?php echo $product['stok']; ?>)
                            <?php else: ?>
                                ✗ Stok habis
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="product-form">
                                <div class="quantity-input">
                                    <button type="button" onclick="decreaseQty()">−</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stok']; ?>">
                                    <button type="button" onclick="increaseQty()">+</button>
                                </div>
                                <button type="submit" name="action" value="add_cart" class="btn-cart-add" <?php echo $product['stok'] <= 0 ? 'disabled' : ''; ?>>
                                    🛒 Tambah ke Keranjang
                                </button>
                                <button type="submit" name="action" value="buy_now" class="btn-buy-now" <?php echo $product['stok'] <= 0 ? 'disabled' : ''; ?>>
                                    ⚡ Beli Langsung
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="description">
                    <h3>Deskripsi Produk</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['deskripsi'])); ?></p>
                </div>
                
                <?php if (count($reviews) > 0): ?>
                <div class="reviews-section">
                    <h3>📝 Ulasan Pelanggan</h3>
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-author"><?php echo htmlspecialchars($review['nama']); ?></span>
                            <span class="review-date"><?php echo formatDate($review['created_at']); ?></span>
                        </div>
                        <div class="review-rating">⭐ <?php echo $review['rating']; ?>/5</div>
                        <div class="review-text"><?php echo htmlspecialchars($review['ulasan']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function changeImage(element) {
            const src = element.querySelector('img').src;
            document.getElementById('mainImage').src = src;
            
            document.querySelectorAll('.thumbnail').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }
        
        function increaseQty() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        }
        
        function decreaseQty() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
    </script>



</body>    <?php include __DIR__ . '/../includes/footer.php'; ?></html>
