<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$period = isset($_GET['period']) ? sanitize($_GET['period']) : 'month';
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$export = isset($_GET['export']) ? sanitize($_GET['export']) : '';

// Tentukan range tanggal berdasarkan period
$today = date('Y-m-d');
switch ($period) {
    case 'day':
        $date_from = $today;
        $date_to = $today;
        break;
    case 'week':
        $date_from = date('Y-m-d', strtotime('-7 days'));
        $date_to = $today;
        break;
    case 'month':
        $date_from = date('Y-m-01');
        $date_to = $today;
        break;
    case 'year':
        $date_from = date('Y-01-01');
        $date_to = $today;
        break;
    case 'custom':
        if (empty($date_from) || empty($date_to)) {
            $date_from = date('Y-m-01');
            $date_to = $today;
        }
        break;
}

// Query data penjualan
$query = "SELECT o.*, c.nama as customer_name, COUNT(oi.id) as item_count
          FROM orders o
          JOIN customers c ON o.customer_id = c.id
          LEFT JOIN order_items oi ON o.id = oi.order_id
          WHERE DATE(o.created_at) BETWEEN ? AND ?
          GROUP BY o.id
          ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$sales_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung statistik
$total_sales = 0;
$total_transactions = count($sales_data);
$total_items = 0;

foreach ($sales_data as $sale) {
    $total_sales += $sale['total_bayar'];
    $total_items += $sale['item_count'];
}

// Export Excel
if ($export === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="laporan_penjualan_' . date('Y-m-d') . '.xls"');
    
    echo "Laporan Penjualan Brew Bakery\n";
    echo "Periode: " . date('d M Y', strtotime($date_from)) . " - " . date('d M Y', strtotime($date_to)) . "\n";
    echo "Tanggal Export: " . date('d M Y H:i:s') . "\n\n";
    
    echo "Statistik Ringkas\n";
    echo "Total Penjualan\t" . $total_sales . "\n";
    echo "Total Transaksi\t" . $total_transactions . "\n";
    echo "Total Item Terjual\t" . $total_items . "\n";
    echo "Rata-rata Transaksi\t" . ($total_transactions > 0 ? $total_sales / $total_transactions : 0) . "\n\n";
    
    echo "Detail Penjualan\n";
    echo "No.\tNo. Pesanan\tPelanggan\tJumlah Item\tTotal Bayar\tStatus\tTanggal\n";
    
    $no = 1;
    foreach ($sales_data as $sale) {
        echo $no . "\t" . $sale['no_pesanan'] . "\t" . $sale['customer_name'] . "\t" . $sale['item_count'] . "\t" . $sale['total_bayar'] . "\t" . ORDER_STATUS[$sale['status']] . "\t" . date('d/m/Y H:i', strtotime($sale['created_at'])) . "\n";
        $no++;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-content { flex: 1; padding: 2rem; background-color: var(--light); }
        
        .report-container { max-width: 1200px; }
        .report-header { margin-bottom: 2rem; }
        .report-header h1 { color: var(--primary); }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .filter-group select,
        .filter-group input { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.3rem; }
        .filter-group input[type="date"] { width: 100%; }
        
        .btn-filter { padding: 0.5rem 1.5rem; background: var(--primary); color: white; border: none; border-radius: 0.3rem; cursor: pointer; font-weight: 600; }
        .btn-export { padding: 0.5rem 1.5rem; background: var(--secondary); color: white; border: none; border-radius: 0.3rem; cursor: pointer; font-weight: 600; margin-left: 0.5rem; }
        
        .stats-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary);
        }
        
        .stat-item h4 { font-size: 0.9rem; color: #666; margin: 0 0 0.5rem 0; }
        .stat-item .value { font-size: 1.8rem; font-weight: 800; color: var(--primary); }
        
        .table-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
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
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 0.3rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .empty-state { text-align: center; padding: 3rem; color: #999; }
        
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .filter-row { flex-direction: column; }
            .table { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="report-container">
                <div class="report-header">
                    <h1>💰 Laporan Penjualan</h1>
                    <p>Analisis total penjualan dan jumlah transaksi</p>
                </div>

                <div class="filter-section">
                    <form method="GET" action="">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="period">Periode</label>
                                <select id="period" name="period" onchange="document.getElementById('custom-dates').style.display = this.value === 'custom' ? 'flex' : 'none';">
                                    <option value="day" <?php echo $period === 'day' ? 'selected' : ''; ?>>Hari Ini</option>
                                    <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>7 Hari Terakhir</option>
                                    <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>Bulan Ini</option>
                                    <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>Tahun Ini</option>
                                    <option value="custom" <?php echo $period === 'custom' ? 'selected' : ''; ?>>Custom</option>
                                </select>
                            </div>

                            <div class="filter-group" id="custom-dates" style="display: <?php echo $period === 'custom' ? 'flex' : 'none'; ?>; gap: 0.5rem; flex: 2;">
                                <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                                <span style="align-self: center;">sampai</span>
                                <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                            </div>

                            <button type="submit" class="btn-filter">🔍 Filter</button>
                            <a href="?period=<?php echo $period; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&export=excel" class="btn-export">📥 Export Excel</a>
                        </div>
                    </form>
                </div>

                <div class="stats-box">
                    <div class="stat-item">
                        <h4>Total Penjualan</h4>
                        <div class="value"><?php echo formatCurrency($total_sales); ?></div>
                    </div>
                    <div class="stat-item">
                        <h4>Total Transaksi</h4>
                        <div class="value"><?php echo $total_transactions; ?></div>
                    </div>
                    <div class="stat-item">
                        <h4>Total Item</h4>
                        <div class="value"><?php echo $total_items; ?></div>
                    </div>
                    <div class="stat-item">
                        <h4>Rata-rata Transaksi</h4>
                        <div class="value"><?php echo formatCurrency($total_transactions > 0 ? $total_sales / $total_transactions : 0); ?></div>
                    </div>
                </div>

                <?php if (count($sales_data) > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Item</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_data as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['no_pesanan']); ?></td>
                                <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                <td><?php echo $sale['item_count']; ?></td>
                                <td><strong><?php echo formatCurrency($sale['total_bayar']); ?></strong></td>
                                <td>
                                    <span class="status-badge" style="background-color: #d1ecf1; color: #0c5460;">
                                        <?php echo ORDER_STATUS[$sale['status']] ?? ucfirst($sale['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($sale['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 3rem; margin: 0;">📊</p>
                    <p>Tidak ada data penjualan untuk periode ini</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('period').addEventListener('change', function() {
            document.getElementById('custom-dates').style.display = this.value === 'custom' ? 'flex' : 'none';
        });
    </script>
</body>
</html>
