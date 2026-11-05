<?php
/**
 * TECHSTORE - Automated Database Setup & Installer
 * Menjalankan pembuatan database techstore_db dan import data demo secara otomatis.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'techstore_db';
$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (php_sapi_name() === 'cli')) {
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $sqlFile = __DIR__ . '/database.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("File database.sql tidak ditemukan di: " . $sqlFile);
        }

        $sql = file_get_contents($sqlFile);
        
        // Execute multi-query
        $pdo->exec($sql);
        
        $status = 'success';
        $message = "Database <strong>techstore_db</strong> dan seluruh tabel serta data demo berhasil di-import!";
        
        if (php_sapi_name() === 'cli') {
            echo "SUCCESS: Database techstore_db and demo data successfully imported!\n";
            exit(0);
        }
    } catch (Exception $e) {
        $status = 'danger';
        $message = "Gagal melakukan setup database: " . $e->getMessage();
        if (php_sapi_name() === 'cli') {
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
        }
        .setup-card {
            background: #ffffff;
            color: #1e293b;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 580px;
            width: 100%;
            overflow: hidden;
        }
        .setup-header {
            background: #0f172a;
            color: #ffffff;
            padding: 30px;
            text-align: center;
            border-bottom: 3px solid #2563eb;
        }
    </style>
</head>
<body>
    <div class="container p-3">
        <div class="setup-card mx-auto">
            <div class="setup-header">
                <i class="bi bi-cpu text-primary display-4 mb-2 d-inline-block"></i>
                <h3 class="fw-bold mb-1">TECHSTORE INSTALLER</h3>
                <p class="text-secondary mb-0 small">Sistem Informasi Penjualan Accessories Komputer & Laptop</p>
            </div>
            <div class="p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $status ?> d-flex align-items-center mb-4" role="alert">
                        <i class="bi <?= $status === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' ?> fs-4 me-3"></i>
                        <div><?= $message ?></div>
                    </div>
                    <?php if ($status === 'success'): ?>
                        <div class="card bg-light border-0 mb-4 p-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-check-fill text-primary me-2"></i>Akun Demo Siap Digunakan:</h6>
                            <ul class="list-unstyled mb-0 small text-muted">
                                <li><strong>Admin:</strong> admin@techstore.com | Password: <code>admin123</code></li>
                                <li><strong>Customer:</strong> customer@techstore.com | Password: <code>customer123</code></li>
                            </ul>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary py-2 fw-semibold">
                                <i class="bi bi-house-door me-2"></i>Buka Halaman Utama Website
                            </a>
                            <a href="auth/login.php" class="btn btn-outline-dark py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login Admin / Customer
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info border-0 d-flex mb-4">
                        <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
                        <div>
                            Klik tombol di bawah untuk menginisialisasi database <strong>techstore_db</strong>, membuat 9 tabel relasional, serta mengisi 24 produk contoh dan transaksi demo.
                        </div>
                    </div>

                    <div class="card bg-light border-0 mb-4 p-3 small">
                        <div class="fw-bold text-secondary mb-2 text-uppercase">Konfigurasi Default XAMPP:</div>
                        <div>Host: <code>127.0.0.1</code></div>
                        <div>User: <code>root</code></div>
                        <div>Password: <em>(kosong)</em></div>
                        <div>Database: <code>techstore_db</code></div>
                    </div>

                    <form method="POST">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold fs-6">
                            <i class="bi bi-database-fill-gear me-2"></i>Inisialisasi Database Sekarang
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="bg-light px-4 py-3 text-center border-top small text-muted">
                Tugas Proyek Perkuliahan Full Stack Web Developer &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>
</body>
</html>
