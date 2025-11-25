<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

checkAdminAuth();

$period = isset($_GET['period']) ? sanitize($_GET['period']) : 'month';
$export = isset($_GET['export']) ? sanitize($_GET['export']) : '';

$today = date('Y-m-d');
$revenue_data = [];

if ($period === 'day') {
    // Revenue per jam hari ini
    $query = "SELECT 
        HOUR(created_at) as hour,
        DATE_FORMAT(created_at, '%H:00') as time_label,
        COUNT(*) as transaction_count,
        SUM(total_bayar) as total_revenue
        FROM orders
        WHERE DATE(created_at) = ?
        GROUP BY HOUR(created_at)
        ORDER BY hour ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
} elseif ($period === 'week') {
    // Revenue per hari 7 hari terakhir
    $query = "SELECT 
        DATE(created_at) as date,
        DATE_FORMAT(created_at, '%a, %d %b') as date_label,
        COUNT(*) as transaction_count,
        SUM(total_bayar) as total_revenue
        FROM orders
        WHERE created_at >= DATE_SUB(?, INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
} elseif ($period === 'month') {
    // Revenue per hari bulan ini
    $query = "SELECT 
        DATE(created_at) as date,
        DATE_FORMAT(created_at, '%d %b') as date_label,
        COUNT(*) as transaction_count,
        SUM(total_bayar) as total_revenue
        FROM orders
        WHERE YEAR(created_at) = YEAR(?) AND MONTH(created_at) = MONTH(?)
        GROUP BY DATE(created_at)
        ORDER BY date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $today, $today);
} else {
    // Revenue per bulan tahun ini
    $query = "SELECT 
        MONTH(created_at) as month,
        DATE_FORMAT(created_at, '%b') as month_label,
        COUNT(*) as transaction_count,
        SUM(total_bayar) as total_revenue
        FROM orders
        WHERE YEAR(created_at) = YEAR(?)
        GROUP BY MONTH(created_at)
        ORDER BY month ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
}

$stmt->execute();
$revenue_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Total revenue
$total_revenue = array_sum(array_column($revenue_data, 'total_revenue'));
$total_transactions = array_sum(array_column($revenue_data, 'transaction_count'));
$avg_transaction = $total_transactions > 0 ? $total_revenue / $total_transactions : 0;

// Export Excel
if ($export === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="laporan_pendapatan_' . date('Y-m-d') . '.xls"');
    
    echo "Laporan Pendapatan Brew Bakery\n";
    echo "Periode: " . ucfirst($period) . "\n";
    echo "Tanggal Export: " . date('d M Y H:i:s') . "\n\n";
    
    echo "Statistik Ringkas\n";
    echo "Total Pendapatan\t" . $total_revenue . "\n";
    echo "Total Transaksi\t" . $total_transactions . "\n";
    echo "Rata-rata Transaksi\t" . $avg_transaction . "\n\n";
    
    echo "Detail Pendapatan\n";
    echo "Periode\tJumlah Transaksi\tTotal Pendapatan\n";
    
    foreach ($revenue_data as $data) {
        $label = isset($data['time_label']) ? $data['time_label'] : (isset($data['date_label']) ? $data['date_label'] : $data['month_label']);
        echo $label . "\t" . $data['transaction_count'] . "\t" . $data['total_revenue'] . "\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan - Brew Bakery Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-content { flex: 1; padding: 2rem; background-color: var(--light); }
        
        .report-container { max-width: 1200px; }
        .report-header { margin-bottom: 2rem; }
        .report-header h1 { color: var(--primary); }
        
        .period-selector {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .period-selector .select-group { flex: 1; max-width: 200px; }
        .period-selector label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .period-selector select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.3rem; }
        
        .btn-filter { padding: 0.5rem 1.5rem; background: var(--primary); color: white; border: none; border-radius: 0.3rem; cursor: pointer; font-weight: 600; }
        .btn-export { padding: 0.5rem 1.5rem; background: var(--secondary); color: white; border: none; border-radius: 0.3rem; cursor: pointer; font-weight: 600; }
        
        .stats-box {
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
        
        .stat-card h4 { font-size: 0.9rem; color: #666; margin: 0 0 0.5rem 0; }
        .stat-card .value { font-size: 2rem; font-weight: 800; color: var(--primary); }
        
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
        
        .empty-state { text-align: center; padding: 3rem; color: #999; }
        
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .period-selector { flex-direction: column; align-items: stretch; }
            .btn-filter, .btn-export { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="report-container">
                <div class="report-header">
                    <h1>📈 Laporan Pendapatan</h1>
                    <p>Analisis revenue berdasarkan periode waktu</p>
                </div>

                <div class="period-selector">
                    <form method="GET" action="" style="display: flex; gap: 1rem; align-items: flex-end; width: 100%;">
                        <div class="select-group">
                            <label for="period">Tampilkan per:</label>
                            <select id="period" name="period" onchange="this.form.submit();">
                                <option value="day" <?php echo $period === 'day' ? 'selected' : ''; ?>>Per Jam (Hari Ini)</option>
                                <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>Per Hari (7 Hari)</option>
                                <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>Per Hari (Bulan Ini)</option>
                                <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>Per Bulan (Tahun Ini)</option>
                            </select>
                        </div>
                        <a href="?period=<?php echo $period; ?>&export=excel" class="btn-export">📥 Export Excel</a>
                    </form>
                </div>

                <div class="stats-box">
                    <div class="stat-card">
                        <h4>Total Pendapatan</h4>
                        <div class="value"><?php echo formatCurrency($total_revenue); ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Transaksi</h4>
                        <div class="value"><?php echo $total_transactions; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Rata-rata per Transaksi</h4>
                        <div class="value"><?php echo formatCurrency($avg_transaction); ?></div>
                    </div>
                </div>

                <?php if (count($revenue_data) > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Jumlah Transaksi</th>
                                <th>Total Pendapatan</th>
                                <th>Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenue_data as $data): 
                                $label = isset($data['time_label']) ? $data['time_label'] : (isset($data['date_label']) ? $data['date_label'] : $data['month_label']);
                                $avg = $data['transaction_count'] > 0 ? $data['total_revenue'] / $data['transaction_count'] : 0;
                            ?>
                            <tr>
                                <td><?php echo $label; ?></td>
                                <td><?php echo $data['transaction_count']; ?></td>
                                <td><strong><?php echo formatCurrency($data['total_revenue']); ?></strong></td>
                                <td><?php echo formatCurrency($avg); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 3rem;">📊</p>
                    <p>Tidak ada data pendapatan untuk periode ini</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
