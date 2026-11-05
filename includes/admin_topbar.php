<?php
/**
 * TECHSTORE - Admin Topbar Template
 */
?>
<header class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('d-block');">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="text-secondary small d-none d-sm-block">
            <i class="bi bi-calendar3 me-1 text-primary"></i> <?= format_date(date('Y-m-d')) ?> |
            <i class="bi bi-clock me-1 text-primary"></i> <span id="liveClock"><?= date('H:i') ?></span> WIB
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Quick Status Link -->
        <a href="<?= BASE_URL ?>admin/payments/index.php" class="btn btn-sm btn-light border position-relative" title="Pembayaran Menunggu Verifikasi">
            <i class="bi bi-bell-fill text-secondary"></i>
            <?php if ($count_pending_payments > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">Notifikasi Baru</span>
                </span>
            <?php endif; ?>
        </a>

        <!-- Admin Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: bold; font-size: 12px;">
                    A
                </div>
                <span class="fw-semibold small d-none d-md-inline"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li class="px-3 py-2 small text-muted border-bottom">
                    <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong><br>
                    <span><?= htmlspecialchars($_SESSION['user_email']) ?></span>
                </li>
                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>index.php" target="_blank"><i class="bi bi-shop me-2"></i>Kunjungi Toko</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</header>
<main class="admin-content">
    <?= display_flash() ?>
