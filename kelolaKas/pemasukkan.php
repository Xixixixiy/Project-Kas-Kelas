<?php
session_start();
include "../connection/connection.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'bendahara') {
    header("Location: ../login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// --- 2. AMBIL DATA MURID ---
$murid = mysqli_query($conn, "SELECT * FROM murid WHERE id_kelas = '$id_kelas'");

// --- 3. DATA FILTER DARI URL ---
$murid_terpilih = $_GET['id_murid'] ?? '';
$bulan_terpilih = $_GET['bulan'] ?? '';

// --- 4. CEK STATUS MINGGU (Untuk Tombol M-1 s/d M-4) ---
$sudah_bayar = [];
if ($murid_terpilih && $bulan_terpilih) {
    $cek_minggu = mysqli_query($conn, "SELECT minggu FROM transaksi WHERE id_murid = '$murid_terpilih' AND bulan = '$bulan_terpilih' AND jenis = 'Masuk'");
    while ($row = mysqli_fetch_assoc($cek_minggu)) {
        $sudah_bayar[] = $row['minggu'];
    }
}

// --- 5. CEK STATUS BULAN (Untuk Logika Kunci/Lock) ---
$bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$bulan_lunas = [];
if ($murid_terpilih) {
    $cek_lunas = mysqli_query($conn, "SELECT bulan FROM transaksi WHERE id_murid = '$murid_terpilih' AND jenis = 'Masuk' GROUP BY bulan HAVING COUNT(minggu) >= 4");
    while ($row = mysqli_fetch_assoc($cek_lunas)) {
        $bulan_lunas[] = $row['bulan'];
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
            <a class="navbar-brand fw-bold text-primary" href="#">Kas Kelas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard_bendahara.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="pemasukkan.php">Kelola Kas</a>
                    </li>
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
        <h2 class="fw-bold">Pemasukkan</h2>
        <p class="text-muted small">Mengatur pemasukkan dan pengeluaran kas</p>

        <div class="bg-light p-1 d-flex mb-4" style="border-radius: 50px; border: 1px solid #ddd;">
            <a href="pemasukkan.php" class="btn btn-success w-50 fw-bold" style="border-radius: 50px;">
                Pemasukkan
            </a>
            <a href="pengeluaran.php" class="btn btn-light w-50 fw-bold" style="border-radius: 50px;">
                Pengeluaran
            </a>
        </div>

        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
            <form action="proses_tambah.php" method="POST">
                <input type="hidden" name="jenis" id="inputJenis" value="Masuk">

                <div class="row">
                    <div class="col-md-6">
                        <div id="groupMurid" class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-mortarboard"></i> Pilih Murid</label>
                            <select name="id_murid" id="selectMurid" class="form-select border-secondary-subtle">
                                <option value="">-- Pilih Murid --</option>
                                <?php
                                mysqli_data_seek($murid, 0);
                                while ($m = mysqli_fetch_assoc($murid)) { ?>
                                    <option value="<?= $m['id_murid'] ?>" <?= ($murid_terpilih == $m['id_murid']) ? 'selected' : '' ?>>
                                        <?= $m['nama'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div id="groupWaktu" class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-calendar3"></i> Pilih Bulan</label>
                            <select name="bulan" id="selectBulan" class="form-select mb-3">
                                <option value="">-- Pilih Bulan --</option>
                                <?php
                                foreach ($bulan_list as $index => $bln) :
                                    $bulan_sebelumnya = ($index > 0) ? $bulan_list[$index - 1] : null;
                                    $boleh_diisi = ($index == 0 || in_array($bulan_sebelumnya, $bulan_lunas));
                                    $is_lunas = in_array($bln, $bulan_lunas);
                                ?>
                                    <option value="<?= $bln ?>" <?= ($bulan_terpilih == $bln) ? 'selected' : '' ?> <?= (!$boleh_diisi || $is_lunas) ? 'disabled' : '' ?>>
                                        <?= $bln ?> <?= $is_lunas ? ' (Lunas)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="d-flex gap-2">
                                <?php
                                $m_list = ['M-1', 'M-2', 'M-3', 'M-4'];
                                foreach ($m_list as $minggu_val) :
                                    $is_paid = in_array($minggu_val, $sudah_bayar);
                                ?>
                                    <button type="button" class="btn <?= $is_paid ? 'btn-success text-white disabled' : 'btn-light border' ?> btn-minggu" data-m="<?= $minggu_val ?>" <?= $is_paid ? 'disabled' : '' ?>>
                                        <?= $minggu_val ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="minggu" id="inputMinggu" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-cash"></i> Jumlah (Rp)</label>
                            <input type="number" name="jumlah" id="inputJumlah" class="form-control bg-light border-secondary-subtle" placeholder="0" readonly required>
                            <div class="form-text text-primary">*Otomatis Rp 5.000 per minggu</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3 h-100">
                            <label class="form-label small fw-bold">Keterangan</label>
                            <textarea name="keterangan" class="form-control border-secondary-subtle" style="height: 200px;" placeholder="Catatan transaksi..."></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 fw-bold mt-4 p-3" style="border-radius: 12px;">SIMPAN DATA</button>
            </form>
        </div>
    </div>

    <script>
        const selectMurid = document.getElementById('selectMurid');
        const selectBulan = document.getElementById('selectBulan');
        const btnsMinggu = document.querySelectorAll('.btn-minggu');
        const inputMinggu = document.getElementById('inputMinggu');

        // Fungsi Refresh halaman agar data sinkron
        function updatePage() {
            const m = selectMurid.value;
            const b = selectBulan.value;
            if (m) {
                window.location.href = `pemasukkan.php?id_murid=${m}&bulan=${b}`;
            }
        }

        selectMurid.addEventListener('change', updatePage);
        selectBulan.addEventListener('change', updatePage);

        // Logika Klik Minggu
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
                    if (!btnsMinggu[i].disabled) {
                        btnsMinggu[i].classList.remove('btn-light');
                        btnsMinggu[i].classList.add('btn-success', 'text-white');
                    }
                }
                
                inputMinggu.value = btn.getAttribute('data-m');
                // Ambil angka minggunya (misal M-3 jadi 3)
                const mingguKe = index + 1;

                // Hitung nominal: 5000 dikali jumlah minggu yang dipilih
                // Tapi kita perlu tahu berapa minggu yang BARU akan dibayar
                const sudahBayarCount = document.querySelectorAll('.btn-minggu.btn-success.disabled').length;
                const jumlahMingguBaru = mingguKe - sudahBayarCount;

                // Set nominal di input (misal bayar 3 minggu sekaligus = 15.000)
                document.getElementById('inputJumlah').value = jumlahMingguBaru * 5000;

                inputMinggu.value = btn.getAttribute('data-m');
            });
        });
    </script>
</body>

</html>