<?php
require_once 'config.php';

$success = isset($_GET['success']);
$order_id_new = isset($_GET['anggota_id']) ? (int)$_GET['anggota_id'] : null;

// Ambil 1 anggota terakhir yang baru saja memesan
$stmt = $pdo->query("
    SELECT a.id, a.nama, MAX(oi.created_at) AS last_order
    FROM anggota a
    LEFT JOIN order_items oi ON oi.order_id = a.id
    GROUP BY a.id
    ORDER BY last_order DESC
    LIMIT 1
");
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

// Fungsi ambil item pesanan terakhir
function get_items($pdo, $anggota_id)
{
    $s = $pdo->prepare("SELECT size, qty FROM order_items WHERE order_id = :oid ORDER BY FIELD(size, 'S','M','L','XL','XXL')");
    $s->execute([':oid' => $anggota_id]);
    return $s->fetchAll();
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pesanan Anggota</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Daftar Pesanan Anggota</h3>
            <a href="index.php" class="btn btn-success">Buat Pesanan Baru</a>
        </div>

        <?php if ($success && $order_id_new): ?>
            <div class="alert alert-success">
                Pesanan untuk anggota ID #<?= htmlspecialchars($order_id_new) ?> berhasil disimpan.
            </div>
        <?php endif; ?>


        <?php if (!$anggota): ?>
            <div class="card">
                <div class="card-body text-center text-muted">Belum ada pesanan.</div>
            </div>
        <?php else: ?>
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-1">
                                <?= htmlspecialchars($anggota['nama']) ?>
                                <small class="text-muted">#<?= $anggota['id'] ?></small>
                            </h5>
                            <div class="text-muted small">
                                Waktu pesanan: <?= $anggota['last_order'] ?: 'Tidak diketahui' ?>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <table class="table table-sm mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ukuran</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $items = get_items($pdo, $anggota['id']);
                            if (count($items) === 0): ?>
                                <tr>
                                    <td colspan="2" class="text-muted">Belum ada pesanan</td>
                                </tr>
                                <?php else:
                                foreach ($items as $it): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($it['size']) ?></td>
                                        <td><?= htmlspecialchars($it['qty']) ?></td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5 py-3 border-top">
        <p class="mb-0 fw-semibold text-secondary">
            &copy; 2026 Panitia Bona Taon PTS
        </p>
    </footer>
</body>

</html>