<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get categories untuk filter
$categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

// Build WHERE clause
$where = "1=1";
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
$count_query = "SELECT COUNT(*) as total FROM products p WHERE $where";
if ($params) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total / $per_page);

// Get products
$product_query = "SELECT p.*, c.nama as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE $where
                  ORDER BY p.created_at DESC 
                  LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($product_query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

// Handle delete - SAFE DELETION
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get product first
    $product = getProductById($id);
    if ($product) {
        // Check if product is referenced in order_items
        $check_query = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result()->fetch_assoc();
        
        if ($check_result['count'] > 0) {
            // Product masih digunakan dalam order, jangan hapus
            $error = "Tidak dapat menghapus produk! Produk masih digunakan dalam " . $check_result['count'] . " pesanan.";
        } else {
            // Safe to delete
            if ($product['foto_utama']) {
                deleteImage($product['foto_utama'], 'products/');
            }
            
            $photos = $conn->query("SELECT foto FROM product_photos WHERE product_id = $id")->fetch_all(MYSQLI_ASSOC);
            foreach ($photos as $photo) {
                deleteImage($photo['foto'], 'products/');
            }
            
            $conn->query("DELETE FROM product_photos WHERE product_id = $id");
            
            $delete_query = "DELETE FROM products WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $id);
            
            if ($delete_stmt->execute()) {
                header("Location: " . ADMIN_URL . "products/?success=Produk%20berhasil%20dihapus");
                exit();
            } else {
                $error = "Gagal menghapus produk!";
            }
        }
    }
}

$page_success = isset($_GET['success']) ? sanitize($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Brew Bakery Admin</title>
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
        .search-section {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .search-form {
            display: grid;
            grid-template-columns: 1fr 200px 150px;
            gap: 1rem;
        }
        .search-form input,
        .search-form select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }
        .search-form button {
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-add {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 0.3rem;
            font-weight: 600;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .products-table th {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        .products-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .products-table tbody tr:hover {
            background-color: var(--light);
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 0.3rem;
        }
        .product-image-placeholder {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            border-radius: 0.3rem;
        }
        .btn-edit,
        .btn-delete {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        .btn-edit {
            background-color: var(--secondary);
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
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
    
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>🍞 Manajemen Produk</h1>
                <a href="<?php echo ADMIN_URL; ?>products/add.php" class="btn-add">➕ Tambah Produk</a>
            </div>

            <?php if (!empty($page_success)): ?>
                <div class="alert alert-success"><?php echo $page_success; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="search-section">
                <form method="GET" action="" class="search-form">
                    <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category">
                        <option value="0">Semua Kategori</option>
                        <?php 
                        $categories = $conn->query("SELECT * FROM categories ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
                        foreach ($categories as $cat): 
                        ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_id === $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nama']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">🔍 Cari</button>
                </form>
            </div>

            <?php if (count($products) > 0): ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if (!empty($product['foto_utama'])): ?>
                                <img src="<?php echo PRODUCT_IMG_URL . htmlspecialchars($product['foto_utama']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['nama']); ?>" 
                                     class="product-image"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-image-placeholder" style="display: none;">
                                    <span style="font-size: 1.5rem;">🍞</span>
                                </div>
                            <?php else: ?>
                                <div class="product-image-placeholder">
                                    <span style="font-size: 1.5rem;">🍞</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($product['nama']); ?></strong></td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><?php echo formatCurrency($product['harga']); ?></td>
                        <td>
                            <span style="<?php echo $product['stok'] <= 10 ? 'color: #dc3545; font-weight: bold;' : ''; ?>">
                                <?php echo $product['stok']; ?> pcs
                            </span>
                        </td>
                        <td style="display: flex; gap: 0.5rem;">
                            <a href="<?php echo ADMIN_URL; ?>products/edit.php?id=<?php echo $product['id']; ?>" class="btn-edit">Edit</a>
                            <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

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
                <p style="font-size: 3rem; margin-bottom: 1rem;">📦</p>
                <p><strong>Belum ada produk</strong></p>
                <p style="color: #666; margin-bottom: 1.5rem;">Mulai dengan menambahkan produk baru</p>
                <a href="<?php echo ADMIN_URL; ?>products/add.php" class="btn-add">➕ Tambah Produk Pertama</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>