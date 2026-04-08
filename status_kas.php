<?php
session_start();
include "connection/connection.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'bendahara') {
    header("Location: login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// --- 2. LOGIKA FILTER BULAN ---
$bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$bulan_aktif = $_GET['bulan'] ?? date('n') - 1; // Default bulan sekarang (index 0-11)
$nama_bulan_aktif = is_numeric($bulan_aktif) ? $bulan_list[$bulan_aktif] : $bulan_aktif;

// --- 3. AMBIL DATA MURID ---
$query_murid = mysqli_query($conn, "SELECT id_murid, nama FROM murid WHERE id_kelas = '$id_kelas' ORDER BY nama ASC");

// --- HITUNG TOTAL KELUNASAN KELAS ---
$query_total_murid = mysqli_query($conn, "SELECT COUNT(*) as total FROM murid WHERE id_kelas = '$id_kelas'");
$total_murid = mysqli_fetch_assoc($query_total_murid)['total'];

// Hitung berapa murid yang sudah lunas (bayar 4 minggu) di bulan aktif
$query_lunas_kelas = mysqli_query($conn, "SELECT COUNT(*) as total_lunas FROM (
    SELECT id_murid FROM transaksi 
    WHERE bulan = '$bulan_aktif' AND jenis = 'Masuk' 
    GROUP BY id_murid HAVING COUNT(minggu) >= 4
) as subquery");
$total_lunas_kelas = mysqli_fetch_assoc($query_lunas_kelas)['total_lunas'];

$persen_lunas = ($total_murid > 0) ? ($total_lunas_kelas / $total_murid) * 100 : 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Status Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <!-- NAVBAR -->
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
                        <a class="nav-link" href="dashboard_bendahara.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="kelolaKas/pemasukkan.php">
                            Kelola Kas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="status_kas.php">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Status Kas</h2>
                <p class="text-muted small">Monitoring iuran mingguan siswa</p>
            </div>

            <form method="GET" class="d-flex gap-2">
                <select name="bulan" class="form-select border-primary shadow-sm" onchange="this.form.submit()" style="width: 200px; border-radius: 10px;">
                    <?php foreach ($bulan_list as $index => $bln) : ?>
                        <option value="<?= $bln ?>" <?= ($nama_bulan_aktif == $bln) ? 'selected' : '' ?>>
                            Bulan <?= $bln ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 15px; background: <?= ($persen_lunas == 100) ? '#d1e7dd' : '#fff3cd' ?>;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white p-3 me-3 shadow-sm">
                                <i class="bi <?= ($persen_lunas == 100) ? 'bi-check-all text-success' : 'bi-exclamation-triangle text-warning' ?> fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Konfirmasi Kelunasan Kelas</h5>
                                <p class="mb-0 small text-muted">Bulan: <?= $bulan_aktif ?> | <?= $total_lunas_kelas ?> dari <?= $total_murid ?> Murid Lunas</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <h4 class="fw-bold mb-0 <?= ($persen_lunas == 100) ? 'text-success' : 'text-warning' ?>"><?= round($persen_lunas) ?>%</h4>
                            <span class="small fw-bold"><?= ($persen_lunas == 100) ? 'SIAP TUTUP BUKU' : 'BELUM LUNAS' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4 py-3" style="width: 50px;">No</th>
                            <th class="py-3">Nama Siswa</th>
                            <th class="text-center py-3">M-1</th>
                            <th class="text-center py-3">M-2</th>
                            <th class="text-center py-3">M-3</th>
                            <th class="text-center py-3">M-4</th>
                            <th class="text-center py-3 pe-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($query_murid)) :
                            $id_m = $row['id_murid'];

                            // Cek status per minggu di database
                            $cek_bayar = mysqli_query($conn, "SELECT minggu FROM transaksi WHERE id_murid = '$id_m' AND bulan = '$nama_bulan_aktif' AND jenis = 'Masuk'");
                            $paid_weeks = [];
                            while ($t = mysqli_fetch_assoc($cek_bayar)) {
                                $paid_weeks[] = $t['minggu'];
                            }

                            $lunas_count = count($paid_weeks);
                        ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $no++ ?></td>
                                <td class="fw-bold"><?= $row['nama'] ?></td>

                                <?php for ($i = 1; $i <= 4; $i++) : $m = "M-$i"; ?>
                                    <td class="text-center">
                                        <?php if (in_array($m, $paid_weeks)) : ?>
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        <?php else : ?>
                                            <i class="bi bi-x-circle text-danger opacity-25"></i>
                                        <?php endif; ?>
                                    </td>
                                <?php endfor; ?>

                                <td class="text-center pe-4">
                                    <?php if ($lunas_count >= 4) : ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2" style="border-radius: 8px;">Lunas</span>
                                    <?php elseif ($lunas_count > 0) : ?>
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2" style="border-radius: 8px;">Nunggak <?= 4 - $lunas_count ?></span>
                                    <?php else : ?>
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2" style="border-radius: 8px;">Belum Bayar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex gap-3">
            <div class="small"><i class="bi bi-check-circle-fill text-success me-1"></i> Sudah Terbayar</div>
            <div class="small"><i class="bi bi-x-circle text-danger opacity-50 me-1"></i> Belum Terbayar</div>
        </div>
    </div>
</body>

</html>