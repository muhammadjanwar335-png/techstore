<?php
/**
 * TECHSTORE - Logout Script
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Hapus seluruh variabel sesi
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mulai sesi baru untuk pesan flash
session_start();
set_flash('info', 'Anda telah berhasil keluar dari sistem. Sampai jumpa kembali!');
header('Location: ' . BASE_URL . 'auth/login.php');
exit;
