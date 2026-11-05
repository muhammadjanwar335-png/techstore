<?php
/**
 * TECHSTORE - Export Laporan Penjualan ke Excel (Format CSV UTF-8)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$start_date = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.name AS customer_name, u.email AS customer_email,
               p.payment_method, p.payment_status
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN payments p ON t.id = p.transaction_id
        WHERE DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $transactions = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$filename = "Laporan_Penjualan_TechStore_" . date('Ymd_His') . ".csv";

// Header HTTP untuk Unduhan Excel CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis UTF-8 BOM agar Excel Windows membaca aksen dan huruf dengan benar
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Baris Judul
fputcsv($output, ['LAPORAN PENJUALAN TECHSTORE']);
fputcsv($output, ['Periode:', $start_date . ' s/d ' . $end_date]);
fputcsv($output, ['Diekspor pada:', date('d F Y, H:i') . ' WIB']);
fputcsv($output, []); // Baris kosong

// Header Kolom Tabel
fputcsv($output, [
    'No',
    'Kode Transaksi',
    'Tanggal Transaksi',
    'Nama Pelanggan',
    'Email',
    'No Telepon',
    'Ekspedisi Kurir',
    'Metode Pembayaran',
    'Subtotal Produk (Rp)',
    'Ongkir (Rp)',
    'Grand Total (Rp)',
    'Status Pesanan',
    'Status Pembayaran'
]);

$no = 1;
$total_revenue = 0;

foreach ($transactions as $t) {
    $total_revenue += $t['grand_total'];
    fputcsv($output, [
        $no++,
        $t['transaction_code'],
        $t['created_at'],
        $t['recipient_name'],
        $t['customer_email'],
        $t['recipient_phone'],
        $t['shipping_courier'],
        $t['payment_method'],
        (float)$t['total_amount'],
        (float)$t['shipping_cost'],
        (float)$t['grand_total'],
        strtoupper($t['status']),
        strtoupper($t['payment_status'])
    ]);
}

// Baris Total
fputcsv($output, []);
fputcsv($output, ['', '', '', '', '', '', '', '', '', 'TOTAL PENJUALAN:', $total_revenue]);

fclose($output);
exit;
