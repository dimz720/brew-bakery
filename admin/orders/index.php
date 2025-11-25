<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($status_filter) && array_key_exists($status_filter, ORDER_STATUS)) {
    $where .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Get total orders
$count_query = "SELECT COUNT(*) as total FROM orders o $where";
if ($params) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total / $per_page);

// Get orders - FIX: LIMIT OFFSET urutan
$order_query = "SELECT o.*, c.nama FROM orders o 
                JOIN customers c ON o.customer_id = c.id 
                $where 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";

// Add LIMIT dan OFFSET ke params (urutan benar: LIMIT dulu, baru OFFSET)
$limit_params = $params;
$limit_params[] = $per_page;
$limit_params[] = $offset;
$limit_types = $types . "ii";

$stmt = $conn->prepare($order_query);
if ($limit_params) {
    $stmt->bind_param($limit_types, ...$limit_params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$success = isset($_GET['success']) ? sanitize($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
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
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .filter-form {
            display: flex;
            gap: 1rem;
        }
        .filter-form select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
        }
        .filter-form button {
            padding: 0.75rem 1.5rem;
            background-color: #8B6F47;
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
        }
        .filter-form button:hover {
            background-color: #6B4423;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .orders-table th {
            background-color: #8B6F47;
            color: #F5E6D3;
            padding: 1rem;
            text-align: left;
        }
        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .orders-table tbody tr:hover {
            background-color: #F5F1ED;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.3rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-menunggu_bukti {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-menunggu_verifikasi {
            background-color: #cfe2ff;
            color: #084298;
        }
        .status-diterima {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-ditolak {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-siap_kirim {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-selesai {
            background-color: #d4edda;
            color: #155724;
        }
        .btn-detail {
            padding: 0.5rem 1rem;
            background-color: #8B6F47;
            color: white;
            text-decoration: none;
            border-radius: 0.3rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-detail:hover {
            background-color: #6B4423;
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
    
    
    <div style="display: flex;">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>📦 Manajemen Pesanan</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="filter-section">
                <form method="GET" action="" class="filter-form">
                    <select name="status">
                        <option value="">Semua Status</option>
                        <?php foreach (ORDER_STATUS as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $status_filter === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">🔍 Filter</button>
                </form>
            </div>

            <?php if (count($orders) > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['no_pesanan']); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['nama']); ?></td>
                        <td><?php echo formatCurrency($order['total_bayar']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo str_replace('_', '', $order['status']); ?>">
                                <?php echo ORDER_STATUS[$order['status']] ?? ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($order['created_at']); ?></td>
                        <td>
                            <a href="<?php echo ADMIN_URL; ?>orders/detail.php?id=<?php echo $order['id']; ?>" class="btn-detail">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">« Pertama</a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">‹ Sebelumnya</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Selanjutnya ›</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Terakhir »</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state">
                <p style="font-size: 3rem; margin-bottom: 1rem;">📦</p>
                <p><strong>Belum ada pesanan</strong></p>
                <p style="color: #666;">Menunggu pelanggan membuat pesanan</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

   
</body>
</html>
