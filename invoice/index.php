<?php
/**
 * TECHSTORE - Halaman Faktur Penjualan (Invoice A4 Resmi)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_login();

$invoice_code = sanitize($_GET['code'] ?? '');
if (empty($invoice_code)) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT i.*, t.transaction_code, t.recipient_name, t.recipient_phone, t.shipping_address, 
               t.shipping_courier, t.shipping_cost, t.total_amount AS subtotal_items, t.grand_total,
               t.notes AS order_notes, t.created_at AS order_created,
               u.name AS user_name, u.email AS user_email,
               p.payment_method, p.payment_status, p.payment_date, p.verified_at
        FROM invoices i
        JOIN transactions t ON i.transaction_id = t.id
        JOIN users u ON i.user_id = u.id
        LEFT JOIN payments p ON t.id = p.transaction_id
        WHERE i.invoice_code = ?
        LIMIT 1
    ");
    $stmt->execute([$invoice_code]);
    $inv = $stmt->fetch();

    if (!$inv) {
        die("Invoice tidak ditemukan.");
    }

    // Hanya admin atau pemilik invoice yang dapat melihat
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] != $inv['user_id']) {
        die("Akses ditolak! Anda tidak memiliki izin untuk melihat invoice ini.");
    }

    // Ambil daftar barang pesanan
    $stmt_items = $pdo->prepare("
        SELECT td.*, p.weight
        FROM transaction_details td
        LEFT JOIN products p ON td.product_id = p.id
        WHERE td.transaction_id = ?
    ");
    $stmt_items->execute([$inv['transaction_id']]);
    $items = $stmt_items->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($inv['invoice_code']) ?> - TechStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 30px 15px;
        }
        .invoice-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            max-width: 860px;
            margin: 0 auto;
            padding: 45px;
        }
        .invoice-stamp-paid {
            border: 3px solid #16a34a;
            color: #16a34a;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1.15rem;
            padding: 6px 16px;
            display: inline-block;
            border-radius: 8px;
            transform: rotate(-6deg);
            letter-spacing: 0.1em;
        }
        .invoice-stamp-unpaid {
            border: 3px solid #ca8a04;
            color: #ca8a04;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1.15rem;
            padding: 6px 16px;
            display: inline-block;
            border-radius: 8px;
            transform: rotate(-6deg);
            letter-spacing: 0.1em;
        }
        @media print {
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .invoice-card {
                box-shadow: none !important;
                padding: 20px !important;
                max-width: 100% !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Action Bar (Tidak Dicetak) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 no-print mx-auto" style="max-width: 860px;">
        <div class="d-flex gap-2">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>admin/transactions/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Admin Transaksi
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>customer/history.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Riwayat Pesanan
                </a>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-printer-fill me-1"></i>Cetak Invoice (A4)
            </button>
            <a href="<?= BASE_URL ?>invoice/struk.php?code=<?= $inv['invoice_code'] ?>" target="_blank" class="btn btn-outline-dark btn-sm px-3">
                <i class="bi bi-receipt me-1"></i>Cetak Struk Kasir
            </a>
        </div>
    </div>

    <!-- Main Invoice Paper Document -->
    <div class="invoice-card" id="invoiceArea">
        <!-- Header: Logo Toko & Nomor Invoice -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-cpu-fill text-primary fs-2"></i>
                    <h3 class="fw-bold mb-0 text-dark">TECH<span class="text-primary">STORE</span></h3>
                </div>
                <div class="text-secondary small leading-tight">
                    <strong>PT TechStore Indonesia</strong><br>
                    Gedung Cyber Tech Lt. 5, Jl. Rasuna Said No. 45<br>
                    Jakarta Selatan, DKI Jakarta 12950<br>
                    Telp: (021) 555-8989 | Email: billing@techstore.com
                </div>
            </div>

            <div class="text-end">
                <h4 class="fw-bold text-primary mb-1">INVOICE PENJUALAN</h4>
                <div class="font-monospace fw-bold text-dark fs-5"><?= htmlspecialchars($inv['invoice_code']) ?></div>
                <div class="text-muted small">No. Transaksi: <?= htmlspecialchars($inv['transaction_code']) ?></div>
                <div class="mt-2">
                    <?php if ($inv['payment_status'] === 'paid' || $inv['status'] === 'paid'): ?>
                        <div class="invoice-stamp-paid"><i class="bi bi-check-circle me-1"></i>LUNAS / PAID</div>
                    <?php else: ?>
                        <div class="invoice-stamp-unpaid"><i class="bi bi-hourglass-split me-1"></i>UNPAID</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info Penerima & Tanggal Transaksi -->
        <div class="row mb-4">
            <div class="col-6">
                <div class="text-uppercase text-secondary fw-bold small mb-1">Ditagihkan Kepada (Customer):</div>
                <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($inv['recipient_name']) ?></h6>
                <div class="text-secondary small mb-1">No. HP: <?= htmlspecialchars($inv['recipient_phone']) ?></div>
                <div class="text-secondary small mb-1">Email: <?= htmlspecialchars($inv['user_email']) ?></div>
                <div class="text-muted small leading-snug" style="max-width: 320px;">
                    Alamat: <?= htmlspecialchars($inv['shipping_address']) ?>
                </div>
            </div>

            <div class="col-6 text-end">
                <div class="text-uppercase text-secondary fw-bold small mb-1">Detail Pembayaran:</div>
                <div class="small mb-1"><strong>Tanggal Terbit:</strong> <?= format_date($inv['invoice_date']) ?></div>
                <div class="small mb-1"><strong>Jatuh Tempo:</strong> <?= format_date($inv['due_date']) ?></div>
                <div class="small mb-1"><strong>Metode Bayar:</strong> <?= htmlspecialchars($inv['payment_method']) ?></div>
                <div class="small mb-1"><strong>Kurir Pengiriman:</strong> <?= htmlspecialchars($inv['shipping_courier']) ?></div>
            </div>
        </div>

        <!-- Tabel Barang -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Deskripsi Barang / Accessories</th>
                        <th class="text-center" style="width: 100px;">Jumlah</th>
                        <th class="text-end" style="width: 150px;">Harga Satuan</th>
                        <th class="text-end" style="width: 160px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($items as $it): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($it['product_name']) ?></strong>
                            </td>
                            <td class="text-center"><?= $it['quantity'] ?> unit</td>
                            <td class="text-end"><?= format_rupiah($it['price']) ?></td>
                            <td class="text-end fw-bold text-dark"><?= format_rupiah($it['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-semibold">Subtotal Produk:</td>
                        <td class="text-end fw-bold"><?= format_rupiah($inv['subtotal_items']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end fw-semibold">Ongkos Kirim (<?= htmlspecialchars($inv['shipping_courier']) ?>):</td>
                        <td class="text-end fw-bold"><?= format_rupiah($inv['shipping_cost']) ?></td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-extrabold fs-6">TOTAL TAGIHAN:</td>
                        <td class="text-end fw-extrabold text-primary fs-5"><?= format_rupiah($inv['grand_total']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer Invoice & Catatan -->
        <div class="row align-items-end pt-3">
            <div class="col-8">
                <div class="border rounded p-3 bg-light small text-secondary">
                    <strong>Syarat & Ketentuan:</strong><br>
                    1. Invoice ini adalah bukti transaksi yang sah dan diterbitkan secara elektronik oleh sistem TechStore.<br>
                    2. Seluruh produk bergaransi resmi distributor selama 1 tahun terhitung sejak tanggal faktur.<br>
                    3. Simpan invoice ini jika sewaktu-waktu membutuhkan klaim garansi produk.
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="small text-secondary mb-5">Hormat Kami,<br><strong>TechStore Indonesia</strong></div>
                <div class="fw-bold text-dark border-top pt-2 d-inline-block px-4">Authorized Signature</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
