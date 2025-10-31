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

    <style>
        body {
            font-size: 13px;
        }

        table {
            font-size: 12px;
        }

        /* Tambahkan / ganti bagian ini di style */
        @media print {
            @page {
                size: A4 landscape;
                /* orientasi mendatar */
                margin: 0.5cm;
                /* kecilkan margin default browser */
            }

            body {
                font-size: 9px;
                margin: 0;
                padding: 0;
            }

            .container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0.2cm !important;
            }

            table {
                width: 100% !important;
                font-size: 9px;
            }

            .card,
            .card-body {
                border: none !important;
                box-shadow: none !important;
                margin: 0;
                padding: 0;
            }

            .btn-print {
                display: none !important;
            }

            .table {
                border-collapse: collapse !important;
            }
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold">📋 Daftar Pesanan Baju Pomparan Tuan Sihubil</h5>
            <button onclick="window.print()" class="btn btn-sm btn-outline-primary btn-print">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ordersTable" class="table table-bordered align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th rowspan="2" class="align-middle">No</th>
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
                            <?php
                            $total_all = [
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
                            $no = 1;
                            foreach ($orders as $row):
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
                                            $size = str_replace(['KIDS ', 'KID '], 'K-', $size);
                                            if (isset($sizes[$size])) {
                                                $sizes[$size] += $qty;
                                            }
                                        }
                                    }
                                }

                                $total_pesanan = array_sum($sizes);

                                // Tambah ke total keseluruhan
                                foreach ($sizes as $k => $v) {
                                    $total_all[$k] += $v;
                                }
                                $total_all['TOTAL'] = ($total_all['TOTAL'] ?? 0) + $total_pesanan;
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="<?= $total_pesanan > 0 ? 'text-success fw-bold' : '' ?>">
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </td>

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
                        <tfoot class="table-secondary fw-bold text-center">
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <?php foreach (['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'K-S', 'K-M', 'K-L', 'K-XL', 'K-XXL'] as $s): ?>
                                    <td><?= $total_all[$s] ?></td>
                                <?php endforeach; ?>
                                <td><?= $total_all['TOTAL'] ?? 0 ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-3 py-2 border-top small text-secondary">
        &copy; 2026 Panitia Bona Taon PTS
    </footer>

</body>

</html>