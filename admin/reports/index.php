<?php
/**
 * TECHSTORE - Laporan Penjualan Lengkap (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

// Pengaturan Filter Periode
$period = sanitize($_GET['period'] ?? 'month');
$start_date = sanitize($_GET['start_date'] ?? '');
$end_date = sanitize($_GET['end_date'] ?? '');

if ($period === 'today') {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d');
} elseif ($period === 'week') {
    $start_date = date('Y-m-d', strtotime('-6 days'));
    $end_date = date('Y-m-d');
} elseif ($period === 'month') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
} else {
    // Custom filter
    if (empty($start_date)) $start_date = date('Y-m-01');
    if (empty($end_date)) $end_date = date('Y-m-d');
}

try {
    // 1. Ringkasan Statistik Periode Terpilih
    $stmt_summary = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT t.id) AS total_orders,
            COALESCE(SUM(t.grand_total), 0) AS total_revenue,
            COALESCE(SUM(td.quantity), 0) AS total_items_sold
        FROM transactions t
        LEFT JOIN transaction_details td ON t.id = td.transaction_id
        WHERE DATE(t.created_at) BETWEEN ? AND ? AND t.status != 'cancelled'
    ");
    $stmt_summary->execute([$start_date, $end_date]);
    $summary = $stmt_summary->fetch();

    $total_orders = (int)$summary['total_orders'];
    $total_revenue = (float)$summary['total_revenue'];
    $total_items_sold = (int)$summary['total_items_sold'];
    $avg_order = $total_orders > 0 ? ($total_revenue / $total_orders) : 0;

    // 2. Data Grafik Penjualan Harian dalam Periode
    $stmt_chart = $pdo->prepare("
        SELECT DATE(created_at) AS order_date, SUM(grand_total) AS daily_total
        FROM transactions
        WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'
        GROUP BY DATE(created_at)
        ORDER BY order_date ASC
    ");
    $stmt_chart->execute([$start_date, $end_date]);
    $chart_rows = $stmt_chart->fetchAll();

    $chart_labels = [];
    $chart_values = [];
    foreach ($chart_rows as $cr) {
        $chart_labels[] = date('d M', strtotime($cr['order_date']));
        $chart_values[] = (float)$cr['daily_total'];
    }

    // 3. Produk Paling Laris dalam Periode Terpilih
    $stmt_top = $pdo->prepare("
        SELECT td.product_name, SUM(td.quantity) AS total_sold, SUM(td.subtotal) AS total_sales
        FROM transaction_details td
        JOIN transactions t ON td.transaction_id = t.id
        WHERE DATE(t.created_at) BETWEEN ? AND ? AND t.status != 'cancelled'
        GROUP BY td.product_name
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $stmt_top->execute([$start_date, $end_date]);
    $top_products = $stmt_top->fetchAll();

    // 4. Tabel Seluruh Transaksi dalam Periode
    $stmt_list = $pdo->prepare("
        SELECT t.*, u.name AS customer_name, p.payment_method, p.payment_status, i.invoice_code
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        WHERE DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at DESC
    ");
    $stmt_list->execute([$start_date, $end_date]);
    $transactions = $stmt_list->fetchAll();

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = 'Laporan Penjualan - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Laporan Penjualan</h3>
        <p class="text-muted small mb-0">Analisis pendapatan, jumlah produk terjual, dan tren penjualan aktual dari database.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>admin/reports/export-excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel (CSV)
        </a>
        <a href="<?= BASE_URL ?>admin/reports/print.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i>Cetak Laporan (PDF)
        </a>
    </div>
</div>

<!-- Filter Periode Bar Sesuai Ketentuan H -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
    <form method="GET" action="" class="row g-3 align-items-end">
        <div class="col-lg-3 col-md-6">
            <label class="form-label small fw-semibold text-secondary">Preset Periode Waktu</label>
            <select name="period" class="form-select" onchange="if(this.value !== 'custom') this.form.submit();">
                <option value="today" <?= $period === 'today' ? 'selected' : '' ?>>Hari Ini (<?= date('d M Y') ?>)</option>
                <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>7 Hari Terakhir (Mingguan)</option>
                <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Bulan Ini (<?= date('F Y') ?>)</option>
                <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Kustom Rentang Tanggal</option>
            </select>
        </div>

        <div class="col-lg-3 col-md-3">
            <label class="form-label small fw-semibold text-secondary">Tanggal Mulai</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>

        <div class="col-lg-3 col-md-3">
            <label class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>

        <div class="col-lg-3 col-md-12 d-flex gap-2">
            <input type="hidden" name="period" value="custom">
            <button type="submit" class="btn btn-primary flex-fill fw-bold">
                <i class="bi bi-filter me-1"></i>Filter Laporan
            </button>
            <a href="<?= BASE_URL ?>admin/reports/index.php" class="btn btn-outline-secondary" title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- 4 Metrik Summary Cards Sesuai Spesifikasi H -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Pendapatan</div>
                    <div class="fs-4 fw-extrabold text-primary mt-1"><?= format_rupiah($total_revenue) ?></div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
            <small class="text-muted">Periode: <?= format_date($start_date) ?> - <?= format_date($end_date) ?></small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Transaksi</div>
                    <div class="fs-4 fw-extrabold text-dark mt-1"><?= $total_orders ?> Pesanan</div>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <small class="text-muted">Pesanan aktif (non-cancel)</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Produk Terjual</div>
                    <div class="fs-4 fw-extrabold text-dark mt-1"><?= $total_items_sold ?> Unit</div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <small class="text-muted">Total kuantitas barang keluar</small>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Rata-Rata Nilai Order</div>
                    <div class="fs-4 fw-extrabold text-dark mt-1"><?= format_rupiah($avg_order) ?></div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-calculator"></i>
                </div>
            </div>
            <small class="text-muted">Average Order Value (AOV)</small>
        </div>
    </div>
</div>

<!-- Grafik & Produk Terlaris Periode Ini -->
<div class="row g-4 mb-4">
    <!-- Chart Penjualan -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-line text-primary me-2"></i>Grafik Penjualan Harian</h5>
            <div style="height: 280px; position: relative;">
                <canvas id="reportChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Produk Terlaris Periode Ini -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-trophy text-warning me-2"></i>Produk Paling Laris</h5>
            <?php if (empty($top_products)): ?>
                <div class="text-muted text-center py-4 small">Belum ada data penjualan pada rentang tanggal ini.</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($top_products as $idx => $tprod): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div class="text-truncate me-2" style="max-width: 200px;">
                                <div class="fw-bold small text-dark text-truncate"><?= $idx + 1 ?>. <?= htmlspecialchars($tprod['product_name']) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;"><?= format_rupiah($tprod['total_sales']) ?></div>
                            </div>
                            <span class="badge bg-primary rounded-pill"><?= $tprod['total_sold'] ?> terjual</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabel Riwayat Penjualan Rinci -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i>Tabel Transaksi Penjualan</h5>
        <span class="text-muted small">Menampilkan <strong><?= count($transactions) ?></strong> Transaksi</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Transaksi</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Metode Bayar</th>
                    <th>Total Penjualan</th>
                    <th>Status Pesanan</th>
                    <th>Status Bayar</th>
                    <th class="text-end" style="width: 80px;">Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Tidak ada transaksi tercatat pada periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($transactions as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="fw-bold font-monospace text-dark"><?= htmlspecialchars($row['transaction_code']) ?></span>
                            </td>
                            <td><?= format_date($row['created_at']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['customer_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['recipient_phone']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['payment_method']) ?></span>
                            </td>
                            <td class="fw-bold text-primary">
                                <?= format_rupiah($row['grand_total']) ?>
                            </td>
                            <td><?= get_status_badge($row['status']) ?></td>
                            <td><?= get_payment_badge($row['payment_status']) ?></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $row['invoice_code'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Cetak Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('reportChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($chart_values) ?>,
                backgroundColor: 'rgba(37, 99, 235, 0.75)',
                borderColor: '#2563eb',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) {
                            return 'Rp ' + (val / 1000).toLocaleString('id-ID') + 'k';
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
