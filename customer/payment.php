<?php
/**
 * TECHSTORE - Halaman Pembayaran & Simulasi Transaksi
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

$trx_code = sanitize($_GET['trx'] ?? '');
if (empty($trx_code)) {
    header('Location: ' . BASE_URL . 'customer/history.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.*, p.id AS payment_id, p.payment_method, p.payment_status, p.payment_proof, p.amount,
               i.invoice_code, i.id AS invoice_id
        FROM transactions t
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        WHERE t.transaction_code = ? AND t.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$trx_code, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        set_flash('danger', 'Transaksi tidak ditemukan!');
        header('Location: ' . BASE_URL . 'customer/history.php');
        exit;
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// -------------------------------------------------------------
// POST Handler: Upload Bukti Transfer & Simulasi Pembayaran
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Upload Bukti Transfer (Transfer Bank)
    if ($action === 'upload_proof') {
        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/uploads/payments/';
            $result = upload_image($_FILES['proof_image'], $uploadDir, 'proof_' . $order['id'] . '_');

            if ($result['status']) {
                $filename = $result['filename'];
                $stmt_up = $pdo->prepare("
                    UPDATE payments 
                    SET payment_proof = ?, notes = 'Bukti transfer diunggah oleh pelanggan, menunggu verifikasi admin.'
                    WHERE id = ?
                ");
                $stmt_up->execute([$filename, $order['payment_id']]);

                set_flash('success', 'Bukti transfer berhasil diunggah! Admin akan segera memverifikasi pesanan Anda.');
                header('Location: ' . BASE_URL . 'customer/payment.php?trx=' . $trx_code);
                exit;
            } else {
                set_flash('danger', 'Gagal upload: ' . $result['message']);
            }
        } else {
            set_flash('danger', 'Silakan pilih file gambar bukti transfer terlebih dahulu!');
        }
    }

    // 2. Simulasi Bayar Instan (QRIS & E-Wallet)
    if ($action === 'simulate_success') {
        try {
            $pdo->beginTransaction();

            // Update status pembayaran menjadi 'paid'
            $stmt_p = $pdo->prepare("
                UPDATE payments 
                SET payment_status = 'paid', verified_at = NOW(), notes = 'Pembayaran terkonfirmasi otomatis via simulasi demonstrasi.'
                WHERE id = ?
            ");
            $stmt_p->execute([$order['payment_id']]);

            // Update status transaksi menjadi 'processing'
            $stmt_t = $pdo->prepare("UPDATE transactions SET status = 'processing' WHERE id = ?");
            $stmt_t->execute([$order['id']]);

            // Update status invoice menjadi 'paid'
            $stmt_i = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
            $stmt_i->execute([$order['invoice_id']]);

            $pdo->commit();

            set_flash('success', 'Simulasi pembayaran sukses! Status pesanan Anda kini sedang diproses oleh toko.');
            header('Location: ' . BASE_URL . 'customer/payment.php?trx=' . $trx_code);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash('danger', 'Gagal memproses simulasi: ' . $e->getMessage());
        }
    }
}

$page_title = 'Pembayaran Pesanan ' . $order['transaction_code'] . ' - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Tagihan Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="p-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Instruksi Pembayaran</span>
                            <h4 class="fw-bold mb-1">Kode Transaksi: <?= htmlspecialchars($order['transaction_code']) ?></h4>
                            <small class="text-secondary">Dibuat pada: <?= format_date($order['created_at'], true) ?></small>
                        </div>
                        <div class="text-md-end">
                            <div class="text-secondary small">Total Tagihan:</div>
                            <div class="fs-2 fw-extrabold text-info"><?= format_rupiah($order['grand_total']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Status Alert Banner -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold small text-secondary">Status Pembayaran:</span>
                            <?= get_payment_badge($order['payment_status']) ?>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold small text-secondary">Metode:</span>
                            <span class="badge bg-dark"><?= htmlspecialchars($order['payment_method']) ?></span>
                        </div>
                    </div>

                    <?php if ($order['payment_status'] === 'paid'): ?>
                        <!-- JIKA SUDAH LUNAS / TERVERIFIKASI -->
                        <div class="text-center py-4">
                            <div class="display-3 text-success mb-3"><i class="bi bi-patch-check-fill"></i></div>
                            <h3 class="fw-bold text-success mb-2">Pembayaran Berhasil Diterima!</h3>
                            <p class="text-muted mb-4">
                                Terima kasih, transaksi Anda telah terverifikasi. Pesanan sedang dipersiapkan oleh tim logistik TechStore.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $order['invoice_code'] ?>" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-receipt me-2"></i>Lihat & Cetak Faktur (Invoice)
                                </a>
                                <a href="<?= BASE_URL ?>invoice/struk.php?code=<?= $order['invoice_code'] ?>" target="_blank" class="btn btn-outline-dark px-3 py-2">
                                    <i class="bi bi-printer me-2"></i>Cetak Struk Kasir
                                </a>
                                <a href="<?= BASE_URL ?>customer/history.php" class="btn btn-light border px-4 py-2">
                                    <i class="bi bi-arrow-left me-2"></i>Riwayat Pesanan
                                </a>
                            </div>
                        </div>

                    <?php elseif ($order['payment_status'] === 'failed'): ?>
                        <!-- JIKA DITOLAK / GAGAL -->
                        <div class="alert alert-danger p-4 text-center rounded-3 mb-4">
                            <i class="bi bi-x-octagon-fill display-4 text-danger mb-2 d-block"></i>
                            <h5 class="fw-bold">Pembayaran Ditolak atau Dibatalkan</h5>
                            <p class="small text-muted mb-3">Catatan Admin: <?= htmlspecialchars($order['notes'] ?? 'Bukti transfer tidak valid atau belum masuk ke mutasi rekening.') ?></p>
                            <a href="<?= BASE_URL ?>customer/history.php" class="btn btn-outline-danger btn-sm">Kembali ke Riwayat Pesanan</a>
                        </div>

                    <?php else: ?>
                        <!-- JIKA MASIH PENDING -->

                        <!-- Countdown Timer Simulasi -->
                        <div class="alert alert-warning border-0 d-flex align-items-center justify-content-between p-3 mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-hourglass-top fs-4 text-warning"></i>
                                <div>
                                    <div class="fw-bold small">Batas Waktu Pembayaran:</div>
                                    <small class="text-muted">Selesaikan pembayaran sebelum batas waktu berakhir</small>
                                </div>
                            </div>
                            <div class="fw-bold fs-5 text-danger font-monospace" id="countdownTimer">23:59:45</div>
                        </div>

                        <!-- 1. JIKA METODE: TRANSFER BANK -->
                        <?php if (strpos($order['payment_method'], 'Transfer Bank') !== false): ?>
                            <div class="border rounded-4 p-4 mb-4 bg-light">
                                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-bank text-primary me-2"></i>Nomor Rekening Tujuan:</h6>
                                
                                <div class="card p-3 mb-3 border bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-primary mb-1">Bank BCA</span>
                                            <div class="fs-4 fw-bold font-monospace text-dark">8830 1234 5678</div>
                                            <small class="text-muted">Atas Nama: <strong>PT TECHSTORE INDONESIA</strong></small>
                                        </div>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('883012345678'); alert('Nomor rekening BCA berhasil disalin!');">
                                            <i class="bi bi-clipboard me-1"></i>Salin
                                        </button>
                                    </div>
                                </div>

                                <div class="card p-3 border bg-white mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary mb-1">Bank Mandiri</span>
                                            <div class="fs-4 fw-bold font-monospace text-dark">127 000 987 6543</div>
                                            <small class="text-muted">Atas Nama: <strong>PT TECHSTORE INDONESIA</strong></small>
                                        </div>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('1270009876543'); alert('Nomor rekening Mandiri berhasil disalin!');">
                                            <i class="bi bi-clipboard me-1"></i>Salin
                                        </button>
                                    </div>
                                </div>

                                <!-- Form Upload Bukti Transfer -->
                                <h6 class="fw-bold mt-4 mb-2 text-dark"><i class="bi bi-upload text-primary me-2"></i>Upload Bukti Transfer Bank:</h6>
                                <p class="small text-muted mb-3">Format JPG, PNG, atau WebP (Maks. 2MB). Bukti ini akan diverifikasi oleh Admin.</p>

                                <?php if (!empty($order['payment_proof'])): ?>
                                    <div class="alert alert-info d-flex align-items-center small py-2 mb-3">
                                        <i class="bi bi-check-circle-fill text-info fs-5 me-2"></i>
                                        <div>Bukti transfer sudah pernah diunggah (<strong><?= htmlspecialchars($order['payment_proof']) ?></strong>). Anda dapat mengunggah ulang jika bukti salah.</div>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="upload_proof">
                                    <div class="mb-3">
                                        <input type="file" name="proof_image" class="form-control input-image-preview" data-preview-target="proofPreview" accept="image/jpeg,image/png,image/webp" required>
                                    </div>
                                    <div class="mb-3 text-center">
                                        <img id="proofPreview" src="#" alt="Preview Bukti" class="img-fluid rounded-3 border d-none" style="max-height: 250px;">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Kirim Bukti Pembayaran
                                    </button>
                                </form>
                            </div>

                        <!-- 2. JIKA METODE: QRIS -->
                        <?php elseif ($order['payment_method'] === 'QRIS'): ?>
                            <div class="text-center border rounded-4 p-4 mb-4 bg-light">
                                <span class="badge bg-success px-3 py-2 rounded-pill mb-2">QRIS Standar Nasional</span>
                                <h5 class="fw-bold mb-1">Pindai Kode QRIS di Bawah Ini</h5>
                                <p class="small text-muted mb-4">Gunakan aplikasi BCA Mobile, GoPay, OVO, DANA, Livin, atau ShopeePay</p>

                                <!-- Simulated QR Code Visual -->
                                <div class="bg-white p-3 rounded-4 d-inline-block border shadow-sm mb-3">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=TECHSTORE-PAY-<?= urlencode($order['transaction_code']) ?>-AMT-<?= (int)$order['grand_total'] ?>" alt="QRIS Code" class="img-fluid" style="width: 220px; height: 220px;">
                                    <div class="fw-bold text-dark mt-2 small">NMID: ID1020304050607</div>
                                </div>

                                <div class="alert alert-info border-0 small text-start mb-4">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    <strong>Mode Simulasi Demonstrasi Perkuliahan:</strong><br>
                                    Tekan tombol konfirmasi simulasi di bawah untuk menguji alur sistem saat pembayaran dinyatakan berhasil secara instan.
                                </div>

                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="simulate_success">
                                    <button type="submit" class="btn btn-success btn-lg px-4 py-3 fw-bold w-100 shadow-sm">
                                        <i class="bi bi-lightning-charge-fill me-2"></i>Simulasi Bayar Sekarang (Instan Lunas)
                                    </button>
                                </form>
                            </div>

                        <!-- 3. JIKA METODE: E-WALLET (DANA, OVO, GoPay) -->
                        <?php elseif (in_array($order['payment_method'], ['DANA', 'OVO', 'GoPay'])): ?>
                            <div class="border rounded-4 p-4 mb-4 bg-light text-center">
                                <h5 class="fw-bold mb-2">Pembayaran via <?= htmlspecialchars($order['payment_method']) ?></h5>
                                <p class="small text-muted mb-4">Nomor virtual / nomor terdaftar akun e-wallet Anda</p>

                                <div class="card p-3 border bg-white mb-4 text-start">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">Nomor Akun Merchant:</span>
                                        <strong class="font-monospace text-primary">0812-3456-7890 (TechStore)</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Total Tagihan:</span>
                                        <strong class="text-dark fs-5"><?= format_rupiah($order['grand_total']) ?></strong>
                                    </div>
                                </div>

                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="simulate_success">
                                    <button type="submit" class="btn btn-primary btn-lg px-4 py-3 fw-bold w-100 shadow-sm">
                                        <i class="bi bi-check-circle-fill me-2"></i>Simulasi Bayar via <?= htmlspecialchars($order['payment_method']) ?>
                                    </button>
                                </form>
                            </div>

                        <!-- 4. JIKA METODE: COD (Cash On Delivery) -->
                        <?php else: ?>
                            <div class="border rounded-4 p-4 mb-4 bg-light text-center">
                                <i class="bi bi-cash-coin display-3 text-warning mb-2 d-inline-block"></i>
                                <h5 class="fw-bold mb-2">Metode COD (Cash on Delivery)</h5>
                                <p class="text-muted small mb-4">
                                    Pesanan Anda telah dicatat dan akan segera diproses oleh tim kami. Siapkan uang pas sebesar <strong><?= format_rupiah($order['grand_total']) ?></strong> saat kurir mengantarkan barang ke alamat Anda.
                                </p>
                                <a href="<?= BASE_URL ?>customer/history.php" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-box-seam me-2"></i>Pantau Status Pesanan
                                </a>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>

                    <!-- Action Links -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="<?= BASE_URL ?>customer/history.php" class="text-decoration-none text-secondary small">
                            <i class="bi bi-arrow-left me-1"></i>Ke Riwayat Pesanan
                        </a>
                        <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $order['invoice_code'] ?>" class="text-decoration-none text-primary small fw-semibold">
                            Lihat Draft Invoice &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simulasi hitung mundur timer pembayaran
let seconds = 86390;
const countdownEl = document.getElementById('countdownTimer');
if (countdownEl) {
    setInterval(() => {
        if (seconds > 0) {
            seconds--;
            const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            countdownEl.textContent = `${h}:${m}:${s}`;
        }
    }, 1000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
