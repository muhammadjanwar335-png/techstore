<?php
/**
 * TECHSTORE - Konfigurasi Database & Pengaturan Global
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set Zona Waktu Indonesia Barat
date_default_timezone_set('Asia/Jakarta');

// Parameter Koneksi Database MySQL
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'techstore_db';

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Tampilan ramah jika database belum di-create atau MySQL belum dinyalakan
    die('
        <div style="font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 25px; border-radius: 10px; border-left: 5px solid #dc3545; background: #fff5f5; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
            <h2 style="color: #dc3545; margin-top: 0;">Koneksi Database Gagal!</h2>
            <p style="color: #4b5563;">Sistem tidak dapat terhubung ke MySQL. Pastikan service MySQL di XAMPP Control Panel sudah berstatus <strong>Running</strong>.</p>
            <p style="background: #eee; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 13px;">' . htmlspecialchars($e->getMessage()) . '</p>
            <p style="margin-bottom: 0;">Silakan jalankan file installer otomatis melalui: <br>
                <a href="' . (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/techstore/setup.php" style="display: inline-block; margin-top: 10px; padding: 10px 18px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    Jalankan Setup Database Otomatis
                </a>
            </p>
        </div>
    ');
}

// Deteksi Otomatis Base URL Aplikasi
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Menentukan root path folder techstore
$scriptPath = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) : '';
// Mencari posisi folder techstore dalam path URL
$pos = strpos($scriptPath, '/techstore');
if ($pos !== false) {
    $baseUrlPath = substr($scriptPath, 0, $pos + 10);
} else {
    // Jika dijalankan langsung dari root (misal php -S localhost:8000)
    $baseUrlPath = '';
}

define('BASE_URL', rtrim($protocol . $host . $baseUrlPath, '/') . '/');
define('APP_NAME', 'TECHSTORE');
define('APP_TAGLINE', 'Sistem Informasi Penjualan Accessories Komputer & Laptop');
