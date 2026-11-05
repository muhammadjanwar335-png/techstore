<?php
/**
 * TECHSTORE - Cetak Laporan Penjualan Resmi (A4 PDF Ready)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$start_date = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));

try {
    // 1. Ringkasan
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

    // 2. Daftar Transaksi
    $stmt = $pdo->prepare("
        SELECT t.*, u.name AS customer_name, p.payment_method, p.payment_status
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN payments p ON t.id = p.transaction_id
        WHERE DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at ASC
    ");
    $stmt->execute([$start_date, $end_date]);
    $transactions = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan (<?= $start_date ?> - <?= $end_date ?>) - TechStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #000;
            background: #fff;
            padding: 30px;
            font-size: 11pt;
        }
        .report-header {
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .stat-box {
            border: 1px solid #ccc;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>

<div class="no-print mb-4 d-flex justify-content-between align-items-center">
    <a href="<?= BASE_URL ?>admin/reports/index.php" class="btn btn-outline-secondary btn-sm">
        &larr; Kembali ke Laporan
    </a>
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold">
        Cetak Laporan / Simpan PDF
    </button>
</div>

<!-- Header Laporan -->
<div class="report-header text-center">
    <h3 class="fw-bold mb-1">TECHSTORE INDONESIA</h3>
    <h5 class="fw-bold mb-1">LAPORAN PENJUALAN PERIPHERAL & ACCESSORIES KOMPUTER</h5>
    <div class="small">Gedung Cyber Tech Lt. 5, Jl. Rasuna Said No. 45, Jakarta Selatan | Telp: (021) 555-8989</div>
    <div class="small fw-bold mt-2">
        PERIODE: <?= date('d/m/Y', strtotime($start_date)) ?> S/D <?= date('d/m/Y', strtotime($end_date)) ?>
    </div>
</div>

<!-- Ringkasan Statistik -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="stat-box">
            <div class="small text-muted">Total Pendapatan Bersih</div>
            <div class="fs-5 fw-bold"><?= format_rupiah($total_revenue) ?></div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-box">
            <div class="small text-muted">Total Transaksi</div>
            <div class="fs-5 fw-bold"><?= $total_orders ?> Pesanan</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-box">
            <div class="small text-muted">Total Unit Terjual</div>
            <div class="fs-5 fw-bold"><?= $total_items_sold ?> Item</div>
        </div>
    </div>
</div>

<!-- Tabel Rincian Transaksi -->
<table class="table table-bordered table-sm align-middle mb-4">
    <thead class="table-light text-center small fw-bold">
        <tr>
            <th style="width: 35px;">No</th>
            <th>No. Transaksi</th>
            <th>Tanggal</th>
            <th>Nama Pelanggan</th>
            <th>Metode Bayar</th>
            <th>Status Order</th>
            <th>Status Bayar</th>
            <th class="text-end">Total Penjualan</th>
        </tr>
    </thead>
    <tbody class="small">
        <?php if (empty($transactions)): ?>
            <tr>
                <td colspan="8" class="text-center py-3 text-muted">Tidak ada data transaksi pada periode ini.</td>
            </tr>
        <?php else: ?>
            <?php $no = 1; foreach ($transactions as $t): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="font-monospace fw-bold"><?= htmlspecialchars($t['transaction_code']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                    <td><?= htmlspecialchars($t['recipient_name']) ?></td>
                    <td><?= htmlspecialchars($t['payment_method']) ?></td>
                    <td class="text-center text-uppercase"><?= htmlspecialchars($t['status']) ?></td>
                    <td class="text-center text-uppercase"><?= htmlspecialchars($t['payment_status']) ?></td>
                    <td class="text-end fw-bold"><?= format_rupiah($t['grand_total']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot class="fw-bold">
        <tr>
            <td colspan="7" class="text-end">TOTAL KESELURUHAN:</td>
            <td class="text-end"><?= format_rupiah($total_revenue) ?></td>
        </tr>
    </tfoot>
</table>

<!-- Kolom Tanda Tangan Resmi -->
<div class="row pt-4">
    <div class="col-8">
        <div class="small text-muted">
            <em>Dicetak otomatis melalui Sistem Informasi TechStore pada: <?= date('d F Y, H:i') ?> WIB</em>
        </div>
    </div>
    <div class="col-4 text-center">
        <div class="small">Jakarta, <?= date('d F Y') ?></div>
        <div class="small mb-5">Mengetahui,<br><strong>Manager Operasional TechStore</strong></div>
        <div class="border-top pt-1 fw-bold d-inline-block px-4">
            <?= htmlspecialchars($_SESSION['user_name']) ?>
        </div>
    </div>
</div>

</body>
</html>
