<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../config.php';

$harga_perpcs = 100000; // harga satuan per pcs

$stmt = $pdo->query("
    SELECT 
        a.id, 
        a.nama,
        -- Ukuran Dewasa
        SUM(CASE WHEN UPPER(oi.size) = 'S' THEN oi.qty ELSE 0 END) AS S,
        SUM(CASE WHEN UPPER(oi.size) = 'M' THEN oi.qty ELSE 0 END) AS M,
        SUM(CASE WHEN UPPER(oi.size) = 'L' THEN oi.qty ELSE 0 END) AS L,
        SUM(CASE WHEN UPPER(oi.size) = 'XL' THEN oi.qty ELSE 0 END) AS XL,
        SUM(CASE WHEN UPPER(oi.size) = 'XXL' THEN oi.qty ELSE 0 END) AS XXL,
        SUM(CASE WHEN UPPER(oi.size) = 'XXXL' THEN oi.qty ELSE 0 END) AS XXXL,

        -- Ukuran Anak (Kids)
        SUM(CASE WHEN UPPER(oi.size) IN ('KIDS S','K-S') THEN oi.qty ELSE 0 END) AS KS,
        SUM(CASE WHEN UPPER(oi.size) IN ('KIDS M','K-M') THEN oi.qty ELSE 0 END) AS KM,
        SUM(CASE WHEN UPPER(oi.size) IN ('KIDS L','K-L') THEN oi.qty ELSE 0 END) AS KL,
        SUM(CASE WHEN UPPER(oi.size) IN ('KIDS XL','K-XL') THEN oi.qty ELSE 0 END) AS KXL,
        SUM(CASE WHEN UPPER(oi.size) IN ('KIDS XXL','K-XXL') THEN oi.qty ELSE 0 END) AS KXXL,

        MAX(oi.created_at) AS created_at
    FROM anggota a
    INNER JOIN order_items oi ON oi.order_id = a.id
    GROUP BY a.id, a.nama
    ORDER BY created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_all = [
    'S' => 0,
    'M' => 0,
    'L' => 0,
    'XL' => 0,
    'XXL' => 0,
    'XXXL' => 0,
    'KS' => 0,
    'KM' => 0,
    'KL' => 0,
    'KXL' => 0,
    'KXXL' => 0
];
$total_harga_semua = 0;

foreach ($orders as $o) {
    foreach ($total_all as $size => $_) {
        $total_all[$size] += (int)$o[$size];
        $total_harga_semua += (int)$o[$size] * $harga_perpcs;
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pesanan Baju Anggota</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.3/css/buttons.bootstrap5.min.css">
    <style>
        table th,
        table td {
            text-align: center;
            vertical-align: middle;
        }

        table td.name-col {
            text-align: left;
            font-weight: 500;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        .total-box {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        /* 🌿 Warna lembut untuk kolom Kids */
        .kids-col {
            background-color: #e8f8ec !important;
        }

        thead tr:nth-child(2) th.kids-head {
            background-color: #d1f1da !important;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Daftar Pesanan Baju PTS</h3>
            <div>
                <a href="add.php" class="btn btn-success me-2">+ Buat Pesanan Baru</a>
                <a href="export.php" class="btn btn-outline-warning me-2">Export data</a>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table id="ordersTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2">Nama</th>
                            <th colspan="6">Ukuran Dewasa</th>
                            <th colspan="5" class="kids-head">Ukuran Anak (Kids)</th>
                            <th rowspan="2">Total Qty</th>
                            <th rowspan="2">Total Harga (Rp)</th>
                            <th rowspan="2">Tanggal</th>
                            <th rowspan="2">Aksi</th>
                        </tr>
                        <tr>
                            <th>S</th>
                            <th>M</th>
                            <th>L</th>
                            <th>XL</th>
                            <th>XXL</th>
                            <th>XXXL</th>
                            <th class="kids-head">K-S</th>
                            <th class="kids-head">K-M</th>
                            <th class="kids-head">K-L</th>
                            <th class="kids-head">K-XL</th>
                            <th class="kids-head">K-XXL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="15" class="text-center text-muted">Belum ada pesanan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <?php
                                $total_qty = $o['S'] + $o['M'] + $o['L'] + $o['XL'] + $o['XXL'] + $o['XXXL'] +
                                    $o['KS'] + $o['KM'] + $o['KL'] + $o['KXL'] + $o['KXXL'];
                                $total_harga = $total_qty * $harga_perpcs;
                                ?>
                                <tr>
                                    <td class="name-col"><?= htmlspecialchars($o['nama']) ?></td>
                                    <?php foreach (['S', 'M', 'L', 'XL', 'XXL', 'XXXL'] as $sz): ?>
                                        <td class="<?= $o[$sz] > 0 ? 'text-success fw-bold' : '' ?>">
                                            <?= $o[$sz] ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <?php foreach (['KS', 'KM', 'KL', 'KXL', 'KXXL'] as $sz): ?>
                                        <td class="kids-col <?= $o[$sz] > 0 ? 'text-success fw-bold' : '' ?>">
                                            <?= $o[$sz] ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="fw-bold text-success"><?= $total_qty ?></td>
                                    <td><?= number_format($total_harga, 0, ',', '.') ?></td>
                                    <td><?= $o['created_at'] ? date('d M Y H:i', strtotime($o['created_at'])) : '-' ?></td>
                                    <td>
                                        <form method="post" action="delete_order.php" class="d-inline" onsubmit="return confirm('Hapus pesanan ini?');">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="total-box mt-4">
                    <h6 class="fw-bold mb-2">Total Keseluruhan Semua Pesanan:</h6>
                    <div class="row text-center mb-3">
                        <?php foreach ($total_all as $size => $total): ?>
                            <div class="col <?= str_starts_with($size, 'K') ? 'kids-col' : '' ?>">
                                <strong><?= $size ?></strong><br>
                                <span><?= $total ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center">
                        <h5 class="fw-bold text-success">
                            Total Nilai Semua Pesanan: Rp <?= number_format($total_harga_semua, 0, ',', '.') ?>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        $(document).ready(function() {
            $('#ordersTable').DataTable({
                pageLength: 10,
                order: [
                    [12, 'desc']
                ],
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        text: 'Copy',
                        className: 'btn btn-outline-secondary btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        className: 'btn btn-outline-success btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-outline-primary btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        className: 'btn btn-outline-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-outline-dark btn-sm'
                    }
                ]
            });
        });
    </script>
</body>

</html>