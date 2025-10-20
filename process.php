<?php
require_once 'config.php';

// Ambil daftar anggota (untuk dropdown di form)
$stmt = $pdo->query("SELECT id, nama FROM anggota ORDER BY nama ASC");
$anggota_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$customer_name = trim($_POST['customer_name'] ?? '');
$qtys = $_POST['qty'] ?? []; // contoh: ['S'=>2, 'M'=>1, ...]

if ($customer_name === '') {
    die('Nama pemesan diperlukan.');
}

// 🔹 Cek apakah nama sudah ada di tabel anggota
$stmt = $pdo->prepare("SELECT id FROM anggota WHERE nama = ?");
$stmt->execute([$customer_name]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggota) {
    // Jika belum ada, buat anggota baru
    $stmt = $pdo->prepare("INSERT INTO anggota (nama) VALUES (?)");
    $stmt->execute([$customer_name]);
    $anggota_id = $pdo->lastInsertId();
} else {
    // Kalau sudah ada, ambil ID-nya
    $anggota_id = $anggota['id'];
}
// 🔹 Cek apakah anggota sudah pernah memesan sebelumnya
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
$stmtCheck->execute([$anggota_id]);
$already = (int)$stmtCheck->fetchColumn();
if ($already > 0) {
    // Redirect kembali ke form dan tunjukkan pesan error di index.php
    header('Location: index.php?error=1');
    exit;
}
// 🔹 Filter input jumlah baju
$clean_items = [];
foreach ($qtys as $size => $q) {
    $q = (int)$q;
    if ($q > 0) {
        $clean_items[] = ['size' => $size, 'qty' => $q];
    }
}

if (count($clean_items) === 0) {
    die('Minimal memesan 1 baju.');
}

try {
    $pdo->beginTransaction();

    // 🔹 Masukkan ke tabel order_items, tapi order_id = id anggota
    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_id, size, qty, created_at)
        VALUES (:order_id, :size, :qty, NOW())
    ");

    foreach ($clean_items as $it) {
        $stmtItem->execute([
            ':order_id' => $anggota_id,
            ':size' => $it['size'],
            ':qty' => $it['qty']
        ]);
    }

    $pdo->commit();

    header("Location: view_orders.php?success=1&anggota_id=" . $anggota_id);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Gagal menyimpan pesanan: " . $e->getMessage());
}
