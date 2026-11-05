<?php
/**
 * TECHSTORE - Admin Header Partial
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Pastikan hanya admin yang dapat mengakses
check_admin();

$page_title = isset($page_title) ? $page_title . ' - TechStore Admin' : 'Panel Administrator - TechStore';

// Ambil notifikasi penting untuk badge sidebar:
// 1. Pesanan yang butuh verifikasi pembayaran
$stmt_pending_pay = $pdo->query("SELECT COUNT(*) AS total FROM payments WHERE payment_status = 'pending'");
$count_pending_payments = (int)$stmt_pending_pay->fetchColumn();

// 2. Produk dengan stok menipis (< 5)
$stmt_low_stock = $pdo->query("SELECT COUNT(*) AS total FROM products WHERE stock < 5 AND status = 'active'");
$count_low_stock = (int)$stmt_low_stock->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons 1.11.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="admin-wrapper">
