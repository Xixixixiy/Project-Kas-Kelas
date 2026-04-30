<?php
session_start();
include __DIR__ . "/../config/database.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) != 'bendahara') {
    header("Location: login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// --- 2. AMBIL DATA MURID (Gunakan try-catch atau cek query) ---
$query_murid = "SELECT u.id_user, a.nama_anggota 
                FROM user u
                JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
                WHERE u.id_kelas = '$id_kelas' AND u.id_role IN (1, 2, 3) AND u.status = 'Aktif'
                ORDER BY a.nama_anggota ASC";
$murid = mysqli_query($conn, $query_murid);

// Ambil Kategori yang jenisnya 'Masuk' saja
$query_kategori = mysqli_query($conn, "SELECT * FROM kategori WHERE jenis = 'Masuk'");

// --- 3. DATA FILTER DARI URL ---
$user_terpilih = $_GET['id_user'] ?? '';
$bulan_terpilih = $_GET['bulan'] ?? '';
$tahun_terpilih = $_GET['tahun'] ?? date('Y');

// --- 4. CEK STATUS MINGGU & LUNAS ---
$sudah_bayar = [];
$bulan_lunas = [];
$bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

if ($user_terpilih) {
    // Ambil minggu yang sudah dibayar
    if ($bulan_terpilih) {
        $cek_minggu = mysqli_query($conn, "SELECT minggu FROM transaksi WHERE id_user = '$user_terpilih' AND bulan = '$bulan_terpilih' AND tahun = '$tahun_terpilih'");
        while ($row = mysqli_fetch_assoc($cek_minggu)) {
            $sudah_bayar[] = $row['minggu'];
        }
    }

    // Ambil daftar bulan yang sudah lunas (Hanya untuk kategori ID 1 / Uang Kas)
    $cek_lunas = mysqli_query($conn, "SELECT bulan FROM transaksi 
             WHERE id_user = '$user_terpilih' 
             AND tahun = '$tahun_terpilih' 
             AND id_kategori = 1 
             GROUP BY bulan HAVING COUNT(minggu) >= 4");
    while ($row = mysqli_fetch_assoc($cek_lunas)) {
        $bulan_lunas[] = $row['bulan'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pemasukkan Kas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

    <?php include "../layout/navbar.php"; ?>

    <div class="container">
        <h2 class="fw-bold text-success">Pemasukkan Kas</h2>
        <p class="text-muted small">Catat uang kas masuk dari murid</p>

        <div class="bg-white p-1 d-flex mb-4 shadow-sm" style="border-radius: 50px;">
            <a href="transaksi_masuk.php" class="btn btn-success w-50 fw-bold" style="border-radius: 50px;">Pemasukkan</a>
            <a href="transaksi_keluar.php" class="btn btn-light w-50 fw-bold" style="border-radius: 50px;">Pengeluaran</a>
        </div>

        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">

            <form action="actions/proses_tambah.php" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Pilih Kategori</label>
                            <select name="id_kategori" id="selectKategori" class="form-select" onchange="cekKategori()">
                                <?php
                                mysqli_data_seek($query_kategori, 0);
                                while ($k = mysqli_fetch_assoc($query_kategori)): ?>
                                    <option value="<?= $k['id_kategori'] ?>" <?= ($k['id_kategori'] == 1) ? 'selected' : '' ?>>
                                        <?= $k['nama_kategori'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Pilih Murid</label>
                            <select name="id_user" id="selectMurid" class="form-select">
                                <option value="">-- Pilih Murid --</option>
                                <?php if ($murid) : ?>
                                    <?php while ($m = mysqli_fetch_assoc($murid)) : ?>
                                        <option value="<?= $m['id_user'] ?>" <?= ($user_terpilih == $m['id_user']) ? 'selected' : '' ?>>
                                            <?= $m['nama_anggota'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small">Tahun</label>
                                <select name="tahun" id="selectTahun" class="form-select">
                                    <?php for ($i = date('Y') - 1; $i <= date('Y'); $i++): ?>
                                        <option value="<?= $i ?>" <?= ($tahun_terpilih == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-bold small">Bulan</label>
                                <select name="bulan" id="selectBulan" class="form-select">
                                    <option value="">-- Pilih Bulan --</option>
                                    <?php foreach ($bulan_list as $bln) :
                                        $is_lunas = in_array($bln, $bulan_lunas);
                                    ?>
                                        <option value="<?= $bln ?>"
                                            <?= ($bulan_terpilih == $bln) ? 'selected' : '' ?>
                                            class="<?= $is_lunas ? 'opt-lunas' : '' ?>"
                                            <?= $is_lunas ? 'disabled' : '' ?>>
                                            <?= $bln ?> <?= $is_lunas ? '(Lunas)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="wrapperMinggu">
                            <label class="form-label fw-bold small">Pilih Minggu</label>
                            <div class="d-flex gap-2 mb-3">
                                <?php foreach (['M-1', 'M-2', 'M-3', 'M-4'] as $minggu) :
                                    $paid = in_array($minggu, $sudah_bayar);
                                ?>
                                    <button type="button" class="btn <?= $paid ? 'btn-success disabled' : 'btn-outline-secondary' ?> btn-minggu" data-m="<?= $minggu ?>" <?= $paid ? 'disabled' : '' ?>>
                                        <?= $minggu ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" name="minggu" id="inputMinggu" required>

                        <label class="form-label fw-bold small">Nominal Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="nominal" id="inputJumlah" class="form-control" readonly required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="inputKeterangan" class="form-control" style="height: 310px;" placeholder="Contoh: Titip lewat teman..."></textarea>

                        <button type="submit" class="btn btn-success w-100 mt-4 py-3 fw-bold shadow-sm" style="border-radius: 12px;">
                            <i class="bi bi-check-circle me-2"></i>SIMPAN DATA KAS
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const selectMurid = document.getElementById('selectMurid');
        const selectBulan = document.getElementById('selectBulan');
        const selectTahun = document.getElementById('selectTahun');
        const selectKategori = document.getElementById('selectKategori');
        const btnsMinggu = document.querySelectorAll('.btn-minggu');
        const wrapperMinggu = document.getElementById('wrapperMinggu');
        const inputJumlah = document.getElementById('inputJumlah');
        const inputKeterangan = document.getElementById('inputKeterangan');

        function cekKategori() {
            const opsiLunas = document.querySelectorAll('.opt-lunas');

            // Asumsi: 1 = Uang Kas, 2 = Denda, 3 = Sumbangan
            if (selectKategori.value != "1") {
                // JIKA BUKAN UANG KAS (Denda/Sumbangan)
                wrapperMinggu.classList.add('d-none');
                inputJumlah.readOnly = false;
                inputJumlah.value = "";
                inputKeterangan.required = true;
                inputKeterangan.placeholder = "WAJIB: Tulis alasan denda/keterangan sumbangan...";

                // Buka kunci semua bulan yang tadinya disabled (Lunas)
                opsiLunas.forEach(opt => {
                    opt.disabled = false;
                });
            } else {
                // JIKA BALIK KE UANG KAS
                wrapperMinggu.classList.remove('d-none');
                inputJumlah.readOnly = true;
                inputKeterangan.required = false;
                inputKeterangan.placeholder = "Contoh: Titip lewat teman...";

                // Kunci kembali bulan yang statusnya lunas
                opsiLunas.forEach(opt => {
                    opt.disabled = true;
                });

                // Opsional: Jika bulan yang dipilih saat ini ternyata lunas, reset pilihannya
                if (selectBulan.selectedOptions[0].classList.contains('opt-lunas')) {
                    selectBulan.value = "";
                }
            }
        }

        // Fungsi Refresh Data (Tetap ada untuk iuran mingguan)
        function reloadData() {
            // JIKA kategori yang dipilih BUKAN Uang Kas (ID 1), jangan refresh halaman!
            if (selectKategori.value != "1") {
                return; // Berhenti di sini, jangan jalankan window.location.href
            }

            const m = selectMurid.value;
            const b = selectBulan.value;
            const t = selectTahun.value;
            window.location.href = `transaksi_masuk.php?id_user=${m}&bulan=${b}&tahun=${t}`;
        }

        selectMurid.addEventListener('change', reloadData);
        selectBulan.addEventListener('change', reloadData);
        selectTahun.addEventListener('change', reloadData);

        // Jalankan cekKategori saat halaman load (agar status denda/kas sinkron)
        window.onload = cekKategori;

        // Logika Klik Minggu (Tetap sama)
        btnsMinggu.forEach((btn, idx) => {
            btn.addEventListener('click', () => {
                if (btn.disabled) return;
                btnsMinggu.forEach(b => {
                    if (!b.disabled) b.className = 'btn btn-outline-secondary btn-minggu';
                });

                let count = 0;
                for (let i = 0; i <= idx; i++) {
                    if (!btnsMinggu[i].disabled) {
                        btnsMinggu[i].className = 'btn btn-success text-white btn-minggu';
                        count++;
                    }
                }
                document.getElementById('inputMinggu').value = btn.dataset.m;
                inputJumlah.value = count * 5000;
            });
        });
    </script>

    <?php include 'layout/notifikasi.php'; ?>
</body>

</html>