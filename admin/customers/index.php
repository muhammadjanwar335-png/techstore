<?php
/**
 * TECHSTORE - Manajemen Data Pelanggan (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$search = sanitize($_GET['q'] ?? '');
$where = ["u.role = 'customer'"];
$params = [];

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql = "
    SELECT u.*, 
           COUNT(t.id) AS total_orders,
           COALESCE(SUM(CASE WHEN t.status = 'completed' THEN t.grand_total ELSE 0 END), 0) AS total_spent
    FROM users u
    LEFT JOIN transactions t ON u.id = t.user_id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY u.id
    ORDER BY u.id DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = 'Data Pelanggan - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>Data Pelanggan</h3>
        <p class="text-muted small mb-0">Daftar pelanggan terdaftar, riwayat belanja, dan kontak pengiriman.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari nama, email, atau no HP..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Cari</button>
        </div>
        <?php if (!empty($search)): ?>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>admin/customers/index.php" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Pelanggan</th>
                    <th>Kontak & WhatsApp</th>
                    <th>Alamat Pengiriman</th>
                    <th>Total Pesanan</th>
                    <th>Total Belanja</th>
                    <th class="text-end" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Tidak ada data pelanggan yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($customers as $c): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                                        <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    <i class="bi bi-telephone me-1 text-success"></i><?= htmlspecialchars($c['phone'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 260px;">
                                <?= htmlspecialchars($c['address'] ?? '-') ?>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary fw-bold"><?= (int)$c['total_orders'] ?> Pesanan</span>
                            </td>
                            <td class="fw-bold text-success">
                                <?= format_rupiah($c['total_spent']) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>admin/customers/detail.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Profil & Riwayat">
                                    <i class="bi bi-eye-fill me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
