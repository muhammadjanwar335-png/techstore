/**
 * TECHSTORE - Main JavaScript Functions
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Preview Gambar Sebelum Upload (Admin Produk & Customer Bukti Bayar)
    const imageInputs = document.querySelectorAll('.input-image-preview');
    imageInputs.forEach(input => {
        input.addEventListener('change', function () {
            const previewTargetId = this.getAttribute('data-preview-target');
            const previewEl = document.getElementById(previewTargetId);
            if (previewEl && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewEl.src = e.target.result;
                    previewEl.classList.remove('d-none');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // 2. Tombol Konfirmasi Hapus Data
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const itemName = this.getAttribute('data-item-name') || 'data ini';
            if (!confirm(`Apakah Anda yakin ingin menghapus ${itemName}? Tindakan ini tidak dapat dibatalkan.`)) {
                e.preventDefault();
            }
        });
    });

    // 3. Tombol Tambah/Kurang Jumlah Keranjang Belanja
    const qtyInputs = document.querySelectorAll('.cart-qty-input');
    qtyInputs.forEach(input => {
        input.addEventListener('change', function () {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });

    // 4. Inisialisasi Tooltip Bootstrap (jika ada)
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Format Angka ke Rupiah di Javascript
function formatRupiahJS(number) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
}
