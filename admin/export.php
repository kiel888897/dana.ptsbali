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
        SUM(CASE WHEN oi.size = 'S' THEN oi.qty ELSE 0 END) AS S,
        SUM(CASE WHEN oi.size = 'M' THEN oi.qty ELSE 0 END) AS M,
        SUM(CASE WHEN oi.size = 'L' THEN oi.qty ELSE 0 END) AS L,
        SUM(CASE WHEN oi.size = 'XL' THEN oi.qty ELSE 0 END) AS XL,
        SUM(CASE WHEN oi.size = 'XXL' THEN oi.qty ELSE 0 END) AS XXL,
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
    <title>Daftar Pesanan Baju PTS</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Daftar Pesanan Baju PTS</h3>
            <div>
                <button id="exportCSV" class="btn btn-success btn-sm me-2">Export CSV</button>
                <button id="exportPDF" class="btn btn-info btn-sm">Export PDF</button>
                <a href="index.php" class="btn btn-outline-danger btn-sm">Kembali</a>
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
                            <th>Total Qty</th>
                            <th>Total Harga (Rp)</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $row):
                            $totalQty = $row['S'] + $row['M'] + $row['L'] + $row['XL'] + $row['XXL'];
                            $totalHarga = $totalQty * $harga_perpcs;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="text-center"><?= (int)$row['S'] ?></td>
                                <td class="text-center"><?= (int)$row['M'] ?></td>
                                <td class="text-center"><?= (int)$row['L'] ?></td>
                                <td class="text-center"><?= (int)$row['XL'] ?></td>
                                <td class="text-center"><?= (int)$row['XXL'] ?></td>
                                <td class="text-center"><?= $totalQty ?></td>
                                <td class="text-center"><?= number_format($totalHarga, 0, ',', '.') ?></td>
                                <td class="text-center"><?= $row['created_at'] ? date('d M Y H:i', strtotime($row['created_at'])) : '-' ?></td>
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
                    [8, "desc"]
                ]
            });
        });

        // Export CSV
        document.getElementById("exportCSV").addEventListener("click", function() {
            let csv = [];
            const rows = document.querySelectorAll("#ordersTable tr");
            rows.forEach(row => {
                const cols = row.querySelectorAll("td, th");
                const data = Array.from(cols).slice(0, 9).map(col => col.innerText.trim());
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
                    fontSize: 9,
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