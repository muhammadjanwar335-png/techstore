<?php
/**
 * TECHSTORE - Kumpulan Fungsi Helper & Keamanan Sistem
 */

require_once __DIR__ . '/database.php';

// Format Angka ke Rupiah Indonesia
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Format Tanggal Indonesia
function format_date($datetime, $with_time = false) {
    if (empty($datetime)) return '-';
    $timestamp = strtotime($datetime);
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    
    if ($with_time) {
        $jam = date('H:i', $timestamp);
        return "$tgl $bln $thn, $jam WIB";
    }
    return "$tgl $bln $thn";
}

// Sanitasi Input String untuk Mencegah XSS
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Proteksi Halaman Harus Login
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        set_flash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

// Proteksi Halaman Khusus Admin
function check_admin() {
    check_login();
    if ($_SESSION['user_role'] !== 'admin') {
        set_flash('danger', 'Akses ditolak! Halaman ini hanya diperuntukkan bagi Administrator.');
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Proteksi Halaman Khusus Customer
function check_customer() {
    check_login();
    if ($_SESSION['user_role'] !== 'customer') {
        set_flash('warning', 'Halaman ini khusus untuk akun Pelanggan/Customer.');
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
        exit;
    }
}

// Set Flash Message (Notifikasi Sesi)
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type, // success, danger, warning, info
        'message' => $message
    ];
}

// Tampilkan Flash Message
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        
        $icons = [
            'success' => 'bi-check-circle-fill',
            'danger'  => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            'info'    => 'bi-info-circle-fill'
        ];
        $icon = isset($icons[$type]) ? $icons[$type] : 'bi-bell-fill';
        
        return "
        <div class='alert alert-{$type} alert-dismissible fade show d-flex align-items-center shadow-sm my-3' role='alert'>
            <i class='bi {$icon} fs-5 me-2'></i>
            <div>{$message}</div>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
    return '';
}

// Mendapatkan Jumlah Item di Keranjang Belanja
function get_cart_count($pdo, $user_id) {
    if (!$user_id) return 0;
    try {
        $stmt = $pdo->prepare("
            SELECT SUM(cd.quantity) AS total_items
            FROM carts c
            JOIN cart_details cd ON c.id = cd.cart_id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return (int)($row['total_items'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

// Generator Kode Transaksi Unik
function generate_transaction_code() {
    return 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

// Generator Nomor Faktur (Invoice)
function generate_invoice_code() {
    return 'INV/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -4));
}

// Tampilan Badge Status Transaksi
function get_status_badge($status) {
    switch (strtolower($status)) {
        case 'pending':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>';
        case 'processing':
            return '<span class="badge bg-info text-dark"><i class="bi bi-gear-fill me-1"></i>Diproses</span>';
        case 'shipped':
            return '<span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Dikirim</span>';
        case 'completed':
            return '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>';
        case 'cancelled':
            return '<span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>Dibatalkan</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($status)) . '</span>';
    }
}

// Tampilan Badge Status Pembayaran
function get_payment_badge($status) {
    switch (strtolower($status)) {
        case 'paid':
            return '<span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Lunas (Paid)</span>';
        case 'pending':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Menunggu (Pending)</span>';
        case 'failed':
            return '<span class="badge bg-danger"><i class="bi bi-x-octagon-fill me-1"></i>Gagal / Ditolak (Failed)</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($status)) . '</span>';
    }
}

// Fungsi Upload File Gambar Aman
function upload_image($file, $targetDir, $prefix = 'img_') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['status' => false, 'message' => 'Parameter file tidak valid.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'Terjadi kesalahan saat upload file (Kode: ' . $file['error'] . ').'];
    }

    // Cek Ukuran File (Maksimal 2MB = 2 * 1024 * 1024 bytes)
    if ($file['size'] > 2097152) {
        return ['status' => false, 'message' => 'Ukuran file melebihi batas maksimal 2MB.'];
    }

    // Validasi Tipe MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!array_key_exists($mimeType, $allowedMimes)) {
        return ['status' => false, 'message' => 'Format file tidak didukung. Hanya JPG, PNG, dan WebP yang diizinkan.'];
    }

    $ext = $allowedMimes[$mimeType];
    $filename = $prefix . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $destination = rtrim($targetDir, '/') . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['status' => false, 'message' => 'Gagal memindahkan file ke direktori tujuan.'];
    }

    return ['status' => true, 'filename' => $filename];
}

// Fallback Gambar Produk Modern (Menggunakan Unsplash Tech Images atau SVG Grafis Elegan)
function get_product_image_url($image_filename, $category_name = 'Hardware') {
    $uploadPath = __DIR__ . '/../assets/uploads/products/' . $image_filename;
    if (!empty($image_filename) && file_exists($uploadPath)) {
        return BASE_URL . 'assets/uploads/products/' . htmlspecialchars($image_filename);
    }
    
    // Mapping gambar berkualitas tinggi untuk presentasi demo sesuai nama file default
    $curated = [
        'keyboard-1.jpg' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80',
        'keyboard-2.jpg' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600&auto=format&fit=crop&q=80',
        'keyboard-3.jpg' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=600&auto=format&fit=crop&q=80',
        'mouse-1.jpg'    => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=600&auto=format&fit=crop&q=80',
        'mouse-2.jpg'    => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&auto=format&fit=crop&q=80',
        'mouse-3.jpg'    => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&auto=format&fit=crop&q=80',
        'headset-1.jpg'  => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format&fit=crop&q=80',
        'headset-2.jpg'  => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
        'headset-3.jpg'  => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80',
        'monitor-1.jpg'  => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600&auto=format&fit=crop&q=80',
        'monitor-2.jpg'  => 'https://images.unsplash.com/photo-1585792180666-f7347c490ee2?w=600&auto=format&fit=crop&q=80',
        'monitor-3.jpg'  => 'https://images.unsplash.com/photo-1547082299-de196ea013d6?w=600&auto=format&fit=crop&q=80',
        'webcam-1.jpg'   => 'https://images.unsplash.com/photo-1587826080692-f439cd0b70da?w=600&auto=format&fit=crop&q=80',
        'webcam-2.jpg'   => 'https://images.unsplash.com/photo-1588702547919-26089e690ecc?w=600&auto=format&fit=crop&q=80',
        'webcam-3.jpg'   => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80',
        'flashdisk-1.jpg'=> 'https://images.unsplash.com/photo-1628191011993-9c8e87834bc6?w=600&auto=format&fit=crop&q=80',
        'flashdisk-2.jpg'=> 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=600&auto=format&fit=crop&q=80',
        'flashdisk-3.jpg'=> 'https://images.unsplash.com/photo-1563770660941-20978e870e26?w=600&auto=format&fit=crop&q=80',
        'storage-1.jpg'  => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=600&auto=format&fit=crop&q=80',
        'storage-2.jpg'  => 'https://images.unsplash.com/photo-1544652478-6653e09f18a2?w=600&auto=format&fit=crop&q=80',
        'storage-3.jpg'  => 'https://images.unsplash.com/photo-1531492746076-161ca9bcad58?w=600&auto=format&fit=crop&q=80',
        'acc-1.jpg'      => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80',
        'acc-2.jpg'      => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80',
        'acc-3.jpg'      => 'https://images.unsplash.com/photo-1527443195645-1133f7f28990?w=600&auto=format&fit=crop&q=80'
    ];

    if (isset($curated[$image_filename])) {
        return $curated[$image_filename];
    }

    // Default Tech Accessory Placeholder
    return 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&auto=format&fit=crop&q=80';
}
