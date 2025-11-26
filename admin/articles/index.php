<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where = "1=1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND a.judul LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Get total articles
$count_query = "SELECT COUNT(*) as total FROM articles a WHERE $where";
if ($params) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total / $per_page);

// Get articles - PERBAIKAN DI SINI (ubah users → admins)
$article_query = "SELECT a.*, ad.nama FROM articles a 
                  JOIN admins ad ON a.created_by = ad.id 
                  WHERE $where
                  ORDER BY a.created_at DESC 
                  LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($article_query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_success = isset($_GET['success']) ? sanitize($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Artikel - Brew Bakery Admin</title>
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
            display: flex;
            gap: 1rem;
        }
        .search-form input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }
        .search-form button {
            padding: 0.75rem 1.5rem;
            background-color: #8B6F47;
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
        }
        .search-form button:hover {
            background-color: #6B4423;
        }
        .btn-add {
            background-color: #8B6F47;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 0.3rem;
            font-weight: 600;
        }
        .btn-add:hover {
            background-color: #6B4423;
        }
        .articles-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .articles-table th {
            background-color: #8B6F47;
            color: #F5E6D3;
            padding: 1rem;
            text-align: left;
        }
        .articles-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .articles-table tbody tr:hover {
            background-color: #F5F1ED;
        }
        .btn-edit {
            background-color: #D4A574;
            color: #6B4423;
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .btn-edit:hover {
            background-color: #8B6F47;
            color: white;
        }
        .btn-delete {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
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
            color: #8B6F47;
        }
        .pagination a:hover {
            background-color: #8B6F47;
            color: white;
        }
        .pagination .active {
            background-color: #8B6F47;
            color: white;
            border-color: #8B6F47;
        }
    </style>
</head>
<body>
    
    
    <div style="display: flex; min-height: 100vh;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>📰 Manajemen Artikel</h1>
                <a href="<?php echo ADMIN_URL; ?>articles/add-simple.php" class="btn-add">➕ Tambah Artikel</a>
            </div>

            <?php if ($page_success): ?>
                <div class="alert alert-success"><?php echo $page_success; ?></div>
            <?php endif; ?>

            <div class="search-section">
                <form method="GET" action="" class="search-form">
                    <input type="text" name="search" placeholder="Cari artikel..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">🔍 Cari</button>
                </form>
            </div>

            <?php if (count($articles) > 0): ?>
            <table class="articles-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($article['judul']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars(substr(strip_tags($article['isi']), 0, 60)); ?>...</small>
                        </td>
                        <td><?php echo htmlspecialchars($article['nama']); ?></td>
                        <td><?php echo formatDate($article['created_at']); ?></td>
                        <td style="display: flex; gap: 0.5rem;">
                            <a href="<?php echo ADMIN_URL; ?>articles/edit.php?id=<?php echo $article['id']; ?>" class="btn-edit">Edit</a>
                            <a href="<?php echo ADMIN_URL; ?>articles/delete.php?id=<?php echo $article['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus artikel ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">« Pertama</a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">‹ Sebelumnya</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Selanjutnya ›</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Terakhir »</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state">
                <p style="font-size: 3rem; margin-bottom: 1rem;">📝</p>
                <p><strong>Belum ada artikel</strong></p>
                <p style="color: #666; margin-bottom: 1.5rem;">Mulai dengan menulis artikel baru</p>
                <a href="<?php echo ADMIN_URL; ?>articles/add-simple.php" class="btn-add">➕ Buat Artikel Pertama</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
</body>
</html>