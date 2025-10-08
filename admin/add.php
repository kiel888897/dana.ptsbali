<?php
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
    <style>
        body {
            background: linear-gradient(to bottom right, #e0f7fa, #ffffff);
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 1rem;
        }

        h3 {
            font-weight: 600;
            color: #007b83;
        }

        .form-label {
            font-weight: 500;
        }

        .qty-input {
            text-align: center;
            border: 1px solid #ccc;
            transition: border-color 0.2s;
        }

        .qty-input:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 5px rgba(0, 188, 212, 0.4);
        }

        .info-box {
            background: #f1f8e9;
            border-left: 4px solid #8bc34a;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }

        .table th {
            background-color: #009688;
            color: white;
        }

        .table td strong {
            color: #00796b;
        }

        .kids-section {
            margin-top: 2rem;
        }

        .btn-primary {
            background-color: #00796b;
            border: none;
        }

        .btn-primary:hover {
            background-color: #009688;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm p-4">
                    <div class="text-center mb-4">
                        <h3 class="card-title mb-3">Form Pemesanan Baju PTS</h3>
                        <img src="../1.png" alt="Desain Baju PTS" class="img-fluid rounded shadow-sm" style="max-width: 300px;">
                    </div>

                    <div class="info-box mb-3">
                        💰 Harga per pcs: <strong>Rp 100.000 (All Size)</strong><br>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                💳 Pembayaran ke: <strong>BRI 0368 0105 9546 508 A.n. Sarah Andriani Putri</strong>
                            </div>
                            <button type="button" id="copyRekening" class="btn btn-sm btn-outline-primary">
                                Copy
                            </button>
                        </div>
                    </div>
                    <form id="orderForm" method="post" action="process.php">

                        <div class="mb-3">
                            <label for="customer_name_select" class="form-label">Nama</label>
                            <select class="form-select" id="customer_name_select" name="customer_name_select" required>
                                <option value="" disabled selected>Pilih nama ...</option>
                                <?php foreach ($anggota_list as $a): ?>
                                    <option value="<?= htmlspecialchars($a['nama']) ?>"><?= htmlspecialchars($a['nama']) ?></option>
                                <?php endforeach; ?>
                                <option value="__tambah_baru__">+ Tambah Nama Baru...</option>
                            </select>

                            <input type="text"
                                class="form-control mt-2 d-none"
                                id="customer_name_input"
                                name="customer_name"
                                placeholder="Masukkan nama baru">
                        </div>

                        <p class="fw-semibold mt-4 mb-2">Pilih ukuran & jumlah (isi 0 jika tidak pesan ukuran tersebut)</p>

                        <!-- Ukuran Dewasa -->
                        <div class="row g-2 mb-4">
                            <?php
                            $sizes = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
                            foreach ($sizes as $s) {
                                echo '
                  <div class="col-6 col-md-4">
                    <label class="form-label d-block">' . $s . '</label>
                    <input type="number" class="form-control qty-input" name="qty[' . $s . ']" min="0" value="0">
                  </div>';
                            }
                            ?>
                        </div>

                        <!-- Ukuran Anak -->
                        <div class="kids-section">
                            <h6 class="fw-semibold mb-2 text-success">Ukuran Anak-anak (Kids)</h6>
                            <div class="row g-2 mb-3">
                                <?php
                                $kids = ['KIDS S', 'KIDS M', 'KIDS L', 'KIDS XL', 'KIDS XXL'];
                                foreach ($kids as $k) {
                                    echo '
                    <div class="col-6 col-md-4">
                      <label class="form-label d-block">' . $k . '</label>
                      <input type="number" class="form-control qty-input" name="qty[' . $k . ']" min="0" value="0">
                    </div>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="mb-3 form-text text-danger" id="errorText" style="display:none;">
                            Masukkan minimal 1 qty untuk salah satu ukuran.
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary px-4">Pesan Sekarang</button>
                            <a href="index.php" class="btn btn-outline-secondary">Lihat Pesanan</a>
                        </div>
                    </form>

                    <!-- Tabel ukuran -->
                    <div class="mt-5">
                        <h6 class="fw-bold text-center mb-3">Tabel Ukuran Dewasa</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>Ukuran</th>
                                        <th>Lingkar Dada (cm)</th>
                                        <th>Panjang Baju (cm)</th>
                                        <th>Lebar Bahu (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>S</strong></td>
                                        <td>46–48</td>
                                        <td>66–68</td>
                                        <td>40–42</td>
                                    </tr>
                                    <tr>
                                        <td><strong>M</strong></td>
                                        <td>50–52</td>
                                        <td>68–70</td>
                                        <td>42–44</td>
                                    </tr>
                                    <tr>
                                        <td><strong>L</strong></td>
                                        <td>54–56</td>
                                        <td>70–72</td>
                                        <td>44–46</td>
                                    </tr>
                                    <tr>
                                        <td><strong>XL</strong></td>
                                        <td>58–60</td>
                                        <td>72–74</td>
                                        <td>46–48</td>
                                    </tr>
                                    <tr>
                                        <td><strong>XXL</strong></td>
                                        <td>62–64</td>
                                        <td>74–76</td>
                                        <td>48–50</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="fw-bold text-center mb-3 text-success">Tabel Ukuran Anak-anak (Kids)</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>Ukuran</th>
                                        <th>Umur (tahun)</th>
                                        <th>Lingkar Dada (cm)</th>
                                        <th>Panjang Baju (cm)</th>
                                        <th>Lebar Bahu (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>KIDS S</strong></td>
                                        <td>4–5</td>
                                        <td>36–38</td>
                                        <td>42–44</td>
                                        <td>30–32</td>
                                    </tr>
                                    <tr>
                                        <td><strong>KIDS M</strong></td>
                                        <td>6–7</td>
                                        <td>40–42</td>
                                        <td>46–48</td>
                                        <td>32–34</td>
                                    </tr>
                                    <tr>
                                        <td><strong>KIDS L</strong></td>
                                        <td>8–9</td>
                                        <td>44–46</td>
                                        <td>50–52</td>
                                        <td>34–36</td>
                                    </tr>
                                    <tr>
                                        <td><strong>KIDS XL</strong></td>
                                        <td>10–11</td>
                                        <td>48–50</td>
                                        <td>54–56</td>
                                        <td>36–38</td>
                                    </tr>
                                    <tr>
                                        <td><strong>KIDS XXL</strong></td>
                                        <td>12–13</td>
                                        <td>52–54</td>
                                        <td>58–60</td>
                                        <td>38–40</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        // Validasi minimal 1 qty > 0
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const qtys = document.querySelectorAll('.qty-input');
            let total = 0;
            qtys.forEach(i => total += Number(i.value) || 0);
            if (total <= 0) {
                e.preventDefault();
                document.getElementById('errorText').style.display = 'block';
            } else {
                document.getElementById('errorText').style.display = 'none';
            }
        });

        // Input nama baru
        document.getElementById('customer_name_select').addEventListener('change', function() {
            const inputBaru = document.getElementById('customer_name_input');
            if (this.value === '__tambah_baru__') {
                inputBaru.classList.remove('d-none');
                inputBaru.setAttribute('required', 'required');
                inputBaru.value = '';
                inputBaru.focus();
            } else {
                inputBaru.classList.add('d-none');
                inputBaru.removeAttribute('required');
                inputBaru.value = this.value;
            }
        });
    </script>
    <script>
        document.getElementById('copyRekening').addEventListener('click', function() {
            const textToCopy = "036801059546508";
            navigator.clipboard.writeText(textToCopy).then(() => {
                const btn = this;
                btn.innerText = "Copied!";
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                setTimeout(() => {
                    btn.innerText = "Copy";
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            }).catch(err => {
                alert("Gagal menyalin teks 😢");
            });
        });
    </script>

</body>

</html>