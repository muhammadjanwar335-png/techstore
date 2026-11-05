</main>
    <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
            <span>&copy; <?= date('Y') ?> <strong>TECHSTORE</strong> - Sistem Informasi Penjualan Accessories Komputer & Laptop.</span>
            <span>Tugas Proyek Perkuliahan Full Stack Web Developer</span>
        </div>
    </footer>
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- Bootstrap 5.3.3 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App JS -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

<script>
    // Update Jam Live
    function updateLiveClock() {
        const el = document.getElementById('liveClock');
        if (el) {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            el.textContent = `${h}:${m}`;
        }
    }
    setInterval(updateLiveClock, 30000);
</script>
</body>
</html>
