# TECHSTORE – SISTEM INFORMASI PENJUALAN ACCESSORIES KOMPUTER DAN LAPTOP
### Versi Serverless & Client-Side LocalStorage (Bebas Database MySQL & XAMPP)

TechStore adalah website sistem informasi e-commerce penjualan aksesoris komputer dan laptop yang dirancang khusus untuk memenuhi tugas proyek perkuliahan Pemrograman Web / Full Stack Web Development.

Seluruh sistem telah **dimodernisasi menjadi 100% Client-Side Web Application** menggunakan **HTML5, CSS3, JavaScript (ES6), dan LocalStorage**. Website ini **TIDAK lagi memerlukan PHP, MySQL, Apache, phpMyAdmin, maupun XAMPP**. Anda dapat menjalankannya langsung di browser cukup dengan mengklik dua kali file `index.html`, menjalankannya melalui VS Code Live Server, atau mengunggahnya ke hosting statis gratis seperti GitHub Pages, Netlify, dan Vercel.

---

## A. FITUR UTAMA & KEUNGGULAN SISTEM

1. **100% Bebas Server & Database Lokal**:
   - Berjalan penuh di sisi klien (browser) tanpa instalasi XAMPP atau konfigurasi database MySQL.
   - Menggunakan JavaScript `localStorage` sebagai database engine persisten (`TechDB` di `assets/js/db.js`).
   - Data produk, kategori, keranjang belanja, transaksi, bukti pembayaran, dan laporan penjualan tersimpan secara persisten di browser.
   - Dilengkapi tombol reset data bawaan untuk mengembalikan data awal kapan saja.

2. **Hak Akses & Role-Based Authentication**:
   - Role **Admin**: Akses penuh ke dashboard, CRUD produk, CRUD kategori, verifikasi pembayaran, manajemen status pesanan, dan laporan penjualan.
   - Role **Customer**: Registrasi, katalog produk dengan filter dan pencarian, keranjang belanja, checkout, simulasi pembayaran, riwayat pesanan, profil, dan cetak invoice.
   - Route guard otomatis: Halaman admin terlindungi dan akan mengarahkan pengguna non-admin ke halaman login.

3. **Master Data & CRUD Lengkap**:
   - 8 Kategori Aksesoris Komputer (Keyboard, Mouse, Headset, Monitor, Webcam, Flashdisk, SSD & Harddisk, Accessories Laptop).
   - 24 Produk Demo lengkap dengan gambar berkualitas tinggi, harga format Rupiah, bobot barang, dan stok.
   - Admin dapat **Menambah**, **Melihat**, **Mengubah (Edit)**, dan **Menghapus** produk serta kategori.
   - Fitur upload foto produk baru dan bukti transfer bank menggunakan HTML5 `FileReader` yang dikonversi ke Base64 dan disimpan di `localStorage`.

4. **Transaksi & Keranjang Belanja Real-Time**:
   - Tambah barang ke keranjang dengan quantity dinamis.
   - Perhitungan otomatis subtotal, ongkos kirim berdasarkan kurir (JNE, SiCepat, J&T, POS Indonesia), dan total tagihan.
   - Penomoran transaksi otomatis (`TRX-YYYYMMDD-XXXX`) dan nomor invoice otomatis (`INV/YYYYMMDD/XXXX`).
   - **Manajemen Stok Otomatis**: Stok berkurang seketika saat pesanan dibuat, dan dikembalikan (restorasi) otomatis jika pesanan dibatalkan oleh Admin.

5. **Simulasi Pembayaran Terlengkap**:
   - **QRIS**: Menampilkan kode QR dinamis dengan tombol simulasi 1-klik bayar instan (otomatis Paid).
   - **Transfer Bank (BCA, Mandiri, BRI, BNI)**: Menampilkan instruksi nomor rekening, timer countdown batas bayar, dan form upload bukti transfer gambar.
   - **E-Wallet (DANA, OVO, GoPay)**: Menampilkan panduan bayar e-wallet dengan tombol konfirmasi bayar.
   - **COD (Cash on Delivery)**: Bayar tunai di tempat saat barang tiba.

6. **Invoice & Cetak Struk Kasir**:
   - **Faktur Penjualan A4 Resmi** (`invoice/index.html`): Dilengkapi kop toko, nomor invoice, detail pembeli, rincian item, dan stempel status (PAID/UNPAID) yang siap cetak atau simpan ke PDF.
   - **Struk Kasir POS Thermal** (`invoice/struk.html`): Format cetak struk kasir mini ukuran 80mm / 58mm dengan font monospaced khas toko komputer modern.

7. **Dashboard Admin & Visualisasi Grafik (Chart.js)**:
   - 4 Card Metrik: Total Pendapatan, Total Transaksi, Total Produk, dan Peringatan Stok Menipis (< 5 unit).
   - Grafik Penjualan 7 Hari Terakhir menggunakan Chart.js (Line Chart).
   - Grafik Komposisi Kategori Produk menggunakan Chart.js (Doughnut Chart).
   - Tabel 5 transaksi terbaru dan daftar 5 produk terlaris.

8. **Laporan Penjualan & Ekspor Data**:
   - Filter laporan penjualan: **Hari Ini**, **7 Hari Terakhir**, **Bulan Ini**, atau **Rentang Tanggal Kustom**.
   - Grafik batang omset penjualan dinamis (Chart.js Bar Chart).
   - **Export ke Excel (.CSV)** langsung di browser tanpa server (Blob download UTF-8).
   - **Cetak Laporan Penjualan (PDF View)** dengan format resmi A4, kop surat, dan kolom tanda tangan pengesahan.

---

## B. AKUN DEMO UNTUK PRESENTASI

Halaman login (`auth/login.html`) telah dilengkapi tombol **1-Klik Demo Login** untuk memudahkan presentasi di depan dosen:

| Peran (Role) | Email Demo | Password Demo | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **ADMIN** | `admin@techstore.com` | `admin123` | Dashboard admin, CRUD produk & kategori, verifikasi pembayaran, ubah status pesanan, laporan penjualan & export Excel. |
| **CUSTOMER** | `customer@techstore.com` | `customer123` | Beranda toko, katalog, keranjang, checkout, simulasi pembayaran, riwayat pesanan, cetak invoice & struk kasir. |

---

## C. STRUKTUR FOLDER PROJECT

Struktur project disusun rapi dan modular dengan navigasi tautan relatif:

```
techstore/
├── assets/
│   ├── css/
│   │   └── style.css            # Desain tema TechStore Navy Blue, responsive card, & print styling
│   └── js/
│       ├── db.js                # Core Database Engine (TechDB), seed data, CRUD, cart, auth, & helpers
│       └── main.js              # Inisialisasi tema & global event listener
├── admin/
│   ├── dashboard.html           # Dashboard statistik metrik, Chart.js, transaksi baru & produk terlaris
│   ├── products/
│   │   ├── index.html           # Tabel data produk, pencarian, & filter stok menipis
│   │   ├── create.html          # Form tambah produk baru + preview foto
│   │   └── edit.html            # Form edit data produk + update stok & harga
│   ├── categories/
│   │   └── index.html           # Manajemen kategori (CRUD dalam modal interaktif)
│   ├── customers/
│   │   ├── index.html           # Daftar pelanggan terdaftar & total belanja
│   │   └── detail.html          # Riwayat transaksi individual pelanggan
│   ├── transactions/
│   │   ├── index.html           # Daftar seluruh pesanan pelanggan & filter status
│   │   └── detail.html          # Detail pesanan, alamat, & form ubah status pesanan
│   ├── payments/
│   │   └── index.html           # Verifikasi bukti transfer bank (Setujui / Tolak)
│   └── reports/
│       └── index.html           # Laporan penjualan, grafik omset, export Excel CSV & cetak PDF
├── customer/
│   ├── dashboard.html           # Dashboard pelanggan (pesanan aktif, total belanja)
│   ├── products.html            # Katalog produk dengan pencarian & filter kategori
│   ├── cart.html                # Keranjang belanja dengan update quantity & subtotal otomatis
│   ├── checkout.html            # Form checkout, pilihan kurir, & metode pembayaran
│   ├── payment.html             # Simulasi pembayaran (QRIS instan, Transfer, E-Wallet, COD)
│   ├── history.html             # Riwayat pesanan & progress bar status pesanan
│   ├── order-detail.html        # Rincian pesanan pembeli & kurir pengiriman
│   └── profile.html             # Edit profil pelanggan & kata sandi
├── auth/
│   ├── login.html               # Halaman login modern dengan tombol cepat 1-klik akun demo
│   └── register.html            # Form registrasi pelanggan baru (disimpan ke LocalStorage)
├── invoice/
│   ├── index.html               # Faktur Penjualan Resmi A4 (Cetak & Download PDF)
│   └── struk.html               # Cetak Struk Kasir / POS Thermal (58mm/80mm)
├── index.html                   # Landing page utama toko online TechStore
├── product-detail.html          # Halaman detail produk, spesifikasi, & tombol beli langsung
└── README.md                    # Dokumentasi lengkap sistem & panduan presentasi
```

---

## D. CARA MENJALANKAN WEBSITE (TANPA XAMPP & TANPA DATABASE)

Karena website ini 100% berbasis HTML5 dan JavaScript, Anda dapat membukanya dengan beberapa metode yang sangat fleksibel:

### Metode 1: Mengklik Dua Kali File `index.html` (Paling Cepat & Praktis)
1. Buka folder `techstore` di File Explorer komputer Anda.
2. Klik dua kali pada file `index.html`.
3. Website akan langsung terbuka di browser default Anda (Google Chrome, Microsoft Edge, Mozilla Firefox, dll).
4. Semua fitur, link, keranjang belanja, simulasi pembayaran, dan dashboard admin langsung bekerja 100%!

### Metode 2: Menggunakan Visual Studio Code Live Server (Direkomendasikan untuk Presentasi)
1. Buka aplikasi **Visual Studio Code**.
2. Pilih menu **File** > **Open Folder...**, lalu pilih folder `techstore`.
3. Klik kanan pada file `index.html`, lalu pilih **"Open with Live Server"**.
4. Website akan terbuka otomatis di alamat `http://127.0.0.1:5500/index.html`.

### Metode 3: Upload ke Web Hosting Statis Gratis (Agar Bisa Diakses Lewat Internet)
Website ini 100% kompatibel dengan layanan hosting statis tanpa konfigurasi server apapun:
- **GitHub Pages**: Buat repositori baru di GitHub, upload seluruh isi folder `techstore`, lalu aktifkan GitHub Pages di menu Settings > Pages.
- **Netlify / Vercel**: Tarik (drag & drop) folder `techstore` ke dashboard Netlify Drop (`app.netlify.com/drop`) untuk langsung mendapatkan tautan website live dalam 10 detik.

---

## E. SKENARIO ALUR DEMONSTRASI PRESENTASI DI HADAPAN DOSEN

Gunakan skenario alur presentasi di bawah ini saat mendemonstrasikan sistem kepada dosen penguji agar seluruh fitur unggulan dapat dipamerkan secara terstruktur:

```
[1. Landing Page] ──> [2. Login Customer] ──> [3. Katalog & Keranjang] ──> [4. Checkout & Bayar]
                                                                                   │
[8. Laporan & Export] <── [7. Verifikasi Bayar] <── [6. CRUD Produk] <── [5. Login Admin]
```

### Tahap 1: Membuka Halaman Utama Toko (Landing Page)
1. Buka halaman `index.html`.
2. Jelaskan konsep desain UI/UX:
   - Warna bertema **Navy Blue Tech Professional** (`#0f172a`, `#1e293b`, `#2563eb`).
   - Tampilan responsif di laptop dan smartphone.
   - Banner promo modern dan nilai lebih toko (Garansi Resmi, Pengiriman Cepat, Pembayaran Aman, Support 24/7).
3. Tunjukkan **8 Kategori Produk Utama** dan grid **Produk Unggulan** lengkap dengan harga Rupiah dan badge ketersediaan stok.

### Tahap 2: Registrasi / Login Sebagai Customer
1. Klik tombol **Masuk** di navigasi kanan atas (`auth/login.html`).
2. Klik tombol demo **"Isi Cepat: Customer Demo"** (`customer@techstore.com` / `customer123`).
3. Klik tombol **Masuk Sekarang**. Sistem langsung mengarahkan ke halaman Beranda dengan navbar khusus customer (menampilkan nama pengguna dan jumlah keranjang belanja).

### Tahap 3: Eksplorasi Katalog & Keranjang Belanja
1. Masuk ke menu **Katalog Produk** (`customer/products.html`).
2. Demonstrasikan fitur pencarian produk (misal ketik *"Keyboard"* atau *"Logitech"*).
3. Demonstrasikan filter berdasarkan kategori (misal klik kategori *"Monitor"* atau *"Headset"*).
4. Klik salah satu produk untuk melihat detail spesifikasi (`product-detail.html`).
5. Klik tombol **"+ Keranjang"** pada beberapa produk. Perhatikan indikator badge pada icon keranjang di navbar bertambah secara real-time.
6. Buka menu **Keranjang Belanja** (`customer/cart.html`):
   - Ubah jumlah produk (+ / -), perhatikan sistem menghitung ulang subtotal dan total harga secara otomatis.
   - Hapus salah satu item untuk menunjukkan fungsionalitas cart.

### Tahap 4: Checkout, Simulasi Pembayaran, & Cetak Invoice
1. Klik tombol **"Lanjut ke Checkout"** (`customer/checkout.html`).
2. Perhatikan data nama pembeli, email, telepon, dan alamat telah terisi otomatis dari profil.
3. Pilih kurir pengiriman (JNE Regular, SiCepat BEST, J&T Express) -> ongkos kirim akan diperbarui secara dinamis pada ringkasan pesanan.
4. Pilih metode pembayaran **QRIS** atau **Transfer Bank**.
5. Klik **"Bayar Sekarang"**. Sistem akan:
   - Membuat nomor transaksi unik (`TRX-YYYYMMDD-XXXX`).
   - Membuat nomor invoice resmi (`INV/YYYYMMDD/XXXX`).
   - Mengurangi stok barang secara otomatis di database `localStorage`.
6. Pada halaman pembayaran (`customer/payment.html`):
   - Jika QRIS: Klik tombol hijau **"Simulasi Bayar Sekarang (Instan Lunas)"**. Status langsung berubah menjadi *Paid*.
   - Jika Transfer Bank: Tunjukkan timer mundur batas pembayaran dan coba upload foto bukti transfer (menggunakan file gambar apapun dari komputer).
7. Klik tombol **"Lihat Invoice Resmi"** (`invoice/index.html`):
   - Tunjukkan invoice A4 resmi lengkap dengan watermark status pembayaran, rincian biaya, dan ongkir.
   - Klik tombol **"Cetak Invoice"** untuk memicu dialog print browser (`window.print()`).
8. Klik tombol **"Cetak Struk Kasir"** (`invoice/struk.html`):
   - Tunjukkan format struk kasir thermal POS (80mm/58mm) seperti pada struk toko ritel modern.

### Tahap 5: Login Sebagai Admin & Menjelaskan Dashboard
1. Logout dari akun customer (`auth/login.html`).
2. Klik tombol demo **"Isi Cepat: Admin Demo"** (`admin@techstore.com` / `admin123`) lalu klik Masuk.
3. Anda akan diarahkan ke **Dashboard Admin** (`admin/dashboard.html`):
   - Jelaskan 4 kartu metrik utama (Total Pendapatan, Total Transaksi, Total Produk, dan Peringatan Stok Menipis).
   - Tunjukkan grafik penjualan 7 hari terakhir berbasis **Chart.js** (Line Chart interaktif dengan tooltip).
   - Tunjukkan grafik distribusi kategori berbasis **Chart.js** (Doughnut Chart).
   - Tunjukkan daftar transaksi terbaru dan 5 produk paling laris.

### Tahap 6: Manajemen Master Data Produk & Kategori (CRUD)
1. Masuk ke menu **Data Produk** (`admin/products/index.html`):
   - Tunjukkan tabel produk lengkap dengan filter pencarian dan tombol filter cepat *"Stok Menipis (< 5)"*.
   - Klik tombol **"+ Tambah Produk"** (`admin/products/create.html`): isi nama produk, kategori, harga, stok, berat, deskripsi, dan pilih file gambar. Simpan dan perhatikan produk baru langsung muncul di tabel.
   - Klik tombol **Edit** pada salah satu produk (`admin/products/edit.html`): ubah harga atau stok barang, simpan perubahan.
   - Klik tombol **Hapus** pada produk demo: perhatikan konfirmasi dan data berhasil terhapus.
2. Masuk ke menu **Kategori Produk** (`admin/categories/index.html`):
   - Tunjukkan tabel kategori beserta jumlah produk yang terhubung di masing-masing kategori.
   - Tambah kategori baru melalui modal interaktif tanpa reload halaman.

### Tahap 7: Verifikasi Pembayaran & Manajemen Status Transaksi
1. Masuk ke menu **Verifikasi Pembayaran** (`admin/payments/index.html`):
   - Tunjukkan daftar bukti pembayaran yang masuk dari pelanggan.
   - Klik tombol pratinjau bukti untuk melihat foto bukti transfer dalam modal pop-up.
   - Klik tombol **"Setujui Pembayaran"** untuk mengubah status menjadi *Paid*.
2. Masuk ke menu **Semua Transaksi** (`admin/transactions/index.html`):
   - Tunjukkan detail pesanan yang baru saja dibuat di Tahap 4.
   - Masuk ke detail transaksi (`admin/transactions/detail.html`):
     - Ubah status pesanan secara bertahap: *Pending* -> *Processing* -> *Shipped* -> *Completed*.
     - **Poin Plus untuk Dosen**: Jelaskan bahwa jika status transaksi diubah menjadi *Cancelled*, sistem secara cerdas akan mengembalikan stok barang ke database secara otomatis.

### Tahap 8: Laporan Penjualan, Ekspor Excel, & Cetak PDF
1. Masuk ke menu **Laporan Penjualan** (`admin/reports/index.html`):
   - Uji filter preset: **Hari Ini**, **7 Hari Terakhir**, **Bulan Ini**, atau pilih tanggal kustom.
   - Tunjukkan metrik total omset, jumlah transaksi, dan barang terjual yang dihitung otomatis berdasarkan data aktual.
   - Tunjukkan grafik omset harian yang beradaptasi sesuai periode filter.
2. Klik tombol hijau **"Export Excel (.CSV)"**:
   - Browser akan langsung mengunduh file `laporan-penjualan-techstore-[tanggal].csv`.
   - File tersebut dapat dibuka langsung dengan rapi di Microsoft Excel.
3. Klik tombol biru **"Cetak Laporan PDF"**:
   - Membuka pratinjau cetak laporan resmi dengan kop surat, tabel ringkasan, dan kolom pengesahan pimpinan.

---

## F. SPESIFIKASI DATABASE CLIENT-SIDE (LOCALSTORAGE)

Data disimpan dalam `localStorage` browser dengan key ber-namespace `techstore_*`:

| Key LocalStorage | Isi / Struktur Data |
| :--- | :--- |
| `techstore_users` | Array data pengguna (Admin, Customer) lengkap dengan email, password, nama, dan no HP. |
| `techstore_categories` | Array kategori produk (ID, Nama Kategori, Icon Bootstrap, Slug). |
| `techstore_products` | Array master produk (ID, Nama, Kategori, Harga, Stok, Berat, Gambar URL/Base64, Deskripsi). |
| `techstore_cart` | Array keranjang belanja sementara pengguna yang sedang login. |
| `techstore_transactions` | Array data transaksi pemesanan (ID, No Transaksi, Tanggal, Customer, Status, Total, Ongkir). |
| `techstore_transaction_details` | Array rincian produk yang dibeli pada setiap nomor transaksi. |
| `techstore_payments` | Array data pembayaran (ID, No Transaksi, Metode, Status, Bukti Base64, Tanggal Bayar). |
| `techstore_invoices` | Array nomor faktur resmi yang diterbitkan untuk setiap pesanan. |
| `techstore_auth` | Object sesi login aktif pengguna saat ini. |

*Jika ingin mereset seluruh data ke kondisi awal demo, Anda dapat mengklik tombol "Reset Database" pada navigasi / footer atau menjalankan perintah `TechDB.reset()` di Developer Console browser (F12).*

---

## G. RINGKASAN KELEBIHAN VERSI TANPA DATABASE INI
- **Sangat Mudah Didemonstrasikan**: Tidak ada risiko error koneksi database, Apache crash, port MySQL bentrok, atau file `.env` salah konfigurasi saat presentasi di depan dosen.
- **Portabilitas Tinggi**: Folder project dapat langsung dicopy ke flashdisk dan dijalankan di laptop mana saja tanpa perlu install software server apapun.
- **Performa Instan**: Respon UI sangat cepat karena seluruh operasi CRUD dan kalkulasi diproses langsung oleh browser engine JavaScript.
- **Tampilan Tetap Profesional**: Desain visual, layout dashboard, navigasi, dan grafik Chart.js 100% sama dengan aplikasi web modern tingkat industri.

---
**TechStore © 2026 – Project Tugas Kuliah Pemrograman Web & Sistem Informasi Penjualan**
