<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

// Quick stats
$today = date('Y-m-d');

$sales_today = $conn->query("SELECT SUM(total_bayar) as total FROM orders WHERE DATE(created_at) = '$today'")->fetch_assoc()['total'] ?? 0;
$orders_today = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = '$today'")->fetch_assoc()['count'];
$products_low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stok <= 10")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-content { flex: 1; padding: 2rem; background-color: var(--light); }
        
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { color: var(--primary); margin-bottom: 0.5rem; }
        
        .stats-grid {
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
            border-left: 4px solid var(--primary);
        }
        
        .stat-card h3 { font-size: 0.9rem; color: #666; margin: 0 0 0.5rem 0; }
        .stat-card .number { font-size: 2rem; font-weight: 800; color: var(--primary); }
        
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .report-card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-color: var(--primary);
        }
        
        .report-icon { font-size: 3rem; margin-bottom: 1rem; }
        .report-card h3 { font-size: 1.3rem; color: var(--primary); margin-bottom: 0.5rem; }
        .report-card p { color: #666; font-size: 0.95rem; line-height: 1.6; }
        .report-card .btn { 
            display: inline-block;
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 0.3rem;
            text-decoration: none;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .reports-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>📊 Laporan & Analitik</h1>
                <p>Pantau data penjualan, produk, dan pendapatan Brew Bakery</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Penjualan Hari Ini</h3>
                    <div class="number"><?php echo formatCurrency($sales_today); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pesanan Hari Ini</h3>
                    <div class="number"><?php echo $orders_today; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Produk Hampir Habis</h3>
                    <div class="number"><?php echo $products_low_stock; ?></div>
                </div>
            </div>

            <div class="reports-grid">
                <a href="<?php echo ADMIN_URL; ?>reports/sales.php" class="report-card">
                    <div class="report-icon">💰</div>
                    <h3>Laporan Penjualan</h3>
                    <p>Total penjualan, jumlah transaksi, dan analisis berdasarkan periode waktu</p>
                    <div class="btn">Buka Laporan →</div>
                </a>

                <a href="<?php echo ADMIN_URL; ?>reports/products.php" class="report-card">
                    <div class="report-icon">📦</div>
                    <h3>Laporan Produk</h3>
                    <p>Status stok, produk best seller, dan produk dengan penjualan terendah</p>
                    <div class="btn">Buka Laporan →</div>
                </a>

                <a href="<?php echo ADMIN_URL; ?>reports/revenue.php" class="report-card">
                    <div class="report-icon">📈</div>
                    <h3>Laporan Pendapatan</h3>
                    <p>Analisis pendapatan per periode dengan detail transaksi</p>
                    <div class="btn">Buka Laporan →</div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
