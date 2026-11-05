<?php
/**
 * TECHSTORE - Detail Profil & Transaksi Pelanggan (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . 'admin/customers/index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer' LIMIT 1");
    $stmt->execute([$id]);
    $cust = $stmt->fetch();

    if (!$cust) {
        set_flash('danger', 'Pelanggan tidak ditemukan.');
        header('Location: ' . BASE_URL . 'admin/customers/index.php');
        exit;
    }

    // Riwayat transaksi customer ini
    $stmt_orders = $pdo->prepare("
        SELECT t.*, p.payment_method, p.payment_status, i.invoice_code
        FROM transactions t
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        WHERE t.user_id = ?
        ORDER BY t.id DESC
    ");
    $stmt_orders->execute([$id]);
    $orders = $stmt_orders->fetchAll();

    // Total spent
    $total_spent = 0;
    foreach ($orders as $o) {
        if ($o['status'] === 'completed') {
            $total_spent += $o['grand_total'];
        }
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Profil Pelanggan ' . $cust['name'] . ' - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-badge text-primary me-2"></i>Detail Pelanggan</h3>
        <p class="text-muted small mb-0">Informasi data diri dan riwayat belanja pelanggan.</p>
    </div>
    <a href="<?= BASE_URL ?>admin/customers/index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-4 mb-4">
    <!-- Info Profil Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center fw-bold fs-2 mb-3" style="width: 72px; height: 72px;">
                <?= strtoupper(substr($cust['name'], 0, 1)) ?>
            </div>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($cust['name']) ?></h5>
            <div class="text-muted small mb-3"><?= htmlspecialchars($cust['email']) ?></div>

            <hr>

            <div class="text-start small">
                <div class="mb-2">
                    <strong>No. Telepon / WA:</strong><br>
                    <span><?= htmlspecialchars($cust['phone'] ?? '-') ?></span>
                </div>
                <div class="mb-2">
                    <strong>Alamat Utama:</strong><br>
                    <span class="text-muted"><?= htmlspecialchars($cust['address'] ?? '-') ?></span>
                </div>
                <div>
                    <strong>Bergabung Sejak:</strong><br>
                    <span class="text-muted"><?= format_date($cust['created_at']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="col-lg-8">
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small">Total Pesanan Dibuat</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= count($orders) ?> Transaksi</div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small">Total Pembelian Selesai</div>
                    <div class="fs-4 fw-bold text-success mt-1"><?= format_rupiah($total_spent) ?></div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Transaksi -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom p-3 fw-bold">
                <i class="bi bi-receipt text-primary me-2"></i>Daftar Transaksi Pelanggan
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. TRX</th>
                            <th>Tanggal</th>
                            <th>Total Tagihan</th>
                            <th>Status Order</th>
                            <th>Pembayaran</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Pelanggan belum pernah melakukan transaksi.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td class="fw-bold font-monospace"><?= htmlspecialchars($ord['transaction_code']) ?></td>
                                    <td><?= format_date($ord['created_at']) ?></td>
                                    <td class="fw-bold text-primary"><?= format_rupiah($ord['grand_total']) ?></td>
                                    <td><?= get_status_badge($ord['status']) ?></td>
                                    <td><?= get_payment_badge($ord['payment_status']) ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>admin/transactions/detail.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-light border" title="Kelola Pesanan">
                                            <i class="bi bi-gear-fill"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $ord['invoice_code'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Lihat Invoice">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
