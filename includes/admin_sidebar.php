<?php
/**
 * TECHSTORE - Admin Sidebar Template
 */
$current_uri = $_SERVER['REQUEST_URI'];
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <i class="bi bi-cpu-fill text-primary fs-3"></i>
        <div>
            <h5 class="mb-0 fw-bold text-white fs-5">TECH<span class="text-primary">STORE</span></h5>
            <small class="text-secondary" style="font-size: 0.72rem; letter-spacing: 0.05em;">ADMIN DASHBOARD</small>
        </div>
    </div>

    <ul class="admin-sidebar-menu">
        <li class="admin-menu-header">Utama</li>
        <li>
            <a href="<?= BASE_URL ?>admin/dashboard.php" class="admin-menu-link <?= strpos($current_uri, 'admin/dashboard.php') !== false ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="admin-menu-header">Manajemen Produk</li>
        <li>
            <a href="<?= BASE_URL ?>admin/products/index.php" class="admin-menu-link <?= strpos($current_uri, 'admin/products/') !== false && strpos($current_uri, 'filter=low_stock') === false ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i>
                <span>Data Produk</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>admin/categories/index.php" class="admin-menu-link <?= strpos($current_uri, 'admin/categories/') !== false ? 'active' : '' ?>">
                <i class="bi bi-tags"></i>
                <span>Kategori Produk</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>admin/products/index.php?filter=low_stock" class="admin-menu-link <?= strpos($current_uri, 'filter=low_stock') !== false ? 'active' : '' ?>">
                <i class="bi bi-exclamation-triangle"></i>
                <span>Stok Menipis</span>
                <?php if ($count_low_stock > 0): ?>
                    <span class="badge bg-danger ms-auto rounded-pill"><?= $count_low_stock ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li class="admin-menu-header">Transaksi & Pelanggan</li>
        <li>
            <a href="<?= BASE_URL ?>admin/transactions/index.php" class="admin-menu-link <?= strpos($current_uri, 'admin/transactions/') !== false ? 'active' : '' ?>">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Semua Transaksi</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>admin/payments/index.php" class="admin-menu-link <?= strpos($current_uri, 'admin/payments/') !== false ? 'active' : '' ?>">
                <i class="bi bi-credit-card-2-front"></i>
                <span>Verifikasi Pembayaran</span>
                <?php if ($count_pending_payments > 0): ?>
                    <span class="badge bg-warning text-dark ms-auto rounded-pill"><?= $count_pending_payments ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>admin/customers/index.php" class="admin-menu-link <?= strpos($current_uri, 'admin/customers/') !== false ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Data Pelanggan</span>
            </a>
        </li>

        <li class="admin-menu-header">Analitik & Laporan</li>
        <li>
            <a href="<?= BASE_URL ?>admin/reports/index.php" class="admin-menu-link <?= strpos($current_uri, 'admin/reports/') !== false ? 'active' : '' ?>">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Laporan Penjualan</span>
            </a>
        </li>

        <li class="admin-menu-header">Sistem & Toko</li>
        <li>
            <a href="<?= BASE_URL ?>index.php" target="_blank" class="admin-menu-link text-info">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Lihat Website Toko</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>auth/logout.php" class="admin-menu-link text-danger mt-3">
                <i class="bi bi-box-arrow-left"></i>
                <span>Keluar (Logout)</span>
            </a>
        </li>
    </ul>
</aside>
<div class="admin-main">
