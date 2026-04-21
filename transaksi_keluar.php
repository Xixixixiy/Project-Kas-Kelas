<?php
session_start();
include "../connection/connection.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'bendahara') {
    header("Location: ../login.php");
    exit;
}

// --- LOGIKA HITUNG SALDO (CEK KEMAMPUAN BAYAR) ---
// Sistem menghitung total uang yang ada sekarang agar tidak terjadi pengeluaran yang melebihi saldo
$q_masuk = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Masuk'");
$q_keluar = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Keluar'");

$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;
$saldo_saat_ini = $total_masuk - $total_keluar;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Kas - Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="#">Kas Kelas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../dashboard_bendahara.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="pemasukkan.php">Kelola Kas</a></li>
                    <li class="nav-item"><a class="nav-link" href="../status_kas.php">Status Kas</a></li>
                    <li class="nav-item"><a class="nav-link" href="../detail_kas.php">Detail Kas</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted"><?php echo $_SESSION['nama']; ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="fw-bold">Pengeluaran</h2>
        <p class="text-muted small">Mencatat biaya keluar dari kas kelas</p>

        <div class="bg-light p-1 d-flex mb-4" style="border-radius: 50px; border: 1px solid #ddd;">
            <a href="pemasukkan.php" class="btn btn-light w-50 fw-bold" style="border-radius: 50px;">Pemasukkan</a>
            <a href="pengeluaran.php" class="btn btn-danger w-50 fw-bold" style="border-radius: 50px;">Pengeluaran</a>
        </div>

        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <div class="alert alert-danger border-0 shadow-sm d-flex justify-content-between align-items-center mb-4" style="border-radius: 15px;">
                <span><i class="bi bi-wallet2 me-2"></i> Saldo Kas Saat Ini:</span>
                <strong class="fs-5">Rp <?= number_format($saldo_saat_ini, 0, ',', '.') ?></strong>
            </div>

            <form action="proses_keluar.php" method="POST">
                <input type="hidden" name="jenis" value="Keluar">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-cash-stack"></i> Jumlah Pengeluaran (Rp)</label>
                            <input type="number" name="jumlah" id="inputJumlah" class="form-control border-secondary-subtle" placeholder="Contoh: 20000" required>
                            <div id="feedbackSaldo" class="invalid-feedback">
                                Maaf, saldo tidak mencukupi untuk pengeluaran ini.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-chat-left-text"></i> Keterangan / Keperluan</label>
                            <textarea name="keterangan" class="form-control border-secondary-subtle" style="height: 120px;" placeholder="Tulis rincian pengeluaran (Contoh: Beli sapu & pengki)" required></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" id="btnSimpan" class="btn btn-danger w-100 fw-bold mt-4 p-3 shadow-sm" style="border-radius: 12px;">
                    SIMPAN PENGELUARAN
                </button>
            </form>
        </div>
    </div>

    <!-- --- JAVASCRIPT VALIDATION (ALGORITMA PENCEGAHAN) --- -->
    <script>
        const inputJumlah = document.getElementById('inputJumlah');
        const btnSimpan = document.getElementById('btnSimpan');
        const saldoSekarang = <?= $saldo_saat_ini ?>; // Mengambil angka saldo dari PHP ke JS

        inputJumlah.addEventListener('input', function() {
            const nominal = parseInt(this.value);

            // Algoritma: Jika nominal yang diketik > saldo, tombol simpan mati (disable)
            if (nominal > saldoSekarang) {
                this.classList.add('is-invalid'); // Memberi warna merah pada input
                btnSimpan.disabled = true;
                btnSimpan.innerText = "SALDO TIDAK CUKUP";
            } else {
                this.classList.remove('is-invalid');
                btnSimpan.disabled = false;
                btnSimpan.innerText = "SIMPAN PENGELUARAN";
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>