<?php
session_start();
include "config/database.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'bendahara') {
    header("Location: login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// --- LOGIKA FILTER BULAN ---
// Mengambil bulan dari URL atau default ke bulan berjalan
// --- DEFINISI LIST BULAN (Untuk Form Dropdown) ---
$bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$bulan_aktif = $_GET['bulan'] ?? $bulan_list[(int)date('m') - 1];

// --- ALGORITMA KELUNASAN KELAS ---
// 1. Hitung total murid di kelas tersebut
// $query_total_murid = mysqli_query($conn, "SELECT COUNT(*) as total FROM murid WHERE id_kelas = '$id_kelas'");
$query_total_murid = mysqli_query($conn, "SELECT COUNT(*) as total 
FROM user u 
JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
WHERE u.id_kelas = '$id_kelas' AND u.status = 'Aktif' AND u.id_role IN (1, 2, 3)");

$total_murid = mysqli_fetch_assoc($query_total_murid)['total'];

// 2. Hitung murid yang sudah bayar 4 kali (M-1 sampai M-4) di bulan tersebut
$query_lunas_kelas = mysqli_query($conn, "SELECT COUNT(*) as total_lunas FROM (
    SELECT t.id_user FROM transaksi t
    JOIN user u ON t.id_user = u.id_user
    WHERE u.id_kelas = '$id_kelas' 
    AND u.status = 'Aktif'
    AND u.id_role IN (1, 2, 3) -- Filter role di sini juga
    AND t.bulan = '$bulan_aktif' 
    AND t.id_kategori = 1 
    GROUP BY t.id_user HAVING COUNT(DISTINCT t.minggu) >= 4
) as subquery");

$total_lunas_kelas = mysqli_fetch_assoc($query_lunas_kelas)['total_lunas'];

// 3. Hitung persentase untuk indikator kemajuan (Progress Bar)
$persen_lunas = ($total_murid > 0) ? ($total_lunas_kelas / $total_murid) * 100 : 0;

// --- AMBIL DAFTAR MURID ---
// Kita butuh ini agar variabel $query_murid dikenali oleh 'while' di bawah
$query_murid = mysqli_query($conn, "SELECT u.id_user, a.nama_anggota
FROM user u
JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
WHERE u.id_kelas = '$id_kelas' AND u.status = 'Aktif' AND u.id_role IN (1, 2, 3)
ORDER BY a.nama_anggota ASC");

$nama_bulan_aktif = $bulan_aktif; // Menyamakan variabel agar dropdown sinkron
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
    <?php include "layout/navbar.php"; ?>

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
                            $id_u = $row['id_user'];

                            // Cek status per minggu di database
                            $cek_bayar = mysqli_query($conn, "SELECT minggu FROM transaksi 
                                        WHERE id_user = '$id_u' AND bulan = '$nama_bulan_aktif' AND id_kategori IN (1,2,3)"); // Pastikan hanya kategori 'Masuk' yang dihitung
                            $paid_weeks = [];
                            while ($t = mysqli_fetch_assoc($cek_bayar)) {
                                $paid_weeks[] = $t['minggu'];
                            }

                            $lunas_count = count($paid_weeks);
                        ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $no++ ?></td>
                                <td class="fw-bold"><?= $row['nama_anggota'] ?></td>

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