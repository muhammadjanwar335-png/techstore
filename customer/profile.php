<?php
/**
 * TECHSTORE - Halaman Profil & Pengaturan Akun Pelanggan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_customer();
$user_id = $_SESSION['user_id'];
$error = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($phone) || empty($address)) {
        $error = 'Nama, nomor telepon, dan alamat lengkap wajib diisi!';
    } else {
        try {
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error = 'Kata sandi baru minimal 6 karakter!';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Konfirmasi kata sandi baru tidak cocok!';
                } else {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt_up = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                    $stmt_up->execute([$name, $phone, $address, $hashed, $user_id]);
                    $_SESSION['user_name'] = $name;
                    set_flash('success', 'Profil dan kata sandi berhasil diperbarui!');
                    header('Location: ' . BASE_URL . 'customer/profile.php');
                    exit;
                }
            } else {
                $stmt_up = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
                $stmt_up->execute([$name, $phone, $address, $user_id]);
                $_SESSION['user_name'] = $name;
                set_flash('success', 'Profil berhasil diperbarui!');
                header('Location: ' . BASE_URL . 'customer/profile.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }
}

$page_title = 'Pengaturan Profil - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none text-muted">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>customer/dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pengaturan Profil</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.5rem; font-weight: bold;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                        <div class="text-muted small"><?= htmlspecialchars($user['email']) ?> &bull; Akun Pelanggan</div>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center small py-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">Informasi Pribadi & Kontak</h6>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Alamat Email (Tidak Dapat Diubah)</label>
                        <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Alamat Pengiriman Utama</label>
                        <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">Ganti Kata Sandi (Kosongkan jika tidak diubah)</h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Kata Sandi Baru</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi kata sandi">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>customer/dashboard.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
