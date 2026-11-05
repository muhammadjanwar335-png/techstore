<?php
/**
 * TECHSTORE - Verifikasi Pembayaran Handler (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = (int)($_POST['payment_id'] ?? 0);
    $transaction_id = (int)($_POST['transaction_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    $admin_id = $_SESSION['user_id'];

    $valid = ['paid', 'pending', 'failed'];

    if ($payment_id > 0 && in_array($status, $valid)) {
        try {
            $pdo->beginTransaction();

            $verified_at = ($status === 'paid') ? date('Y-m-d H:i:s') : null;
            $verified_by = ($status === 'paid') ? $admin_id : null;

            // 1. Update tabel payments
            $stmt_p = $pdo->prepare("
                UPDATE payments 
                SET payment_status = ?, verified_by = ?, verified_at = ?, notes = ?
                WHERE id = ?
            ");
            $stmt_p->execute([$status, $verified_by, $verified_at, $notes, $payment_id]);

            // 2. Jika disetujui (paid), update status transaksi menjadi 'processing' dan invoice menjadi 'paid'
            if ($status === 'paid') {
                $stmt_cur_tr = $pdo->prepare("SELECT status FROM transactions WHERE id = ?");
                $stmt_cur_tr->execute([$transaction_id]);
                $cur_tr_status = $stmt_cur_tr->fetchColumn();

                if ($cur_tr_status === 'pending') {
                    $pdo->prepare("UPDATE transactions SET status = 'processing' WHERE id = ?")->execute([$transaction_id]);
                }
                $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE transaction_id = ?")->execute([$transaction_id]);

                set_flash('success', 'Pembayaran berhasil diverifikasi dan disetujui (LUNAS). Status pesanan telah diperbarui!');
            } elseif ($status === 'failed') {
                $pdo->prepare("UPDATE invoices SET status = 'cancelled' WHERE transaction_id = ?")->execute([$transaction_id]);
                set_flash('warning', 'Pembayaran ditolak (FAILED). Pesanan belum dapat diproses.');
            } else {
                set_flash('info', 'Status pembayaran dikembalikan ke PENDING.');
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash('danger', 'Gagal memverifikasi pembayaran: ' . $e->getMessage());
        }
    } else {
        set_flash('danger', 'Data verifikasi tidak valid.');
    }
}

header('Location: ' . BASE_URL . 'admin/payments/index.php');
exit;
