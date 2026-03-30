<?php
include '../config/app.php';

$result = getMurid($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    simpanData($conn);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Kas - Pemasukkan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">

</head>

<body>

    <div class="container-fluid">
        <div class="d-flex">
            <!-- SIDEBAR -->
            <div class="bg-white border-end vh-100" style="width: 260px;">
                <div class="p-3 border-bottom mb-3">
                    <h4 class="mb-0 text-primary fw-bold">Kas Kelas</h4>
                    <small class="text-muted">XI PPLG 2</small>
                </div>

                <ul class="nav flex-column p-2">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 text-dark" href="/index.html">
                            <i class="bi bi-grid-fill"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item mt-2">
                        <a class="nav-link active d-flex align-items-center gap-2 rounded bg-primary text-white"
                            href="kelolaKas.html">
                            <i class="bi bi-wallet2"></i>
                            Kelola Kas
                        </a>
                    </li>

                    <li class="nav-item mt-2">
                        <a class="nav-link d-flex align-items-center gap-2 text-dark" href="#">
                            <i class="bi bi-check-circle-fill"></i>
                            Status Kas
                        </a>
                    </li>

                    <li class="nav-item mt-2">
                        <a class="nav-link d-flex align-items-center gap-2 text-dark" href="#">
                            <i class="bi bi-receipt"></i>
                            Detail Kas
                        </a>
                    </li>
                </ul>

                <div class="position-absolute bottom-0 w-100 p-3">
                    <a class="nav-link text-danger d-flex align-items-center gap-2" href="#">
                        <i class="bi bi-box-arrow-left"></i>
                        <h6 class="mb-0">Keluar</h6>
                    </a>
                </div>
            </div>

            <div class="flex-grow-1 p-4">
                <h3 class="fw-bold mb-1">Pemasukkan</h3>
                <p class="text-muted mb-3">Mengatur pemasukkan dan pengeluaran kas</p>

                <div class="bg-white border rounded-pill p-1 mb-4 d-flex" style="max-width:420px">
                    <div class="flex-fill text-center toggle-btn bg-success text-white">
                        Pemasukkan
                    </div>
                    <div class="flex-fill text-center toggle-btn text-muted">
                        <a href="pengeluaran.html" class="text-decoration-none text-black">Pengeluaran</a>
                    </div>
                </div>

                <div class="card shadow-sm" style="max-width:900px">
                    <div class="card-body p-4">

                        <!-- FORM DITAMBAHKAN -->
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Pilih Murid -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" for="murid">
                                            <i class="bi bi-mortarboard"></i> Pilih murid
                                        </label>
                                        <select class="form-select" name="murid" id="murid">
                                            <option selected disabled>Pilih Murid</option>
                                            <?php
                                            if ($result->num_rows > 0) {
                                                // Loop through results and create options
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='" . $row['nisn'] . "'>" . $row['nama'] . "</option>";
                                                }
                                            } else {
                                                echo "<option value=''>No categories available</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Pilih Bulan -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" for="bulan">
                                            <i class="bi bi-calendar"></i> Pilih bulan
                                        </label>
                                        <select class="form-select" name="bulan" id="bulan">
                                            <option selected disabled>Pilih bulan</option>
                                            <option value="Januari">Januari</option>
                                            <option value="Februari">Februari</option>
                                            <option value="Maret">Maret</option>
                                            <option value="April">April</option>
                                            <option value="Mei">Mei</option>
                                            <option value="Juni">Juni</option>
                                            <option value="Juli">Juli</option>
                                            <option value="Agustus">Agustus</option>
                                            <option value="September">September</option>
                                            <option value="Oktober">Oktober</option>
                                            <option value="November">November</option>
                                            <option value="Desember">Desember</option>
                                        </select>
                                    </div>

                                    <!-- Minggu -->
                                    <div class="mb-3 d-flex gap-2">
                                        <button type="button" class="week-btn btn btn-outline-secondary" data-minggu="1">M-1</button>
                                        <button type="button" class="week-btn btn btn-outline-secondary" data-minggu="2">M-2</button>
                                        <button type="button" class="week-btn btn btn-outline-secondary" data-minggu="3">M-3</button>
                                        <button type="button" class="week-btn btn btn-outline-secondary" data-minggu="4">M-4</button>
                                    </div>

                                    <input type="hidden" name="minggu_ke" id="minggu_ke">


                                    <!-- Jumlah -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" for="jumlah">
                                            <i class="bi bi-cash"></i> Jumlah (Rp)
                                        </label>
                                        <input type="number" class="form-control" name="jumlah" id="jumlah"
                                            placeholder="Contoh: 5000">
                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" for="keterangan">Keterangan</label>
                                        <textarea class="form-control" rows="9" name="keterangan" id="keterangan"
                                            placeholder="Contoh: Kas bulan April minggu ke-2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="simpan" class="btn btn-success w-100 py-2 fw-bold rounded-pill">
                                Simpan
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const buttons = document.querySelectorAll(".week-btn");
        const hiddenInput = document.getElementById("minggu_ke");

        buttons.forEach(btn => {
            btn.addEventListener("click", function() {

                // reset semua tombol
                buttons.forEach(b => {
                    b.classList.remove("btn-success");
                    b.classList.add("btn-outline-secondary");
                });

                // ubah jadi hijau
                this.classList.remove("btn-outline-secondary");
                this.classList.add("btn-success");

                // simpan ke hidden input
                hiddenInput.value = this.dataset.minggu;
            });
        });

        document.querySelector("form").addEventListener("submit", function(e) {
            if (hiddenInput.value === "") {
                alert("Pilih minggu terlebih dahulu!");
                e.preventDefault();
            }
        });
    </script>
</body>

</html>