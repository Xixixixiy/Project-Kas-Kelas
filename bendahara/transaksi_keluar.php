<?php
session_start();
include "../config/database.php"; // Sesuaikan path config kamu

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'bendahara') {
    header("Location: ../login.php");
    exit;
}

// --- 2. LOGIKA HITUNG SALDO ---
// Kategori 1,2,3 adalah MASUK. Kategori 4,5,6 adalah KELUAR.
$q_masuk = mysqli_query($conn, "SELECT SUM(nominal) as total FROM transaksi WHERE id_kategori IN (1,2,3)");
$q_keluar = mysqli_query($conn, "SELECT SUM(nominal) as total FROM transaksi WHERE id_kategori IN (4,5,6)");

$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;
$saldo_saat_ini = $total_masuk - $total_keluar;

// Ambil daftar kategori khusus pengeluaran (4,5,6) untuk dropdown
$kategori_keluar = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori IN (4,5,6)");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Kas - Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <?php include "../layout/navbar.php"; ?>

    <div class="container mt-4">
        <h2 class="fw-bold text-danger">Pengeluaran Kas</h2>
        <p class="text-muted small">Mencatat biaya keluar dari kas kelas</p>

        <div class="bg-white p-1 d-flex mb-4 shadow-sm" style="border-radius: 50px;">
            <a href="transaksi_masuk.php" class="btn btn-light w-50 fw-bold" style="border-radius: 50px;">Pemasukkan</a>
            <a href="transaksi_keluar.php" class="btn btn-danger w-50 fw-bold" style="border-radius: 50px;">Pengeluaran</a>
        </div>

        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <div class="alert alert-danger border-0 d-flex justify-content-between align-items-center mb-4" style="border-radius: 15px;">
                <span><i class="bi bi-wallet2 me-2"></i> Saldo Kas Saat Ini:</span>
                <strong class="fs-5">Rp <?= number_format($saldo_saat_ini, 0, ',', '.') ?></strong>
            </div>

            <form action="actions/proses_keluar.php" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Kategori Pengeluaran</label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php while ($kat = mysqli_fetch_assoc($kategori_keluar)): ?>
                                    <option value="<?= $kat['id_kategori'] ?>"><?= $kat['nama_kategori'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nominal Pengeluaran (Rp)</label>
                            <input type="number" name="nominal" id="inputNominal" class="form-control" placeholder="Contoh: 50000" required>
                            <div class="invalid-feedback">Saldo tidak mencukupi!</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Keterangan / Keperluan</label>
                            <textarea name="keterangan" class="form-control" style="height: 120px;" placeholder="Contoh: Beli sapu & ember kelas" required></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" id="btnSimpan" class="btn btn-danger w-100 fw-bold mt-2 p-3 shadow-sm" style="border-radius: 12px;">
                    SIMPAN PENGELUARAN
                </button>
            </form>
        </div>
    </div>

    <script>
        const inputNominal = document.getElementById('inputNominal');
        const btnSimpan = document.getElementById('btnSimpan');
        const saldoSekarang = <?= $saldo_saat_ini ?>;

        inputNominal.addEventListener('input', function() {
            const nominal = parseInt(this.value) || 0;
            if (nominal > saldoSekarang) {
                this.classList.add('is-invalid');
                btnSimpan.disabled = true;
                btnSimpan.innerText = "SALDO TIDAK CUKUP";
            } else {
                this.classList.remove('is-invalid');
                btnSimpan.disabled = false;
                btnSimpan.innerText = "SIMPAN PENGELUARAN";
            }
        });
    </script>
</body>

</html>