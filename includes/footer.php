</main>

<footer class="footer-tech mt-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Kolom 1: Profil Toko -->
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand-tech text-white mb-3 d-inline-flex align-items-center" href="<?= BASE_URL ?>index.php">
                    <i class="bi bi-cpu-fill text-primary fs-3"></i>
                    <span class="fs-4 fw-bold">TECH<span class="text-primary">STORE</span></span>
                </a>
                <p class="text-secondary small leading-relaxed">
                    TechStore adalah pusat penjualan accessories komputer, laptop, dan peripheral gaming terlengkap dan bergaransi resmi. Kami menghadirkan produk berkualitas dari brand ternama dunia untuk produktivitas dan pengalaman gaming maksimal.
                </p>
                <div class="d-flex gap-3 text-secondary fs-5 mt-3">
                    <a href="#" class="text-secondary"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-secondary"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="text-secondary"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-secondary"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>

            <!-- Kolom 2: Navigasi Cepat -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="fw-bold text-white mb-3">Navigasi</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= BASE_URL ?>index.php">Beranda</a></li>
                    <li><a href="<?= BASE_URL ?>customer/products.php">Katalog Produk</a></li>
                    <li><a href="<?= BASE_URL ?>customer/cart.php">Keranjang Belanja</a></li>
                    <li><a href="<?= BASE_URL ?>customer/history.php">Lacak Pesanan</a></li>
                    <li><a href="<?= BASE_URL ?>auth/login.php">Masuk Akun</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Kategori Populer -->
            <div class="col-lg-3 col-md-6 col-6">
                <h5 class="fw-bold text-white mb-3">Kategori Pilihan</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= BASE_URL ?>customer/products.php?category=1">Mechanical Keyboard</a></li>
                    <li><a href="<?= BASE_URL ?>customer/products.php?category=2">Gaming Mouse & Silent</a></li>
                    <li><a href="<?= BASE_URL ?>customer/products.php?category=3">Headset & TWS 7.1</a></li>
                    <li><a href="<?= BASE_URL ?>customer/products.php?category=4">Monitor IPS & Ultrawide</a></li>
                    <li><a href="<?= BASE_URL ?>customer/products.php?category=7">NVMe SSD & Eksternal HDD</a></li>
                </ul>
            </div>

            <!-- Kolom 4: Hubungi Kami & Metode Pembayaran -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold text-white mb-3">Kontak & Lokasi</h5>
                <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-3">
                    <li><i class="bi bi-geo-alt-fill text-primary me-2"></i>Gedung Cyber Tech Lt. 5, Jakarta</li>
                    <li><i class="bi bi-telephone-fill text-primary me-2"></i>(021) 555-8989 / 0812-3456-7890</li>
                    <li><i class="bi bi-envelope-fill text-primary me-2"></i>support@techstore.com</li>
                    <li><i class="bi bi-clock-fill text-primary me-2"></i>Senin - Sabtu: 09:00 - 18:00 WIB</li>
                </ul>
                <div class="text-white small fw-bold mb-2">Metode Pembayaran:</div>
                <div class="d-flex flex-wrap gap-2 text-secondary small">
                    <span class="badge bg-secondary">BCA</span>
                    <span class="badge bg-secondary">Mandiri</span>
                    <span class="badge bg-secondary">QRIS</span>
                    <span class="badge bg-secondary">DANA</span>
                    <span class="badge bg-secondary">GoPay</span>
                    <span class="badge bg-secondary">OVO</span>
                    <span class="badge bg-secondary">COD</span>
                </div>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="row align-items-center small text-secondary">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; <?= date('Y') ?> <strong>TECHSTORE</strong>. Dibuat untuk Memenuhi Tugas Proyek Perkuliahan Full Stack Web Developer.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span>Versi 1.0.0 &bull; PHP Native PDO &bull; MySQL &bull; Bootstrap 5</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 (Opsional untuk alert modern) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom App JS -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

</body>
</html>
