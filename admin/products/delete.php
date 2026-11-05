<?php
/**
 * TECHSTORE - Hapus Produk (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $stmt_find = $pdo->prepare("SELECT name, image FROM products WHERE id = ?");
        $stmt_find->execute([$id]);
        $prod = $stmt_find->fetch();

        if ($prod) {
            // Hapus file fisik gambar jika tersimpan di uploads dan bukan default
            $imagePath = __DIR__ . '/../../assets/uploads/products/' . $prod['image'];
            if (!empty($prod['image']) && $prod['image'] !== 'default.jpg' && file_exists($imagePath)) {
                @unlink($imagePath);
            }

            $stmt_del = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt_del->execute([$id]);

            set_flash('success', "Produk <strong>{$prod['name']}</strong> berhasil dihapus dari sistem.");
        } else {
            set_flash('danger', 'Produk tidak ditemukan.');
        }
    } catch (Exception $e) {
        set_flash('danger', 'Gagal menghapus produk: ' . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . 'admin/products/index.php');
exit;
