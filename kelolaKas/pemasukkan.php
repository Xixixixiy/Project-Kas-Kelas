<?php
session_start();
include "../connection/connection.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'bendahara') {
    header("Location: ../login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// ambil data murid
$murid = mysqli_query($conn, "
    SELECT * FROM murid WHERE id_kelas = '$id_kelas'
");

// Logika cek minggu yang sudah dibayar
$sudah_bayar = [];
$murid_terpilih = $_GET['id_murid'] ?? '';
$bulan_terpilih = $_GET['bulan'] ?? '';

if ($murid_terpilih && $bulan_terpilih) {
    $cek_minggu = mysqli_query($conn, "SELECT minggu FROM transaksi WHERE id_murid = '$murid_terpilih' AND bulan = '$bulan_terpilih' AND jenis = 'Masuk'");
    while ($row = mysqli_fetch_assoc($cek_minggu)) {
        $sudah_bayar[] = $row['minggu'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Kas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container-fluid">

            <a class="navbar-brand fw-bold text-primary" href="#">
                Kas Kelas
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard_bendahara.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="kelolaKas/pemasukkan.php">
                            Kelola Kas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            Status Kas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            Detail Kas
                        </a>
                    </li>

                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">
                        <?php echo $_SESSION['nama']; ?>
                    </span>

                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        Logout
                    </a>
                </div>

            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="fw-bold">Pemasukkan</h2>
        <p class="text-muted small">Mengatur pemasukkan dan pengeluaran kas</p>

        <div class="bg-light p-1 d-flex mb-4" style="border-radius: 50px; border: 1px solid #ddd;">
            <button id="btnMasuk" class="btn btn-success w-50 fw-bold" style="border-radius: 50px;">
                Pemasukkan
            </button>
            <button id="btnKeluar" class="btn btn-light w-50 fw-bold" style="border-radius: 50px;">
                Pengeluaran
            </button>
        </div>

        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <form action="proses_tambah.php" method="POST">
                <input type="hidden" name="jenis" id="inputJenis" value="Masuk">

                <div class="row">
                    <div class="col-md-6">
                        <div id="groupMurid" class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-mortarboard"></i> Pilih murid</label>
                            <select name="id_murid" id="selectMurid" class="form-select border-secondary-subtle">
                                <option value="">-- Pilih Murid --</option>
                                <?php while ($m = mysqli_fetch_assoc($murid)) { ?>
                                    <option value="<?= $m['id_murid'] ?>" <?= ($murid_terpilih == $m['id_murid']) ? 'selected' : '' ?>><?= $m['nama'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div id="groupWaktu" class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-calendar3"></i> Pilih Bulan & Minggu</label>

                            <div class="row g-2 mb-3">
                                <?php
                                $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                foreach ($bulan_list as $bln) : ?>
                                    <div class="col-3">
                                        <button type="button" class="btn <?= ($bulan_terpilih == $bln) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm w-100 btn-bulan" data-bulan="<?= $bln ?>">
                                            <?= substr($bln, 0, 3) ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="bulan" id="inputBulan" value="<?= $bulan_terpilih ?>" required>

                            <div class="d-flex gap-2">
                                <?php
                                $m_list = ['M-1', 'M-2', 'M-3', 'M-4'];
                                foreach ($m_list as $minggu_val) :
                                    $is_paid = in_array($minggu_val, $sudah_bayar);
                                ?>
                                    <button type="button"
                                        class="btn <?= $is_paid ? 'btn-success text-white disabled' : 'btn-light border' ?> btn-minggu"
                                        data-m="<?= $minggu_val ?>"
                                        <?= $is_paid ? 'disabled' : '' ?>>
                                        <?= $minggu_val ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="minggu" id="inputMinggu">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-cash"></i> Jumlah (Rp)</label>
                            <input type="number" name="jumlah" class="form-control border-secondary-subtle" placeholder="Contoh: 5000" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3 h-100">
                            <label class="form-label small fw-bold">Keterangan</label>
                            <textarea name="keterangan" class="form-control border-secondary-subtle" style="height: 200px;" placeholder="Catatan transaksi..."></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 fw-bold mt-4 p-3" style="border-radius: 12px;">
                    SIMPAN
                </button>
            </form>
        </div>
    </div>

    <script>
        // 1. LOGIKA MINGGU BERANTAI
        const btnsMinggu = document.querySelectorAll('.btn-minggu');
        const inputMinggu = document.getElementById('inputMinggu');

        btnsMinggu.forEach((btn, index) => {
            btn.addEventListener('click', () => {
                if (btn.disabled) return;

                btnsMinggu.forEach(b => {
                    if (!b.disabled) {
                        b.classList.remove('btn-success', 'text-white');
                        b.classList.add('btn-light');
                    }
                });

                for (let i = 0; i <= index; i++) {
                    btnsMinggu[i].classList.remove('btn-light');
                    btnsMinggu[i].classList.add('btn-success', 'text-white');
                }

                inputMinggu.value = btn.getAttribute('data-m');
            });
        });

        // 2. LOGIKA PINDAH FORM (TOGGLE)
        const btnMasuk = document.getElementById('btnMasuk');
        const btnKeluar = document.getElementById('btnKeluar');
        const groupMurid = document.getElementById('groupMurid');
        const groupWaktu = document.getElementById('groupWaktu');
        const inputJenis = document.getElementById('inputJenis');

        btnKeluar.addEventListener('click', () => {
            btnKeluar.className = 'btn btn-danger w-50 fw-bold';
            btnMasuk.className = 'btn btn-light w-50 fw-bold';
            groupMurid.style.display = 'none';
            groupWaktu.style.display = 'none';
            inputJenis.value = 'Keluar';
        });

        btnMasuk.addEventListener('click', () => {
            btnMasuk.className = 'btn btn-success w-50 fw-bold';
            btnKeluar.className = 'btn btn-light w-50 fw-bold';
            groupMurid.style.display = 'block';
            groupWaktu.style.display = 'block';
            inputJenis.value = 'Masuk';
        });

        // Logika Pilih Bulan
        const btnsBulan = document.querySelectorAll('.btn-bulan');
        const inputBulan = document.getElementById('inputBulan');
        const selectMurid = document.getElementById('selectMurid');

        btnsBulan.forEach(btn => {
            btn.addEventListener('click', () => {
                const selectedBulan = btn.getAttribute('data-bulan');
                const selectedMurid = selectMurid.value;

                if (selectedMurid) {
                    // Refresh halaman untuk cek minggu yang sudah dibayar
                    window.location.href = `pemasukkan.php?id_murid=${selectedMurid}&bulan=${selectedBulan}`;
                } else {
                    alert("Pilih murid terlebih dahulu!");
                }
            });
        });
    </script>
</body>

</html>