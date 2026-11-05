<?php
/**
 * TECHSTORE - Halaman Registrasi Customer
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';
$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($address)) {
        $error = 'Semua kolom formulir wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal terdiri dari 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi kata sandi tidak cocok!';
    } else {
        try {
            // Cek apakah email sudah terdaftar
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email ini sudah terdaftar. Silakan gunakan email lain atau login!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, phone, address)
                    VALUES (?, ?, ?, 'customer', ?, ?)
                ");
                $insert->execute([$name, $email, $hashed_password, $phone, $address]);

                set_flash('success', 'Pendaftaran akun berhasil! Silakan masuk dengan email dan kata sandi Anda.');
                header('Location: ' . BASE_URL . 'auth/login.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

$page_title = 'Daftar Akun Baru - TechStore';
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
            padding: 30px 15px;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            max-width: 650px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="auth-card p-4 p-md-5">
    <div class="text-center mb-4">
        <a href="<?= BASE_URL ?>index.php" class="navbar-brand-tech text-dark fs-3 mb-2 d-inline-block">
            <i class="bi bi-cpu-fill text-primary"></i> TECH<span class="text-primary">STORE</span>
        </a>
        <h3 class="fw-bold mb-1">Pendaftaran Akun Baru</h3>
        <p class="text-muted small">Lengkapi data diri Anda untuk mulai berbelanja di TechStore</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center small py-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control bg-light" placeholder="Contoh: Dimas Pratama" value="<?= htmlspecialchars($name) ?>" required>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control bg-light" placeholder="nama@email.com" value="<?= htmlspecialchars($email) ?>" required>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control bg-light" placeholder="Minimal 6 karakter" required>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary">Konfirmasi Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-check"></i></span>
                    <input type="password" name="confirm_password" class="form-control bg-light" placeholder="Ulangi kata sandi" required>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold small text-secondary">No. WhatsApp / Telepon</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-phone"></i></span>
                    <input type="text" name="phone" class="form-control bg-light" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($phone) ?>" required>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold small text-secondary">Alamat Lengkap Pengiriman</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-geo-alt"></i></span>
                    <textarea name="address" class="form-control bg-light" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kecamatan, kota/kabupaten" required><?= htmlspecialchars($address) ?></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mt-4 fw-bold shadow-sm">
            <i class="bi bi-person-check-fill me-2"></i>Daftar Sekarang
        </button>
    </form>

    <div class="mt-4 text-center small text-muted">
        Sudah memiliki akun? <a href="<?= BASE_URL ?>auth/login.php" class="fw-bold text-primary">Masuk di sini</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
