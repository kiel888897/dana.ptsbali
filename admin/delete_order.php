<?php
// delete_order.php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    if ($order_id > 0) {
        // Hapus semua item dari order_items yang sesuai order_id
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
    }
}

// Kembali ke halaman daftar
header('Location: index.php');
exit;
