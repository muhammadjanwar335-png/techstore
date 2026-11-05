<?php
/**
 * TECHSTORE - Riwayat Transaksi & Pelacakan Pesanan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

// Filter Status jika dipilih
$status_filter = sanitize($_GET['status'] ?? 'all');
$where = ["t.user_id = ?"];
$params = [$user_id];

if ($status_filter !== 'all' && !empty($status_filter)) {
    $where[] = "t.status = ?";
    $params[] = $status_filter;
}

try {
    $sql = "
        SELECT t.*, p.payment_method, p.payment_status, p.payment_proof,
               i.invoice_code,
               (SELECT COUNT(*) FROM transaction_details WHERE transaction_id = t.id) AS total_items
        FROM transactions t
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY t.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = 'Riwayat Transaksi - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none text-muted">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>customer/dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Riwayat Pesanan</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pesanan Saya</h2>
            <p class="text-muted small mb-0">Pantau status pemesanan, pembayaran, serta cetak invoice transaksi Anda</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cart-plus me-1"></i>Belanja Lagi
            </a>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="d-flex gap-2 overflow-x-auto pb-3 mb-4" style="white-space: nowrap;">
        <a href="<?= BASE_URL ?>customer/history.php?status=all" class="btn btn-sm <?= $status_filter === 'all' ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
            Semua Pesanan
        </a>
        <a href="<?= BASE_URL ?>customer/history.php?status=pending" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
            Menunggu Pembayaran
        </a>
        <a href="<?= BASE_URL ?>customer/history.php?status=processing" class="btn btn-sm <?= $status_filter === 'processing' ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
            Sedang Diproses
        </a>
        <a href="<?= BASE_URL ?>customer/history.php?status=shipped" class="btn btn-sm <?= $status_filter === 'shipped' ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
            Sedang Dikirim
        </a>
        <a href="<?= BASE_URL ?>customer/history.php?status=completed" class="btn btn-sm <?= $status_filter === 'completed' ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
            Selesai
        </a>
    </div>

    <?php if (empty($transactions)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="display-2 text-muted mb-3"><i class="bi bi-bag-x"></i></div>
            <h4 class="fw-bold mb-2">Belum Ada Transaksi</h4>
            <p class="text-muted mb-4">Anda belum memiliki riwayat pembelian barang untuk kategori filter ini.</p>
            <div>
                <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-grid-fill me-2"></i>Mulai Berbelanja
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-4">
            <?php foreach ($transactions as $t): ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <!-- Header Card -->
                    <div class="card-header bg-light border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <span class="fw-bold text-dark font-monospace"><?= htmlspecialchars($t['transaction_code']) ?></span>
                            <span class="text-muted small">&bull; <?= format_date($t['created_at'], true) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?= get_status_badge($t['status']) ?>
                            <?= get_payment_badge($t['payment_status']) ?>
                        </div>
                    </div>

                    <!-- Body Card -->
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-5">
                                <div class="text-secondary small mb-1">Penerima & Alamat:</div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($t['recipient_name']) ?> (<?= htmlspecialchars($t['recipient_phone']) ?>)</div>
                                <div class="text-muted small text-truncate" style="max-width: 320px;"><?= htmlspecialchars($t['shipping_address']) ?></div>
                                <div class="mt-2 text-secondary small">
                                    <i class="bi bi-truck text-primary me-1"></i>Ekspedisi: <strong><?= htmlspecialchars($t['shipping_courier']) ?></strong>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="text-secondary small mb-1">Metode & Total:</div>
                                <div class="small fw-semibold"><?= htmlspecialchars($t['payment_method']) ?></div>
                                <div class="fs-5 fw-extrabold text-primary"><?= format_rupiah($t['grand_total']) ?></div>
                                <small class="text-muted"><?= $t['total_items'] ?> jenis barang</small>
                            </div>

                            <div class="col-md-4 text-md-end d-flex flex-column gap-2 justify-content-center">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                    <!-- Jika masih belum lunas, berikan tombol bayar -->
                                    <?php if ($t['payment_status'] === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>customer/payment.php?trx=<?= $t['transaction_code'] ?>" class="btn btn-warning btn-sm fw-bold">
                                            <i class="bi bi-wallet2 me-1"></i>Bayar Sekarang
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>customer/order-detail.php?code=<?= $t['transaction_code'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>Rincian
                                    </a>

                                    <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $t['invoice_code'] ?>" class="btn btn-outline-dark btn-sm" title="Lihat Invoice">
                                        <i class="bi bi-receipt me-1"></i>Invoice
                                    </a>

                                    <a href="<?= BASE_URL ?>invoice/struk.php?code=<?= $t['invoice_code'] ?>" target="_blank" class="btn btn-light border btn-sm" title="Cetak Struk Kasir">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Stepper Progress Bar Sederhana -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="row text-center text-secondary small g-1">
                                <div class="col-3 <?= in_array($t['status'], ['pending', 'processing', 'shipped', 'completed']) ? 'text-primary fw-bold' : '' ?>">
                                    <i class="bi bi-check-circle-fill"></i> Dipesan
                                </div>
                                <div class="col-3 <?= in_array($t['status'], ['processing', 'shipped', 'completed']) ? 'text-primary fw-bold' : '' ?>">
                                    <i class="bi <?= in_array($t['status'], ['processing', 'shipped', 'completed']) ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i> Diproses
                                </div>
                                <div class="col-3 <?= in_array($t['status'], ['shipped', 'completed']) ? 'text-primary fw-bold' : '' ?>">
                                    <i class="bi <?= in_array($t['status'], ['shipped', 'completed']) ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i> Dikirim
                                </div>
                                <div class="col-3 <?= $t['status'] === 'completed' ? 'text-success fw-bold' : '' ?>">
                                    <i class="bi <?= $t['status'] === 'completed' ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i> Selesai
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
