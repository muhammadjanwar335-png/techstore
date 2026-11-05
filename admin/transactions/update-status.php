<?php
/**
 * TECHSTORE - Handler Ubah Status Transaksi & Kembalikan Stok jika Batal (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = (int)($_POST['transaction_id'] ?? 0);
    $new_status = sanitize($_POST['status'] ?? '');

    $valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    if ($transaction_id > 0 && in_array($new_status, $valid_statuses)) {
        try {
            $pdo->beginTransaction();

            // Ambil status transaksi saat ini
            $stmt_cur = $pdo->prepare("SELECT status FROM transactions WHERE id = ? FOR UPDATE");
            $stmt_cur->execute([$transaction_id]);
            $old_status = $stmt_cur->fetchColumn();

            if ($old_status !== $new_status) {
                // Ambil daftar detail produk pesanan
                $stmt_items = $pdo->prepare("SELECT product_id, quantity FROM transaction_details WHERE transaction_id = ?");
                $stmt_items->execute([$transaction_id]);
                $items = $stmt_items->fetchAll();

                // 1. Jika status baru adalah 'cancelled' dan status lama bukan 'cancelled' -> KEMBALIKAN STOK BARANG
                if ($new_status === 'cancelled' && $old_status !== 'cancelled') {
                    $stmt_restore = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                    foreach ($items as $it) {
                        if ($it['product_id']) {
                            $stmt_restore->execute([$it['quantity'], $it['product_id']]);
                        }
                    }
                }

                // 2. Jika status lama adalah 'cancelled' dan diaktifkan kembali -> KURANGI KEMBALI STOK
                if ($old_status === 'cancelled' && $new_status !== 'cancelled') {
                    $stmt_reduce = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    foreach ($items as $it) {
                        if ($it['product_id']) {
                            $stmt_reduce->execute([$it['quantity'], $it['product_id']]);
                        }
                    }
                }

                // 3. Perbarui status tabel transactions
                $stmt_up = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
                $stmt_up->execute([$new_status, $transaction_id]);

                // 4. Jika selesai (completed), pastikan invoice tercatat paid jika belum
                if ($new_status === 'completed') {
                    $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE transaction_id = ?")->execute([$transaction_id]);
                } elseif ($new_status === 'cancelled') {
                    $pdo->prepare("UPDATE invoices SET status = 'cancelled' WHERE transaction_id = ?")->execute([$transaction_id]);
                }

                $pdo->commit();
                set_flash('success', "Status transaksi berhasil diperbarui menjadi <strong>" . ucfirst($new_status) . "</strong>.");
            } else {
                $pdo->rollBack();
                set_flash('info', 'Status transaksi tidak mengalami perubahan.');
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash('danger', 'Gagal mengubah status: ' . $e->getMessage());
        }
    } else {
        set_flash('danger', 'Data atau status tidak valid.');
    }

    header('Location: ' . BASE_URL . 'admin/transactions/detail.php?id=' . $transaction_id);
    exit;
}

header('Location: ' . BASE_URL . 'admin/transactions/index.php');
exit;
