<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkAdminAuth();

// Get Statistics
$today = date('Y-m-d');

// Pesanan baru
$query_new_orders = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = ?";
$stmt = $conn->prepare($query_new_orders);
$stmt->bind_param("s", $today);
$stmt->execute();
$new_orders = $stmt->get_result()->fetch_assoc()['count'];

// Pesanan diproses
$query_processing = "SELECT COUNT(*) as count FROM orders WHERE status IN ('menunggu_verifikasi', 'diterima')";
$processing = $conn->query($query_processing)->fetch_assoc()['count'];

// Pesanan siap kirim
$query_ready = "SELECT COUNT(*) as count FROM orders WHERE status = 'siap_kirim'";
$ready = $conn->query($query_ready)->fetch_assoc()['count'];

// Produk hampir habis
$query_low_stock = "SELECT COUNT(*) as count FROM products WHERE stok <= 10";
$low_stock = $conn->query($query_low_stock)->fetch_assoc()['count'];

// Total penjualan hari ini
$query_sales = "SELECT SUM(total_bayar) as total FROM orders WHERE DATE(created_at) = ?";
$stmt = $conn->prepare($query_sales);
$stmt->bind_param("s", $today);
$stmt->execute();
$sales = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Pesanan terbaru - PERBAIKAN DI SINI
$query_recent = "SELECT o.*, c.nama FROM orders o 
                 JOIN customers c ON o.customer_id = c.id 
                 ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($query_recent)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Brew Bakery</title>
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
            flex: 1;
            padding: 2rem;
            background-color: #F5F1ED;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #8B6F47;
        }
        .stat-card h3 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
            margin-top: 0;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #8B6F47;
        }
        .stat-card.warning {
            border-left-color: #D4A574;
        }
        .stat-card.warning .number {
            color: #D4A574;
        }
        .recent-orders {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .recent-orders h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #6B4423;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background-color: #8B6F47;
            color: #F5E6D3;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
        }
        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .table tbody tr:hover {
            background-color: #F5F1ED;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: #D4A574;
            color: #6B4423;
        }
        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    
    
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <h1>📊 Dashboard Admin</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?>!</p>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Pesanan Hari Ini</h3>
                    <div class="number"><?php echo $new_orders; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pesanan Diproses</h3>
                    <div class="number"><?php echo $processing; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Siap Dikirim</h3>
                    <div class="number"><?php echo $ready; ?></div>
                </div>
                <div class="stat-card warning">
                    <h3>Produk Hampir Habis</h3>
                    <div class="number"><?php echo $low_stock; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Penjualan Hari Ini</h3>
                    <div class="number"><?php echo formatCurrency($sales); ?></div>
                </div>
            </div>

            <div class="recent-orders">
                <h3>📦 Pesanan Terbaru</h3>
                <?php if (count($recent_orders) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['no_pesanan']); ?></td>
                            <td><?php echo htmlspecialchars($order['nama']); ?></td>
                            <td><?php echo formatCurrency($order['total_bayar']); ?></td>
                            <td>
                                <span class="status-badge" style="background-color: #cfe2ff; color: #084298;">
                                    <?php echo ORDER_STATUS[$order['status']] ?? ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                            <td><a href="<?php echo ADMIN_URL; ?>orders/detail.php?id=<?php echo $order['id']; ?>" style="display: inline-block; padding: 0.5rem 1rem; background-color: #8B6F47; color: white; text-decoration: none; border-radius: 0.3rem; font-weight: 600; font-size: 0.85rem;">Lihat</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: #999; padding: 2rem;">Belum ada pesanan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

   
</body>
</html>