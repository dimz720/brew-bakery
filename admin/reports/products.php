<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$tab = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'stok';
$export = isset($_GET['export']) ? sanitize($_GET['export']) : '';

// Best Seller
$best_sellers = $conn->query("
    SELECT p.*, COUNT(oi.id) as total_sold, SUM(oi.jumlah) as qty_sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY qty_sold DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Worst Seller
$worst_sellers = $conn->query("
    SELECT p.*, COUNT(oi.id) as total_sold, SUM(oi.jumlah) as qty_sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    HAVING qty_sold IS NULL OR qty_sold < 5
    ORDER BY qty_sold ASC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Low Stock
$low_stocks = $conn->query("
    SELECT * FROM products WHERE stok <= 10 ORDER BY stok ASC
")->fetch_all(MYSQLI_ASSOC);

// Export Excel
if ($export === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="laporan_produk_' . date('Y-m-d') . '.xls"');
    
    echo "Laporan Produk Brew Bakery\n";
    echo "Tanggal Export: " . date('d M Y H:i:s') . "\n\n";
    
    if ($tab === 'best') {
        echo "Produk Best Seller\n\n";
        echo "No.\tNama Produk\tHarga\tTotal Terjual\tJumlah Terjual\n";
        $no = 1;
        foreach ($best_sellers as $product) {
            echo $no . "\t" . $product['nama'] . "\t" . $product['harga'] . "\t" . ($product['total_sold'] ?? 0) . "\t" . ($product['qty_sold'] ?? 0) . "\n";
            $no++;
        }
    } elseif ($tab === 'worst') {
        echo "Produk Worst Seller\n\n";
        echo "No.\tNama Produk\tHarga\tTotal Terjual\tJumlah Terjual\n";
        $no = 1;
        foreach ($worst_sellers as $product) {
            echo $no . "\t" . $product['nama'] . "\t" . $product['harga'] . "\t" . ($product['total_sold'] ?? 0) . "\t" . ($product['qty_sold'] ?? 0) . "\n";
            $no++;
        }
    } else {
        echo "Produk Stok Rendah\n\n";
        echo "No.\tNama Produk\tHarga\tStok Saat Ini\n";
        $no = 1;
        foreach ($low_stocks as $product) {
            echo $no . "\t" . $product['nama'] . "\t" . $product['harga'] . "\t" . $product['stok'] . "\n";
            $no++;
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produk - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-content { flex: 1; padding: 2rem; background-color: var(--light); }
        
        .report-container { max-width: 1200px; }
        .report-header { margin-bottom: 2rem; }
        .report-header h1 { color: var(--primary); }
        
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #eee;
        }
        
        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .export-btn {
            float: right;
            padding: 0.5rem 1.5rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .table-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            clear: both;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .table td { padding: 1rem; border-bottom: 1px solid #eee; }
        .table tbody tr:hover { background-color: var(--light); }
        
        .stock-low { color: #dc3545; font-weight: 600; }
        .stock-good { color: #28a745; font-weight: 600; }
        
        .empty-state { text-align: center; padding: 3rem; color: #999; }
        
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .tabs { flex-wrap: wrap; }
            .export-btn { float: none; display: block; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="report-container">
                <div class="report-header">
                    <h1>📦 Laporan Produk</h1>
                    <p>Analisis stok, best seller, dan worst seller</p>
                </div>

                <div class="tabs">
                    <button class="tab-btn <?php echo $tab === 'stok' ? 'active' : ''; ?>" onclick="switchTab(this, 'stok')">📊 Stok Rendah</button>
                    <button class="tab-btn <?php echo $tab === 'best' ? 'active' : ''; ?>" onclick="switchTab(this, 'best')">⭐ Best Seller</button>
                    <button class="tab-btn <?php echo $tab === 'worst' ? 'active' : ''; ?>" onclick="switchTab(this, 'worst')">📉 Worst Seller</button>
                </div>

                <!-- Tab Stok Rendah -->
                <div id="stok" class="tab-content <?php echo $tab === 'stok' ? 'active' : ''; ?>">
                    <a href="?tab=stok&export=excel" class="export-btn">📥 Export Excel</a>
                    <?php if (count($low_stocks) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stocks as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['nama']); ?></td>
                                    <td><?php echo formatCurrency($product['harga']); ?></td>
                                    <td><?php echo $product['stok']; ?> pcs</td>
                                    <td>
                                        <?php if ($product['stok'] <= 5): ?>
                                            <span class="stock-low">⚠️ Sangat Rendah</span>
                                        <?php else: ?>
                                            <span class="stock-low">⚠️ Rendah</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <p style="font-size: 3rem;">✅</p>
                        <p>Semua produk memiliki stok yang cukup</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab Best Seller -->
                <div id="best" class="tab-content <?php echo $tab === 'best' ? 'active' : ''; ?>">
                    <a href="?tab=best&export=excel" class="export-btn">📥 Export Excel</a>
                    <?php if (count($best_sellers) > 0 && $best_sellers[0]['qty_sold'] != null): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Total Transaksi</th>
                                    <th>Jumlah Terjual</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($best_sellers as $product): 
                                    if ($product['qty_sold'] == null) continue;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['nama']); ?></td>
                                    <td><?php echo formatCurrency($product['harga']); ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                    <td><strong><?php echo $product['qty_sold']; ?> pcs</strong></td>
                                    <td>
                                        <span class="<?php echo $product['stok'] <= 10 ? 'stock-low' : 'stock-good'; ?>">
                                            <?php echo $product['stok']; ?> pcs
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <p style="font-size: 3rem;">📊</p>
                        <p>Belum ada data penjualan</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab Worst Seller -->
                <div id="worst" class="tab-content <?php echo $tab === 'worst' ? 'active' : ''; ?>">
                    <a href="?tab=worst&export=excel" class="export-btn">📥 Export Excel</a>
                    <?php if (count($worst_sellers) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Total Transaksi</th>
                                    <th>Jumlah Terjual</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($worst_sellers as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['nama']); ?></td>
                                    <td><?php echo formatCurrency($product['harga']); ?></td>
                                    <td><?php echo $product['total_sold'] ?? 0; ?></td>
                                    <td><strong><?php echo $product['qty_sold'] ?? 0; ?> pcs</strong></td>
                                    <td>
                                        <span class="stock-good"><?php echo $product['stok']; ?> pcs</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <p style="font-size: 3rem;">🎉</p>
                        <p>Semua produk memiliki penjualan yang baik</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(btn, tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            btn.classList.add('active');
            
            // Update URL
            window.location.hash = tabName;
        }
    </script>
</body>
</html>
