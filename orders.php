<?php
require_once 'config.php';

// Ambil semua orders dan item terkait (terbaru dulu)
$stmt = $pdo->query("
    SELECT o.id, o.nama, 
           GROUP_CONCAT(CONCAT(oi.size, ':', oi.qty) SEPARATOR ',') AS items
    FROM anggota o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.nama ASC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pesanan Baju PTS</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Pesanan Baju PTS </h3>
            <div>
                <a href="index.php" class="btn btn-success me-2">+ Tambah Pesanan</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table id="ordersTable" class="table table-striped table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Nama</th>
                            <th>S</th>
                            <th>M</th>
                            <th>L</th>
                            <th>XL</th>
                            <th>XXL</th>
                            <th>Total (pcs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $row): ?>
                            <?php
                            // Default semua ukuran = 0
                            $sizes = ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0, 'XXL' => 0];

                            if (!empty($row['items'])) {
                                $parts = explode(',', $row['items']);
                                foreach ($parts as $p) {
                                    $pair = explode(':', $p);
                                    if (count($pair) === 2) {
                                        $size = strtoupper(trim($pair[0]));
                                        $qty = (int)$pair[1];
                                        if (isset($sizes[$size])) {
                                            $sizes[$size] += $qty;
                                        }
                                    }
                                }
                            }

                            $total_pesanan = array_sum($sizes);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="text-center"><?= $sizes['S'] ?></td>
                                <td class="text-center"><?= $sizes['M'] ?></td>
                                <td class="text-center"><?= $sizes['L'] ?></td>
                                <td class="text-center"><?= $sizes['XL'] ?></td>
                                <td class="text-center"><?= $sizes['XXL'] ?></td>
                                <td class="text-center fw-bold"><?= $total_pesanan ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#ordersTable').DataTable({
                paging: false,
                info: false,
                searching: true,
                order: [
                    [6, "asc"]
                ] // Urut berdasarkan tanggal terbaru
            });
        });
    </script>

</body>

</html>