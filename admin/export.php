<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../config.php';

$harga_perpcs = 100000;

// Ambil semua anggota yang punya pesanan
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
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Export Data Pesanan Baju PTS</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        table th,
        table td {
            text-align: center;
            vertical-align: middle;
        }

        .kids-col {
            background-color: #e8f8ec !important;
        }

        .kids-head {
            background-color: #d1f1da !important;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Export Data Pesanan Baju PTS</h3>
            <div>
                <button id="exportCSV" class="btn btn-success btn-sm me-2">Export CSV</button>
                <button id="exportPDF" class="btn btn-info btn-sm me-2">Export PDF</button>
                <a href="index.php" class="btn btn-outline-danger btn-sm">Kembali</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table id="ordersTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Nama</th>
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
                            <th>Total Qty</th>
                            <th>Total Harga (Rp)</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $row):
                            $totalQty = $row['S'] + $row['M'] + $row['L'] + $row['XL'] + $row['XXL'] + $row['XXXL'] +
                                $row['KS'] + $row['KM'] + $row['KL'] + $row['KXL'] + $row['KXXL'];
                            $totalHarga = $totalQty * $harga_perpcs;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= $row['S'] ?></td>
                                <td><?= $row['M'] ?></td>
                                <td><?= $row['L'] ?></td>
                                <td><?= $row['XL'] ?></td>
                                <td><?= $row['XXL'] ?></td>
                                <td><?= $row['XXXL'] ?></td>
                                <td class="kids-col"><?= $row['KS'] ?></td>
                                <td class="kids-col"><?= $row['KM'] ?></td>
                                <td class="kids-col"><?= $row['KL'] ?></td>
                                <td class="kids-col"><?= $row['KXL'] ?></td>
                                <td class="kids-col"><?= $row['KXXL'] ?></td>
                                <td class="fw-bold text-success"><?= $totalQty ?></td>
                                <td><?= number_format($totalHarga, 0, ',', '.') ?></td>
                                <td><?= $row['created_at'] ? date('d M Y H:i', strtotime($row['created_at'])) : '-' ?></td>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#ordersTable').DataTable({
                paging: false,
                info: false,
                searching: true,
                order: [
                    [14, "desc"]
                ]
            });
        });

        // Export CSV
        document.getElementById("exportCSV").addEventListener("click", function() {
            let csv = [];
            const rows = document.querySelectorAll("#ordersTable tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td, th");
                const data = Array.from(cols).map(col => col.innerText.trim());
                csv.push(data.join(";"));
            });
            const csvContent = "\uFEFF" + csv.join("\n");
            const csvFile = new Blob([csvContent], {
                type: "text/csv;charset=utf-8;"
            });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(csvFile);
            a.download = "pesanan_baju_pts.csv";
            a.click();
        });

        // Export PDF
        document.getElementById("exportPDF").addEventListener("click", function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF("l", "pt", "a4");
            doc.text("Daftar Pesanan Baju PTS", 40, 40);
            doc.autoTable({
                html: "#ordersTable",
                startY: 60,
                styles: {
                    fontSize: 8,
                    halign: 'center',
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: [40, 40, 40],
                    textColor: 255
                },
                theme: 'grid'
            });
            doc.save("pesanan_baju_pts.pdf");
        });
    </script>
</body>

</html>