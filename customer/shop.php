<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkCustomerAuth();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

// Build query
$where = "WHERE p.stok > 0";
$params = [];
$types = "";

if ($search) {
    $where .= " AND p.nama LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($category_id > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

// Get total products
$count_query = "SELECT COUNT(*) as total FROM products p $where";
if ($params) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total / $per_page);

// Get products
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$product_query = "SELECT p.*, c.nama as category_name, COUNT(r.id) as review_count, AVG(r.rating) as avg_rating
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN reviews r ON p.id = r.product_id
                  $where 
                  GROUP BY p.id
                  ORDER BY p.created_at DESC 
                  LIMIT ? OFFSET ?";

$stmt = $conn->prepare($product_query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .shop-layout {
            min-height: calc(100vh - 70px);
            background-color: var(--light);
            padding: 2rem 0;
        }
        .shop-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .shop-header {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .shop-header h1 {
            margin-bottom: 1rem;
        }
        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .search-bar input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
        }
        .search-bar button {
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
        }
        .filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .filter-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }
        .shop-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .product-card {
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .product-image {
            width: 100%;
            height: 250px;
            background-color: var(--accent);
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
            transition: transform 0.3s;
        }
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        .product-image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 250px;
            background-color: #f0f0f0;
            color: #999;
            flex-shrink: 0;
        }
        .product-info {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .product-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            min-height: 2.4em;
            line-height: 1.2;
            font-size: 1.05rem;
        }
        .product-category {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        .product-rating {
            font-size: 0.9rem;
            color: #ffc107;
            margin-bottom: 0.75rem;
        }
        .product-price {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }
        .product-stock {
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        .stock-available {
            color: #28a745;
            font-weight: 600;
        }
        .stock-low {
            color: #ffc107;
            font-weight: 600;
        }
        .stock-out {
            color: #dc3545;
            font-weight: 600;
        }
        .product-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: auto;
        }
        .product-actions a,
        .product-actions button {
            flex: 1;
            padding: 0.6rem 0.5rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: opacity 0.3s;
            font-size: 0.9rem;
        }
        .btn-detail {
            background-color: var(--secondary);
            color: white;
        }
        .btn-detail:hover {
            opacity: 0.9;
        }
        .btn-cart {
            background-color: var(--primary);
            color: white;
        }
        .btn-cart:hover {
            opacity: 0.9;
        }
        .btn-cart:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            text-decoration: none;
            color: var(--primary);
        }
        .pagination a:hover {
            background-color: var(--primary);
            color: white;
        }
        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="shop-layout">
        <div class="shop-container">
            <div class="shop-header">
                <h1>🛒 Belanja Produk Kami</h1>
                
                <form method="GET" action="" class="search-bar">
                    <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Cari</button>
                </form>

                <div class="filters">
                    <div class="filter-group">
                        <label for="category">Kategori</label>
                        <select name="category" id="category" onchange="window.location.href='<?php echo CUSTOMER_URL; ?>shop.php?category=' + this.value + '<?php echo $search ? '&search=' . urlencode($search) : ''; ?>'">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id === $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nama']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <?php if (count($products) > 0): ?>
                <div class="shop-content">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['foto_utama'])): ?>
                                <img src="<?php echo PRODUCT_IMG_URL . htmlspecialchars($product['foto_utama']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['nama']); ?>" 
                                     loading="lazy"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-image-placeholder" style="display: none;">
                                    <div>
                                        <p style="margin: 0; font-size: 3rem;">🍞</p>
                                        <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem;">Foto tidak tersedia</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="product-image-placeholder">
                                    <div>
                                        <p style="margin: 0; font-size: 3rem;">🍞</p>
                                        <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem;">Foto tidak tersedia</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['nama']); ?></div>
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            
                            <?php if ($product['review_count'] > 0): ?>
                            <div class="product-rating">
                                ⭐ <?php echo round($product['avg_rating'], 1); ?> (<?php echo $product['review_count']; ?> ulasan)
                            </div>
                            <?php endif; ?>
                            
                            <!-- MODIFIKASI: Harga dengan diskon -->
                            <div class="product-price">
                                <?php 
                                $priceInfo = formatPriceWithDiscount($product['harga'], $product['diskon_tipe'], $product['diskon_nilai'], $product['diskon_aktif']);
                                if ($priceInfo['savings'] > 0): 
                                ?>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <span style="text-decoration: line-through; color: #999; font-size: 0.9rem;">
                                            <?php echo formatCurrency($priceInfo['original']); ?>
                                        </span>
                                        <span style="color: #28a745; font-weight: 900;">
                                            <?php echo formatCurrency($priceInfo['discounted']); ?>
                                        </span>
                                        <span style="background: #dc3545; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; font-weight: 700;">
                                            <?php echo $priceInfo['discount_display']; ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <?php echo formatCurrency($priceInfo['original']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="product-stock">
                                <?php if ($product['stok'] > 10): ?>
                                    <span class="stock-available">✓ Stok tersedia (<?php echo $product['stok']; ?>)</span>
                                <?php elseif ($product['stok'] > 0): ?>
                                    <span class="stock-low">⚠ Stok terbatas (<?php echo $product['stok']; ?>)</span>
                                <?php else: ?>
                                    <span class="stock-out">✗ Stok habis</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="<?php echo CUSTOMER_URL; ?>product-detail.php?id=<?php echo $product['id']; ?>" class="btn-detail">Detail</a>
                                <button class="btn-cart" <?php echo $product['stok'] <= 0 ? 'disabled' : ''; ?> onclick="addToCart(<?php echo $product['id']; ?>)">Keranjang</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>">« Pertama</a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>">‹ Sebelumnya</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>">Selanjutnya ›</a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>">Terakhir »</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 3rem; margin-bottom: 1rem;">🔍</p>
                    <p>Tidak ada produk yang ditemukan.</p>
                    <?php if ($search || $category_id): ?>
                        <p><a href="<?php echo CUSTOMER_URL; ?>shop.php">Lihat semua produk</a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function addToCart(productId) {
            fetch('<?php echo API_URL; ?>add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    jumlah: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Produk berhasil ditambahkan ke keranjang!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>