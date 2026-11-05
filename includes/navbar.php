<?php
/**
 * TECHSTORE - Navbar Customer Template
 */
// Ambil kategori untuk menu dropdown
try {
    $stmt_nav_cats = $pdo->query("SELECT id, name, slug, icon FROM categories ORDER BY name ASC LIMIT 8");
    $nav_categories = $stmt_nav_cats->fetchAll();
} catch (Exception $e) {
    $nav_categories = [];
}
?>
<header class="navbar-tech sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark py-2">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand navbar-brand-tech" href="<?= BASE_URL ?>index.php">
                <i class="bi bi-cpu-fill text-primary"></i>
                TECH<span>STORE</span>
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links & Controls -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Search Form -->
                <form class="d-flex mx-lg-4 my-2 my-lg-0 flex-grow-1 max-w-md" action="<?= BASE_URL ?>customer/products.php" method="GET" style="max-width: 420px;">
                    <div class="input-group">
                        <input class="form-control form-control-sm bg-dark text-white border-secondary placeholder-gray-400" type="search" name="q" placeholder="Cari keyboard, mouse, SSD..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" aria-label="Search">
                        <button class="btn btn-primary btn-sm px-3" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-tech-link" href="<?= BASE_URL ?>index.php">
                            <i class="bi bi-house-door me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-tech-link" href="<?= BASE_URL ?>customer/products.php">
                            <i class="bi bi-grid-fill me-1"></i>Katalog
                        </a>
                    </li>
                    <!-- Dropdown Kategori -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-tech-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-tags me-1"></i>Kategori
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-secondary">
                            <?php foreach ($nav_categories as $cat): ?>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="<?= BASE_URL ?>customer/products.php?category=<?= $cat['id'] ?>">
                                        <i class="bi <?= htmlspecialchars($cat['icon']) ?> me-2 text-primary"></i>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li>
                                <a class="dropdown-item text-primary fw-semibold" href="<?= BASE_URL ?>customer/products.php">
                                    Lihat Semua Kategori &rarr;
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Keranjang Belanja -->
                    <li class="nav-item me-lg-2">
                        <a class="nav-link nav-tech-link position-relative px-3" href="<?= BASE_URL ?>customer/cart.php" title="Keranjang Belanja">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="d-lg-none ms-2">Keranjang</span>
                            <?php if ($cart_count > 0): ?>
                                <span class="badge-cart-count"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- User Account / Login State -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="btn btn-outline-info btn-sm dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <span><?= htmlspecialchars($_SESSION['user_name']) ?> (Admin)</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg">
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle fs-6"></i>
                                    <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg">
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>customer/dashboard.php"><i class="bi bi-speedometer me-2"></i>Dashboard Saya</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>customer/history.php"><i class="bi bi-bag-check me-2"></i>Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>customer/profile.php"><i class="bi bi-person-gear me-2"></i>Pengaturan Profil</a></li>
                                    <li><hr class="dropdown-divider border-secondary"></li>
                                    <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item d-flex gap-2 ms-lg-2">
                            <a class="btn btn-outline-light btn-sm px-3" href="<?= BASE_URL ?>auth/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                            </a>
                            <a class="btn btn-primary btn-sm px-3" href="<?= BASE_URL ?>auth/register.php">
                                <i class="bi bi-person-plus me-1"></i>Daftar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main>
    <div class="container">
        <?= display_flash() ?>
    </div>
