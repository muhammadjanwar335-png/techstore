<?php
/**
 * TECHSTORE - Tambah Kategori (Admin Handler)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'bi-tag');
    $description = sanitize($_POST['description'] ?? '');

    if (empty($name)) {
        set_flash('danger', 'Nama kategori tidak boleh kosong!');
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $icon]);
            set_flash('success', "Kategori <strong>{$name}</strong> berhasil ditambahkan!");
        } catch (Exception $e) {
            set_flash('danger', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }
}

header('Location: ' . BASE_URL . 'admin/categories/index.php');
exit;
