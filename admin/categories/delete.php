<?php
/**
 * TECHSTORE - Hapus Kategori (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt_check->execute([$id]);
        $prod_count = (int)$stmt_check->fetchColumn();

        if ($prod_count > 0) {
            set_flash('warning', "Kategori tidak dapat dihapus karena masih menampung <strong>{$prod_count}</strong> produk aktif. Pindahkan produk terlebih dahulu!");
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Kategori produk berhasil dihapus.');
        }
    } catch (Exception $e) {
        set_flash('danger', 'Gagal menghapus kategori: ' . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . 'admin/categories/index.php');
exit;
