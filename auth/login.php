<?php
/**
 * TECHSTORE - Halaman Login
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Jika sudah login, langsung alihkan sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . 'index.php');
    }
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerasi ID sesi untuk mencegah session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                set_flash('success', 'Selamat datang kembali, <strong>' . htmlspecialchars($user['name']) . '</strong>!');

                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . 'admin/dashboard.php');
                } else {
                    header('Location: ' . BASE_URL . 'index.php');
                }
                exit;
            } else {
                $error = 'Email atau kata sandi yang Anda masukkan salah!';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

$page_title = 'Masuk ke Akun - TechStore';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            max-width: 950px;
            width: 100%;
        }
        .auth-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 45px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }
        .auth-banner::before {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.3) 0%, rgba(37, 99, 235, 0) 70%);
            bottom: -50px;
            right: -50px;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="row g-0">
        <!-- Kolom Kiri: Branding & Demo Info -->
        <div class="col-lg-5 auth-banner d-none d-lg-flex">
            <div>
                <a href="<?= BASE_URL ?>index.php" class="navbar-brand-tech text-white fs-3 mb-4 d-inline-block">
                    <i class="bi bi-cpu-fill text-primary"></i> TECH<span class="text-primary">STORE</span>
                </a>
                <h4 class="fw-bold text-white mb-2">Sistem Informasi Penjualan Modern</h4>
                <p class="text-secondary small leading-relaxed">
                    Akses panel admin untuk mengelola katalog, inventaris stok, dan laporan keuangan, atau belanja aksesoris komputer berkualitas.
                </p>
            </div>

            <!-- Box Demo Login Cepat untuk Presentasi Dosen -->
            <div class="bg-dark bg-opacity-50 p-3 rounded-4 border border-secondary border-opacity-50 mt-4">
                <div class="d-flex align-items-center gap-2 text-warning fw-bold small mb-2">
                    <i class="bi bi-lightning-charge-fill"></i> AKUN DEMO PRESENTASI
                </div>
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-sm btn-outline-info text-start d-flex justify-content-between align-items-center" onclick="fillDemo('admin@techstore.com', 'admin123')">
                        <span><i class="bi bi-shield-check me-1"></i> <strong>Admin</strong> (admin123)</span>
                        <i class="bi bi-arrow-right-short"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light text-start d-flex justify-content-between align-items-center" onclick="fillDemo('customer@techstore.com', 'customer123')">
                        <span><i class="bi bi-person me-1"></i> <strong>Customer</strong> (customer123)</span>
                        <i class="bi bi-arrow-right-short"></i>
                    </button>
                </div>
            </div>

            <div class="text-secondary small mt-4">
                &copy; <?= date('Y') ?> TechStore Inc. All rights reserved.
            </div>
        </div>

        <!-- Kolom Kanan: Form Login -->
        <div class="col-lg-7 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Masuk ke Akun</h3>
                    <p class="text-muted small mb-0">Silakan masukkan email dan kata sandi Anda</p>
                </div>
                <a href="<?= BASE_URL ?>index.php" class="btn btn-sm btn-light border text-secondary" title="Kembali ke Beranda">
                    <i class="bi bi-house-door-fill"></i>
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center small py-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <?= display_flash() ?>

            <!-- Demo buttons untuk tampilan mobile -->
            <div class="d-lg-none mb-3 p-3 bg-light rounded-3 border">
                <div class="small fw-bold text-muted mb-2"><i class="bi bi-lightning-charge-fill text-warning"></i> Akun Demo:</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="fillDemo('admin@techstore.com', 'admin123')">Admin</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" onclick="fillDemo('customer@techstore.com', 'customer123')">Customer</button>
                </div>
            </div>

            <form method="POST" action="" class="needs-validation">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-secondary">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" id="emailInput" class="form-control bg-light border-start-0" placeholder="nama@email.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small text-secondary mb-0">Kata Sandi</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="passwordInput" class="form-control bg-light border-start-0 border-end-0" placeholder="••••••••" required>
                        <button class="btn btn-light border border-start-0 text-muted" type="button" id="togglePasswordBtn" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Sekarang
                </button>
            </form>

            <div class="mt-4 text-center small text-muted">
                Belum memiliki akun TechStore? <a href="<?= BASE_URL ?>auth/register.php" class="fw-bold text-primary">Daftar Akun Baru</a>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = pass;
}

function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
