<?php
/**
 * TECHSTORE - Dashboard Administrator
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_admin();

try {
    // 1. Metrik Statistik Utama
    $total_products = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $total_transactions = (int)$pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    $total_revenue = (float)$pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM transactions WHERE status = 'completed'")->fetchColumn();
    $low_stock_count = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock < 5 AND status = 'active'")->fetchColumn();

    // 2. Data Grafik Penjualan 7 Hari Terakhir
    $chart_labels = [];
    $chart_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('d M', strtotime($date));

        $stmt_day = $pdo->prepare("
            SELECT COALESCE(SUM(grand_total), 0) 
            FROM transactions 
            WHERE DATE(created_at) = ? AND status != 'cancelled'
        ");
        $stmt_day->execute([$date]);
        $chart_data[] = (float)$stmt_day->fetchColumn();
    }

    // 3. Data Komposisi Kategori (Doughnut Chart)
    $stmt_cat_chart = $pdo->query("
        SELECT c.name, COUNT(p.id) AS total
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
        GROUP BY c.id
        ORDER BY total DESC
    ");
    $cat_chart_data = $stmt_cat_chart->fetchAll();
    $cat_labels = array_column($cat_chart_data, 'name');
    $cat_totals = array_column($cat_chart_data, 'total');

    // 4. 5 Transaksi Terbaru
    $stmt_recent = $pdo->query("
        SELECT t.*, u.name AS customer_name, p.payment_method, p.payment_status, i.invoice_code
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        ORDER BY t.id DESC
        LIMIT 5
    ");
    $recent_transactions = $stmt_recent->fetchAll();

    // 5. Produk Terlaris (Top 5 Best Seller)
    $stmt_top = $pdo->query("
        SELECT p.id, p.name, p.image, c.name AS category_name, p.price,
               COALESCE(SUM(td.quantity), 0) AS total_sold
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN transaction_details td ON p.id = td.product_id
        GROUP BY p.id
        ORDER BY total_sold DESC, p.id DESC
        LIMIT 5
    ");
    $top_products = $stmt_top->fetchAll();

} catch (Exception $e) {
    die("Error mengambil data dashboard: " . $e->getMessage());
}

$page_title = 'Dashboard Admin - TechStore';
include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';
include __DIR__ . '/../includes/admin_topbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Dashboard Administrator</h3>
        <p class="text-muted small mb-0">Ringkasan performa penjualan, stok produk, dan aktivitas transaksi TechStore.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>admin/reports/index.php" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>Laporan Lengkap
        </a>
        <a href="<?= BASE_URL ?>admin/products/create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Tambah Produk
        </a>
    </div>
</div>

<!-- Warning Alert jika ada stok menipis atau pembayaran pending -->
<?php if ($count_pending_payments > 0 || $low_stock_count > 0): ?>
    <div class="row g-3 mb-4">
        <?php if ($count_pending_payments > 0): ?>
            <div class="col-md-6">
                <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between mb-0 p-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                        <div>
                            <strong class="text-dark">Verifikasi Pembayaran!</strong>
                            <div class="small text-muted">Terdapat <?= $count_pending_payments ?> pembayaran baru menunggu konfirmasi admin.</div>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>admin/payments/index.php" class="btn btn-warning btn-sm fw-bold">Verifikasi</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($low_stock_count > 0): ?>
            <div class="col-md-6">
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center justify-content-between mb-0 p-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <div>
                            <strong class="text-dark">Peringatan Stok Menipis!</strong>
                            <div class="small text-muted">Terdapat <?= $low_stock_count ?> produk dengan persediaan di bawah 5 unit.</div>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>admin/products/index.php?filter=low_stock" class="btn btn-danger btn-sm fw-bold">Cek Stok</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- 5 Stat Cards Metrics Sesuai Spesifikasi I -->
<div class="row g-3 mb-4">
    <!-- Total Pendapatan -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Pendapatan</div>
                    <div class="fs-4 fw-extrabold text-primary mt-1"><?= format_rupiah($total_revenue) ?></div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="small text-success"><i class="bi bi-arrow-up-right"></i> Dari transaksi berstatus selesai</div>
        </div>
    </div>

    <!-- Total Transaksi -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Transaksi</div>
                    <div class="fs-4 fw-extrabold text-dark mt-1"><?= $total_transactions ?> Pesanan</div>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <div class="small text-muted"><a href="<?= BASE_URL ?>admin/transactions/index.php" class="text-decoration-none">Kelola transaksi &rarr;</a></div>
        </div>
    </div>

    <!-- Total Produk -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Produk</div>
                    <div class="fs-4 fw-extrabold text-dark mt-1"><?= $total_products ?> Barang</div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <div class="small text-muted"><a href="<?= BASE_URL ?>admin/products/index.php" class="text-decoration-none">Lihat katalog barang &rarr;</a></div>
        </div>
    </div>

    <!-- Total Pelanggan & Stok Menipis -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Pelanggan Terdaftar</div>
                    <div class="fs-4 fw-extrabold text-dark mt-1"><?= $total_customers ?> Akun</div>
                </div>
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="small text-muted"><a href="<?= BASE_URL ?>admin/customers/index.php" class="text-decoration-none">Data pelanggan &rarr;</a></div>
        </div>
    </div>
</div>

<!-- Bagian Grafik Visualisasi Menggunakan Chart.js -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Pendapatan 7 Hari Terakhir -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Tren Penjualan (7 Hari Terakhir)</h5>
                <span class="badge bg-light text-muted border">Minggu Ini</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="salesTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Komposisi Kategori -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Produk per Kategori</h5>
            <div style="height: 280px; position: relative;" class="d-flex align-items-center justify-content-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- 2 Kolom: Transaksi Terbaru & Produk Terlaris -->
<div class="row g-4">
    <!-- Transaksi Terbaru -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Transaksi Terbaru</h5>
                <a href="<?= BASE_URL ?>admin/transactions/index.php" class="btn btn-outline-primary btn-sm">Semua Transaksi</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Total Tagihan</th>
                            <th>Status Order</th>
                            <th>Pembayaran</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $rt): ?>
                            <tr>
                                <td class="fw-bold font-monospace"><?= htmlspecialchars($rt['transaction_code']) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($rt['customer_name']) ?></div>
                                    <small class="text-muted"><?= format_date($rt['created_at']) ?></small>
                                </td>
                                <td class="fw-bold text-primary"><?= format_rupiah($rt['grand_total']) ?></td>
                                <td><?= get_status_badge($rt['status']) ?></td>
                                <td><?= get_payment_badge($rt['payment_status']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>admin/transactions/detail.php?id=<?= $rt['id'] ?>" class="btn btn-light border btn-sm" title="Kelola">
                                        <i class="bi bi-gear-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Produk Terlaris -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom p-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i>Top 5 Produk Terlaris</h5>
            </div>
            <div class="p-3">
                <?php foreach ($top_products as $idx => $tp): ?>
                    <div class="d-flex align-items-center gap-3 py-2 <?= $idx > 0 ? 'border-top' : '' ?>">
                        <span class="badge bg-light text-dark border rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;"><?= $idx + 1 ?></span>
                        <img src="<?= get_product_image_url($tp['image'], $tp['category_name']) ?>" alt="<?= htmlspecialchars($tp['name']) ?>" class="rounded-3 border" style="width: 44px; height: 44px; object-fit: cover;">
                        <div class="flex-grow-1 text-truncate">
                            <div class="fw-bold text-dark text-truncate small"><?= htmlspecialchars($tp['name']) ?></div>
                            <div class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($tp['category_name']) ?></div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary-subtle text-primary fw-bold"><?= (int)$tp['total_sold'] ?> terjual</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Script Inisialisasi Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Line Chart Tren Penjualan 7 Hari
    const ctxSales = document.getElementById('salesTrendsChart').getContext('2d');
    new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($chart_data) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4
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

    // 2. Doughnut Chart Kategori
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($cat_labels) ?>,
            datasets: [{
                data: <?= json_encode($cat_totals) ?>,
                backgroundColor: [
                    '#2563eb', '#06b6d4', '#10b981', '#f59e0b',
                    '#8b5cf6', '#ec4899', '#64748b', '#3b82f6'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 10 } }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
