<?php
/**
 * TECHSTORE - Struk Kasir Kasir / POS Thermal Receipt
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_login();

$invoice_code = sanitize($_GET['code'] ?? '');
if (empty($invoice_code)) {
    die("Kode invoice tidak ditemukan.");
}

try {
    $stmt = $pdo->prepare("
        SELECT i.*, t.transaction_code, t.recipient_name, t.recipient_phone, t.shipping_courier,
               t.shipping_cost, t.total_amount AS subtotal_items, t.grand_total, t.created_at AS order_created,
               u.name AS user_name, p.payment_method, p.payment_status
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
        die("Data transaksi tidak ditemukan.");
    }

    $stmt_items = $pdo->prepare("SELECT * FROM transaction_details WHERE transaction_id = ?");
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
    <title>Struk <?= htmlspecialchars($inv['transaction_code']) ?> - TechStore</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 78mm;
            margin: 0 auto;
            padding: 15px 10px;
            color: #000;
            font-size: 11px;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .divider-double {
            border-top: 2px solid #000;
            margin: 8px 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .no-print {
            margin-bottom: 15px;
            text-align: center;
        }
        .btn-print {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 5px; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Cetak Struk Kasir</button>
</div>

<div class="text-center">
    <div class="fw-bold" style="font-size: 16px;">TECHSTORE</div>
    <div>SISTEM INFORMASI PERIPHERAL & ACC</div>
    <div>Jl. Rasuna Said No. 45, Jakarta</div>
    <div>Telp: (021) 555-8989</div>
</div>

<div class="divider"></div>

<div class="item-row">
    <span>No. TRX:</span>
    <span class="fw-bold"><?= htmlspecialchars($inv['transaction_code']) ?></span>
</div>
<div class="item-row">
    <span>Invoice:</span>
    <span><?= htmlspecialchars($inv['invoice_code']) ?></span>
</div>
<div class="item-row">
    <span>Tanggal:</span>
    <span><?= date('d/m/Y H:i', strtotime($inv['order_created'])) ?></span>
</div>
<div class="item-row">
    <span>Pelanggan:</span>
    <span><?= htmlspecialchars(substr($inv['recipient_name'], 0, 18)) ?></span>
</div>
<div class="item-row">
    <span>Metode:</span>
    <span><?= htmlspecialchars($inv['payment_method']) ?></span>
</div>

<div class="divider"></div>

<div class="fw-bold item-row">
    <span>PRODUK</span>
    <span>TOTAL</span>
</div>

<div class="divider"></div>

<?php foreach ($items as $it): ?>
    <div style="margin-bottom: 6px;">
        <div class="fw-bold"><?= htmlspecialchars($it['product_name']) ?></div>
        <div class="item-row" style="color: #333;">
            <span><?= $it['quantity'] ?> x <?= number_format($it['price'], 0, ',', '.') ?></span>
            <span>Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></span>
        </div>
    </div>
<?php endforeach; ?>

<div class="divider"></div>

<div class="item-row">
    <span>Subtotal:</span>
    <span>Rp <?= number_format($inv['subtotal_items'], 0, ',', '.') ?></span>
</div>
<div class="item-row">
    <span>Ongkir (<?= htmlspecialchars($inv['shipping_courier']) ?>):</span>
    <span>Rp <?= number_format($inv['shipping_cost'], 0, ',', '.') ?></span>
</div>

<div class="divider-double"></div>

<div class="item-row fw-bold" style="font-size: 13px;">
    <span>TOTAL:</span>
    <span>Rp <?= number_format($inv['grand_total'], 0, ',', '.') ?></span>
</div>

<div class="item-row" style="margin-top: 4px;">
    <span>STATUS:</span>
    <span class="fw-bold"><?= strtoupper($inv['payment_status']) === 'PAID' ? 'LUNAS (PAID)' : 'BELUM LUNAS' ?></span>
</div>

<div class="divider"></div>

<div class="text-center" style="margin-top: 15px; font-size: 10px;">
    <div>TERIMA KASIH TELAH BERBELANJA</div>
    <div>DI TECHSTORE INDONESIA</div>
    <div>Barang yang dibeli bergaransi 1 thn</div>
    <div>www.techstore.com</div>
</div>

</body>
</html>
