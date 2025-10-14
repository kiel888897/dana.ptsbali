<?php
require_once 'config.php';

// Ambil semua orders dan item terkait
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
            <h3>📋 Daftar Pesanan Baju PTS</h3>
            <a href="index.php" class="btn btn-success">+ Tambah Pesanan</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ordersTable" class="table table-striped table-bordered align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th rowspan="2" class="align-middle">Nama</th>
                                <th colspan="6">Ukuran Dewasa</th>
                                <th colspan="5">Ukuran Anak (Kids)</th>
                                <th rowspan="2" class="align-middle">Total (pcs)</th>
                            </tr>
                            <tr>
                                <th>S</th>
                                <th>M</th>
                                <th>L</th>
                                <th>XL</th>
                                <th>XXL</th>
                                <th>XXXL</th>
                                <th>K-S</th>
                                <th>K-M</th>
                                <th>K-L</th>
                                <th>K-XL</th>
                                <th>K-XXL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $row): ?>
                                <?php
                                // Semua ukuran default 0
                                $sizes = [
                                    'S' => 0,
                                    'M' => 0,
                                    'L' => 0,
                                    'XL' => 0,
                                    'XXL' => 0,
                                    'XXXL' => 0,
                                    'K-S' => 0,
                                    'K-M' => 0,
                                    'K-L' => 0,
                                    'K-XL' => 0,
                                    'K-XXL' => 0
                                ];

                                if (!empty($row['items'])) {
                                    $parts = explode(',', $row['items']);
                                    foreach ($parts as $p) {
                                        $pair = explode(':', $p);
                                        if (count($pair) === 2) {
                                            $size = strtoupper(trim($pair[0]));
                                            $qty = (int)$pair[1];

                                            // Normalisasi nama ukuran agar cocok dengan array
                                            $size = str_replace('KIDS ', 'K-', $size);
                                            $size = str_replace('KID ', 'K-', $size); // antisipasi variasi

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

                                    <?php foreach (['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'K-S', 'K-M', 'K-L', 'K-XL', 'K-XXL'] as $s): ?>
                                        <td class="text-center <?= $sizes[$s] != 0 ? 'text-success fw-bold' : '' ?>">
                                            <?= $sizes[$s] ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td class="text-center fw-bold <?= $total_pesanan != 0 ? 'text-success' : '' ?>">
                                        <?= $total_pesanan ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
                    [0, "asc"]
                ]
            });
        });
    </script>

    <!-- Footer -->
    <footer class="text-center mt-5 py-3 border-top">
        <p class="mb-0 fw-semibold text-secondary">
            &copy; 2026 Panitia Bona Taon PTS
        </p>
    </footer>
</body>

</html>