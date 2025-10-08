<?php

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
// index.php
require_once '../config.php';
// Ambil daftar anggota dari database
$stmt = $pdo->query("SELECT id, nama FROM anggota ORDER BY nama ASC");
$anggota_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemesanan Baju PTS</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Gambar Baju -->
                        <div class="text-center mb-4">
                            <h3 class="card-title mb-3">Form Pemesanan Baju PTS</h3>
                        </div>

                        <form id="orderForm" method="post" action="process.php">

                            <div class="mb-3">
                                <label for="customer_name_select" class="form-label">Nama</label>

                                <!-- Dropdown pilih nama -->
                                <select class="form-select" id="customer_name_select" name="customer_name_select" required>
                                    <option value="" disabled selected>Pilih nama anggota...</option>
                                    <?php foreach ($anggota_list as $a): ?>
                                        <option value="<?= htmlspecialchars($a['nama']) ?>"><?= htmlspecialchars($a['nama']) ?></option>
                                    <?php endforeach; ?>
                                    <option value="__tambah_baru__">+ Tambah Nama Baru...</option>
                                </select>

                                <!-- Input teks jika nama baru -->
                                <input type="text"
                                    class="form-control mt-2 d-none"
                                    id="customer_name_input"
                                    name="customer_name"
                                    placeholder="Masukkan nama baru">
                            </div>

                            <p class="fw-semibold">Pilih ukuran & jumlah (masukkan 0 jika tidak ingin ukuran tersebut)</p>

                            <div class="row g-2 mb-3">
                                <?php
                                $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                                foreach ($sizes as $s) {
                                    echo '<div class="col-6 col-md-4">
                              <label class="form-label d-block">' . $s . '</label>
                              <input type="number" class="form-control qty-input" name="qty[' . $s . ']" min="0" value="0">
                            </div>';
                                }
                                ?>
                            </div>

                            <div class="mb-3 form-text" id="errorText" style="color:#b02a37; display:none;">Masukkan minimal 1 qty untuk salah satu ukuran.</div>

                            <button type="submit" class="btn btn-primary">Pesan Sekarang</button>
                            <a href="index.php" class="btn btn-outline-secondary ms-2">Lihat Pesanan</a>

                        </form>

                    </div>
                </div>

                <div class="mt-3 text-muted small">
                    Note: Sistem akan menyimpan hanya ukuran yang qty > 0. Ukuran dapat diubah oleh admin.
                </div>
            </div>
        </div>
    </div>

    <script>
        // simple client-side validation: harus ada qty > 0
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const qtys = document.querySelectorAll('.qty-input');
            let total = 0;
            qtys.forEach(i => {
                total += Number(i.value) || 0;
            });
            if (total <= 0) {
                e.preventDefault();
                document.getElementById('errorText').style.display = 'block';
            } else {
                document.getElementById('errorText').style.display = 'none';
            }
        });
    </script>

    <script>
        document.getElementById('customer_name_select').addEventListener('change', function() {
            const inputBaru = document.getElementById('customer_name_input');

            if (this.value === '__tambah_baru__') {
                inputBaru.classList.remove('d-none');
                inputBaru.setAttribute('required', 'required');
                inputBaru.focus();
            } else {
                inputBaru.classList.add('d-none');
                inputBaru.removeAttribute('required');
                inputBaru.value = this.value; // isi otomatis nama yg dipilih
            }
        });
    </script>
</body>

</html>