-- ============================================================
-- TECHSTORE - SISTEM INFORMASI PENJUALAN ACCESSORIES KOMPUTER & LAPTOP
-- Database SQL Dump untuk Tugas Proyek Perkuliahan
-- Nama Database: techstore_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS `techstore_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `techstore_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `transaction_details`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `cart_details`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Tabel: users
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
  `phone` VARCHAR(25) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: categories (Minimal 8 Kategori Sesuai Spesifikasi)
-- ------------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-tag',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: products (Minimal 20 Data Produk Contoh)
-- ------------------------------------------------------------
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `weight` INT NOT NULL DEFAULT 500 COMMENT 'Berat dalam gram',
  `image` VARCHAR(255) DEFAULT 'default.jpg',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: carts
-- ------------------------------------------------------------
CREATE TABLE `carts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: cart_details
-- ------------------------------------------------------------
CREATE TABLE `cart_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_cd_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cd_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: transactions
-- ------------------------------------------------------------
CREATE TABLE `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transaction_code` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `shipping_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `recipient_name` VARCHAR(100) NOT NULL,
  `recipient_phone` VARCHAR(25) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_courier` VARCHAR(50) DEFAULT 'JNE Regular',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_trans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: transaction_details
-- ------------------------------------------------------------
CREATE TABLE `transaction_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `quantity` INT NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  CONSTRAINT `fk_td_trans` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_td_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: payments
-- ------------------------------------------------------------
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` INT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `verified_by` INT DEFAULT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  CONSTRAINT `fk_pay_trans` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_admin` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: invoices
-- ------------------------------------------------------------
CREATE TABLE `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_code` VARCHAR(50) NOT NULL UNIQUE,
  `transaction_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('unpaid', 'paid', 'cancelled') NOT NULL DEFAULT 'unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_inv_trans` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA DEMO AWAL (SEEDED DATA)
-- ============================================================

-- 1. Demo Users (Admin & Customer dengan password bcrypt ter-hash)
-- admin@techstore.com / admin123
-- customer@techstore.com / customer123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`) VALUES
(1, 'Administrator TechStore', 'admin@techstore.com', '$2y$10$X.P0eJyFtpkx8sbyNNaw8udXRKhyH5q1rP3GkzvBhxyXAcUL27trO', 'admin', '081234567890', 'Gedung Cyber Tech Lt. 5, Jl. Rasuna Said No. 45, Jakarta Selatan'),
(2, 'Dimas Pratama', 'customer@techstore.com', '$2y$10$yn4.EQehZT.A.avndJtBaeOJmdtaMwPpM3Q4pyVjPgDwwkj6Nln2u', 'customer', '085712345678', 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan'),
(3, 'Siti Rahmawati', 'siti@techstore.com', '$2y$10$yn4.EQehZT.A.avndJtBaeOJmdtaMwPpM3Q4pyVjPgDwwkj6Nln2u', 'customer', '081398765432', 'Jl. Anggrek Nelimurni No. 88, Slipi, Jakarta Barat');

-- 2. Kategori Produk (8 Kategori Sesuai Ketentuan D)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Keyboard', 'keyboard', 'Keyboard mekanikal, gaming RGB, ergonomis dan wireless kantor', 'bi-keyboard'),
(2, 'Mouse', 'mouse', 'Mouse gaming presisi tinggi, wireless silent click, dan ergonomis vertikal', 'bi-mouse'),
(3, 'Headset', 'headset', 'Headset gaming surround 7.1, TWS noise cancelling, dan studio headphone', 'bi-headphones'),
(4, 'Monitor', 'monitor', 'Monitor gaming IPS refresh rate tinggi, ultrawide, dan resolusi 4K profesional', 'bi-display'),
(5, 'Webcam', 'webcam', 'Webcam streaming Full HD 1080p 60fps, 2K/4K ring light untuk meeting & live', 'bi-camera-video'),
(6, 'Flashdisk', 'flashdisk', 'Flashdisk USB 3.2 berkecepatan tinggi, OTG dual type-C dan logam tahan air', 'bi-usb-drive'),
(7, 'SSD dan Harddisk', 'ssd-dan-harddisk', 'NVMe PCIe Gen 4, SSD Eksternal Portable anti guncang, dan Harddisk Kapasitas Besar', 'bi-device-hdd'),
(8, 'Accessories Laptop', 'accessories-laptop', 'Cooling pad RGB, USB-C Multiport Docking Hub, Aluminium Stand, dan Sleeve Case', 'bi-laptop');

-- 3. Data Produk Contoh (24 Produk Lengkap dengan Spek, Stok, Harga Nyata)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `weight`, `image`, `status`) VALUES
-- Kategori 1: Keyboard
(1, 1, 'Keychron K2 V2 Wireless Mechanical Keyboard', 'keychron-k2-v2-wireless-mechanical-keyboard', 'Mechanical keyboard 75% compact layout dengan konektivitas Bluetooth 5.1 dan kabel Type-C. Dilengkapi Gateron G Pro Brown switch, RGB backlight 18 mode, baterai 4000mAh tahan hingga 240 jam, kompatibel Mac & Windows.', 1450000.00, 15, 850, 'keyboard-1.jpg', 'active'),
(2, 1, 'Logitech G Pro X TKL Lightspeed Gaming Keyboard', 'logitech-g-pro-x-tkl-lightspeed', 'Keyboard gaming tenkeyless kelas turnamen dengan teknologi nirkabel Lightspeed ultra-cepat 1ms. Switch GX Blue Clicky yang tactile dan akurat, LIGHTSYNC RGB, dan kontrol media terdedikasi.', 2350000.00, 8, 900, 'keyboard-2.jpg', 'active'),
(3, 1, 'Rexus Daxa M84 Pro Gasket Mount Hot-swappable', 'rexus-daxa-m84-pro', 'Keyboard mekanikal lokal premium dengan struktur Gasket Mount empuk, fitur hot-swappable 5-pin universal, 3 koneksi (2.4GHz, BT 5.0, Kabel), keycaps PBT doubleshot Cherry profile, dan peredam silikon ganda.', 899000.00, 3, 950, 'keyboard-3.jpg', 'active'),

-- Kategori 2: Mouse
(4, 2, 'Logitech MX Master 3S Performance Wireless Mouse', 'logitech-mx-master-3s', 'Mouse produktivitas tercanggih dengan sensor 8000 DPI Darkfield yang dapat bekerja di atas kaca. Dilengkapi MagSpeed electromagnetic scroll super cepat, tombol Silent Clicks hening 90%, dan thumb scroll wheel.', 1699000.00, 12, 350, 'mouse-1.jpg', 'active'),
(5, 2, 'Razer DeathAdder V3 Pro Wireless Ultra-lightweight', 'razer-deathadder-v3-pro', 'Mouse gaming esports ergonomis paling legendaris dengan bobot ultra-ringan hanya 63 gram. Menggunakan sensor Focus Pro 30K Optical, Optical Mouse Switches Gen-3 dengan ketahanan 90 juta klik, dan baterai hingga 90 jam.', 2150000.00, 7, 300, 'mouse-2.jpg', 'active'),
(6, 2, 'Fantech Helios II Pro XD3 V3 Wireless Gaming Mouse', 'fantech-helios-ii-pro-xd3v3', 'Mouse gaming simetris dengan sensor Pixart PAW3395 26.000 DPI, TTC Gold optical encoder, polling rate hingga 4000Hz, baterai hemat daya, serta PTFE skates murni meluncur sangat mulus.', 679000.00, 4, 250, 'mouse-3.jpg', 'active'),

-- Kategori 3: Headset
(7, 3, 'SteelSeries Arctis Nova 7 Wireless Gaming Headset', 'steelseries-arctis-nova-7', 'Headset gaming nirkabel dengan sistem audio Nova Acoustic System dan High Fidelity Drivers. Mendukung koneksi ganda simultan 2.4GHz dan Bluetooth, mikrofon ClearCast Gen 2 bertenaga AI noise cancelling.', 2890000.00, 6, 650, 'headset-1.jpg', 'active'),
(8, 3, 'HyperX Cloud III Gaming Headset 7.1 DTS:X', 'hyperx-cloud-iii-gaming-headset', 'Pembaruan dari headset legendaris Cloud II dengan bantalan memory foam ekstra lembut, driver 53mm yang di-tune ulang untuk kejernihan suara maksimal, mikrofon 10mm ultra-jernih, dan bodi aluminium tangguh.', 1399000.00, 14, 700, 'headset-2.jpg', 'active'),
(9, 3, 'Sony WH-1000XM5 Wireless Noise Cancelling Headphone', 'sony-wh-1000xm5-noise-cancelling', 'Headphone premium peredam bising terbaik industri dengan prosesor V1 & QN1 ganda. Menghadirkan audio Hi-Res nirkabel LDAC, 8 mikrofon beamforming untuk panggilan telepon kristal jernih, dan baterai 30 jam fast charging.', 4699000.00, 5, 800, 'headset-3.jpg', 'active'),

-- Kategori 4: Monitor
(10, 4, 'LG UltraGear 27GP850-B 27 Inch Nano IPS QHD 180Hz', 'lg-ultragear-27gp850-b', 'Monitor gaming 27 inci resolusi 2K QHD (2560x1440) dengan panel Nano IPS 1ms GtG, refresh rate 180Hz (O/C), color gamut DCI-P3 98%, HDR400, dan kompatibel NVIDIA G-Sync serta AMD FreeSync Premium.', 4950000.00, 4, 7500, 'monitor-1.jpg', 'active'),
(11, 4, 'Xiaomi Curved Gaming Monitor 30 Inch WFHD 200Hz', 'xiaomi-curved-gaming-monitor-30-inch', 'Monitor lengkung ultrawide 21:9 resolusi 2560x1080 dengan kelengkungan 1800R imersif, refresh rate 200Hz cepat, 99% sRGB color gamut, teknologi AMD FreeSync Premium, dan bezel super tipis.', 2899000.00, 3, 6800, 'monitor-2.jpg', 'active'),
(12, 4, 'ASUS ProArt Display PA248QV 24.1 Inch 16:10 IPS', 'asus-proart-display-pa248qv', 'Monitor profesional kreator konten dengan rasio 16:10 resolusi WUXGA (1920x1200), akurasi warna Delta E < 2 terkalibrasi pabrik Calman Verified, 100% sRGB dan 100% Rec. 709, stand ergonomis putar 90 derajat.', 3450000.00, 9, 6200, 'monitor-3.jpg', 'active'),

-- Kategori 5: Webcam
(13, 5, 'Logitech Brio 500 Full HD 1080p 60fps Auto-Framing', 'logitech-brio-500-full-hd-1080p', 'Webcam modern dengan fitur Show Mode untuk memiringkan kamera ke meja presentasi, koreksi cahaya RightLight 4 otomatis, mik noise reduction ganda, dan penutup privasi fisik terintegrasi.', 1799000.00, 11, 350, 'webcam-1.jpg', 'active'),
(14, 5, 'Razer Kiyo Pro Streaming Webcam with Light Sensor', 'razer-kiyo-pro-streaming-webcam', 'Webcam streaming kelas pro dengan sensor cahaya adaptif STARVIS Type 1/2.8 ultra-sensitif dalam kondisi redup, resolusi uncompressed Full HD 1080p 60fps, lensa sudut lebar dengan FOV yang dapat disesuaikan.', 1899000.00, 5, 450, 'webcam-2.jpg', 'active'),
(15, 5, 'Anker PowerConf C200 2K Webcam Ringan & Ringkas', 'anker-powerconf-c200-2k-webcam', 'Webcam 2K QHD jernih dengan aperture f/2.0 besar, autofokus cepat 0.5 detik, 2 mikrofon bertenaga AI pengurang bising sekitar, dan cover privasi geser.', 899000.00, 2, 300, 'webcam-3.jpg', 'active'),

-- Kategori 6: Flashdisk
(16, 6, 'SanDisk Ultra Dual Drive Luxe USB Type-C 128GB', 'sandisk-ultra-dual-drive-luxe-128gb', 'Flashdisk berbahan logam murni all-metal 2-in-1 dengan konektor reversibel USB Type-C dan Type-A tradisional. Kecepatan baca super cepat hingga 150MB/s untuk transfer file instan antara smartphone dan PC.', 245000.00, 35, 100, 'flashdisk-1.jpg', 'active'),
(17, 6, 'Kingston DataTraveler Max 256GB USB 3.2 Gen 2', 'kingston-datatraveler-max-256gb', 'Flashdisk performa tingkat tertinggi dengan standar USB 3.2 Gen 2 terkini, menyajikan kecepatan baca hingga 1000MB/s dan tulis 900MB/s setara SSD eksternal dalam ukuran saku.', 675000.00, 18, 120, 'flashdisk-2.jpg', 'active'),
(18, 6, 'Samsung BAR Plus 64GB Titan Gray USB 3.1 300MB/s', 'samsung-bar-plus-64gb-titan-gray', 'Flashdisk dengan bodi unibody logam tangguh tahan air, benturan, magnet, suhu ekstrem, dan sinar-X. Kecepatan transfer hingga 300MB/s dengan desain gantungan kunci ergonomis modern.', 175000.00, 25, 100, 'flashdisk-3.jpg', 'active'),

-- Kategori 7: SSD dan Harddisk
(19, 7, 'Samsung 980 PRO NVMe M.2 SSD 1TB PCIe 4.0', 'samsung-980-pro-nvme-m2-1tb', 'SSD internal performa monster dengan kecepatan baca sekuensial hingga 7000MB/s dan tulis 5000MB/s. Dilengkapi pengontrol berlapis nikel dan algoritma termal cerdas untuk menjaga kestabilan gaming & editing berat.', 1750000.00, 10, 200, 'storage-1.jpg', 'active'),
(20, 7, 'SanDisk Extreme Portable SSD V2 1TB USB 3.2 Gen 2', 'sandisk-extreme-portable-ssd-v2-1tb', 'SSD eksternal portabel tangguh dengan sertifikasi IP55 tahan air dan debu serta tahan jatuh hingga 2 meter. Kecepatan baca 1050MB/s dan tulis 1000MB/s dengan loop karabiner praktis dibawa bepergian.', 1699000.00, 8, 300, 'storage-2.jpg', 'active'),
(21, 7, 'Seagate One Touch External HDD 2TB USB 3.0', 'seagate-one-touch-external-hdd-2tb', 'Harddisk eksternal berkapasitas besar 2TB dengan casing aluminium bertekstur elegan. Mendukung backup otomatis satu klik, password encryption hardware 256-bit AES, dan kompatibel plug-and-play Windows & Mac.', 1120000.00, 15, 450, 'storage-3.jpg', 'active'),

-- Kategori 8: Accessories Laptop
(22, 8, 'Baseus Metal Gleam Series 8-in-1 Multifunctional Type-C Hub', 'baseus-metal-gleam-8-in-1-hub', 'Docking adapter USB-C lengkap dengan port HDMI 4K@60Hz, 3x USB 3.0 5Gbps, Type-C Power Delivery 100W fast charge, Gigabit Ethernet RJ45 1000Mbps, serta slot SD/TF card reader bodi aluminium aerospace.', 485000.00, 16, 250, 'acc-1.jpg', 'active'),
(23, 8, 'DeepCool Multi Core X6 Laptop Cooling Pad Dual Fan', 'deepcool-multi-core-x6-cooling-pad', 'Cooling pad laptop dengan 4 kipas pendingin (dua 140mm + dua 100mm) berkecepatan tinggi dengan 4 status kontrol status kipas. Desain geometri kupu-kupu hitam futuristik dengan 2 sudut kemiringan ergonomis.', 289000.00, 2, 1100, 'acc-2.jpg', 'active'),
(24, 8, 'Orico Aluminium Adjustable Laptop Stand Ergonomic', 'orico-aluminium-adjustable-laptop-stand', 'Stand dudukan laptop berbahan aluminium murni CNC anodized kuat menopang laptop hingga 17 inci. Sudut kemiringan dan ketinggian dapat disesuaikan fleksibel untuk mencegah nyeri leher dan memperlancar sirkulasi udara laptop.', 235000.00, 22, 900, 'acc-3.jpg', 'active');

-- 4. Demo Transaksi Contoh (Agar Dashboard, Grafik, dan Laporan Langsung Berisi Data Nyata Saat Presentasi)
INSERT INTO `transactions` (`id`, `transaction_code`, `user_id`, `total_amount`, `shipping_cost`, `grand_total`, `status`, `recipient_name`, `recipient_phone`, `shipping_address`, `shipping_courier`, `notes`, `created_at`) VALUES
(1, 'TRX-20261028-0001', 2, 3149000.00, 25000.00, 3174000.00, 'completed', 'Dimas Pratama', '085712345678', 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan', 'JNE Regular', 'Tolong packing kayu dan bubble wrap tebal', '2026-10-28 09:30:00'),
(2, 'TRX-20261029-0002', 3, 1750000.00, 15000.00, 1765000.00, 'completed', 'Siti Rahmawati', '081398765432', 'Jl. Anggrek Nelimurni No. 88, Slipi, Jakarta Barat', 'SiCepat BEST', 'Kirim saat jam kerja kantor', '2026-10-29 14:15:00'),
(3, 'TRX-20261030-0003', 2, 1699000.00, 20000.00, 1719000.00, 'shipped', 'Dimas Pratama', '085712345678', 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan', 'J&T Express', 'Nomor resi mohon diupdate segera', '2026-10-30 11:20:00'),
(4, 'TRX-20261031-0004', 3, 720000.00, 15000.00, 735000.00, 'processing', 'Siti Rahmawati', '081398765432', 'Jl. Anggrek Nelimurni No. 88, Slipi, Jakarta Barat', 'JNE Regular', 'Pilihan warna hitam', '2026-10-31 08:45:00'),
(5, 'TRX-20261031-0005', 2, 899000.00, 18000.00, 917000.00, 'pending', 'Dimas Pratama', '085712345678', 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan', 'SiCepat Reguler', 'Simulasi pembayaran transfer bank', '2026-10-31 10:10:00');

-- 5. Detail Barang Transaksi
INSERT INTO `transaction_details` (`id`, `transaction_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 'Keychron K2 V2 Wireless Mechanical Keyboard', 1450000.00, 1, 1450000.00),
(2, 1, 4, 'Logitech MX Master 3S Performance Wireless Mouse', 1699000.00, 1, 1699000.00),
(3, 2, 19, 'Samsung 980 PRO NVMe M.2 SSD 1TB PCIe 4.0', 1750000.00, 1, 1750000.00),
(4, 3, 20, 'SanDisk Extreme Portable SSD V2 1TB USB 3.2 Gen 2', 1699000.00, 1, 1699000.00),
(5, 4, 22, 'Baseus Metal Gleam Series 8-in-1 Multifunctional Type-C Hub', 485000.00, 1, 485000.00),
(6, 4, 24, 'Orico Aluminium Adjustable Laptop Stand Ergonomic', 235000.00, 1, 235000.00),
(7, 5, 3, 'Rexus Daxa M84 Pro Gasket Mount Hot-swappable', 899000.00, 1, 899000.00);

-- 6. Demo Pembayaran (Payments)
INSERT INTO `payments` (`id`, `transaction_id`, `payment_method`, `payment_status`, `amount`, `payment_proof`, `payment_date`, `verified_by`, `verified_at`, `notes`) VALUES
(1, 1, 'Transfer Bank - BCA', 'paid', 3174000.00, 'proof-trx-1.jpg', '2026-10-28 09:45:00', 1, '2026-10-28 10:00:00', 'Pembayaran via BCA telah diverifikasi lunas.'),
(2, 2, 'QRIS', 'paid', 1765000.00, NULL, '2026-10-29 14:18:00', 1, '2026-10-29 14:20:00', 'Pembayaran simulasi QRIS instan sukses.'),
(3, 3, 'GoPay', 'paid', 1719000.00, NULL, '2026-10-30 11:25:00', 1, '2026-10-30 11:30:00', 'Pembayaran via GoPay terverifikasi otomatis.'),
(4, 4, 'Transfer Bank - Mandiri', 'paid', 735000.00, 'proof-trx-4.jpg', '2026-10-31 09:00:00', 1, '2026-10-31 09:15:00', 'Bukti transfer valid dari Bank Mandiri.'),
(5, 5, 'Transfer Bank - BCA', 'pending', 917000.00, NULL, '2026-10-31 10:10:00', NULL, NULL, 'Menunggu unggahan bukti transfer dari pelanggan.');

-- 7. Demo Faktur (Invoices)
INSERT INTO `invoices` (`id`, `invoice_code`, `transaction_id`, `user_id`, `invoice_date`, `due_date`, `total_amount`, `status`) VALUES
(1, 'INV/20261028/0001', 1, 2, '2026-10-28', '2026-10-29', 3174000.00, 'paid'),
(2, 'INV/20261029/0002', 2, 3, '2026-10-29', '2026-10-30', 1765000.00, 'paid'),
(3, 'INV/20261030/0003', 3, 2, '2026-10-30', '2026-10-31', 1719000.00, 'paid'),
(4, 'INV/20261031/0004', 4, 3, '2026-10-31', '2026-11-01', 735000.00, 'paid'),
(5, 'INV/20261031/0005', 5, 2, '2026-10-31', '2026-11-01', 917000.00, 'unpaid');
