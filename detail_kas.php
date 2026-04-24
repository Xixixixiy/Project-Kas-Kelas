<?php
session_start();
include "config/database.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'bendahara') {
    header("Location: login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// --- 2. LOGIKA FILTER BULAN & SALDO ---
// Untuk daftar bulan di combobox (opsional: bisa hardcode atau ambil dari DB)
$list_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'Semua';

// Buat kondisi tambahan untuk SQL
$kondisi_bulan = ($filter_bulan != 'Semua') ? " AND t.bulan = '$filter_bulan'" : "";

// A. HITUNG TOTAL SALDO (Hanya untuk kelas ini dan bulan yang dipilih)
// 1. Hitung Pemasukan
$sql_masuk = "SELECT SUM(nominal) as total FROM transaksi t 
              LEFT JOIN user u ON t.id_user = u.id_kelas
              WHERE id_kelas = '$id_kelas' 
              AND t.id_kategori IN (1,2,3) $kondisi_bulan";
$q_masuk = mysqli_query($conn, $sql_masuk) or die(mysqli_error($conn));

// 2. Hitung Pengeluaran
$sql_keluar = "SELECT SUM(nominal) as total FROM transaksi t 
               LEFT JOIN user u ON t.id_user = u.id_kelas
               WHERE id_kelas = '$id_kelas' 
               AND t.id_kategori IN (4,5,6) $kondisi_bulan";
$q_keluar = mysqli_query($conn, $sql_keluar) or die(mysqli_error($conn));

// Sekarang baru fetch (pasti aman karena ada 'or die' di atas)
$res_masuk = mysqli_fetch_assoc($q_masuk);
$res_keluar = mysqli_fetch_assoc($q_keluar);

$total_masuk = $res_masuk['total'] ?? 0;
$total_keluar = $res_keluar['total'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

// B. AMBIL SEMUA RIWAYAT TRANSAKSI
$sql = "SELECT t.*, a.nama_anggota, k.nama_kategori
        FROM transaksi t 
        LEFT JOIN user u ON t.id_user = u.id_user 
        LEFT JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
        JOIN kategori k ON t.id_kategori = k.id_kategori
        WHERE id_kelas = '$id_kelas' 
        $kondisi_bulan
        ORDER BY t.created_at DESC";

$query_riwayat = mysqli_query($conn, $sql) or die(mysqli_error($conn));
// Tambahkan 'or die' di sini supaya kalau error kamu langsung tau alasannya!
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Kas - Riwayat Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <?php include "layout/navbar.php"; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Detail Riwayat Kas</h2>
                <p class="text-muted small">Daftar mutasi uang masuk dan keluar secara kronologis</p>

                <div class="mb-3">
                    <div class="">
                        <form method="GET" action="">
                            <select name="bulan" class="form-select shadow-sm border-0" onchange="this.form.submit()">
                                <option value="Semua" <?= $filter_bulan == 'Semua' ? 'selected' : '' ?>>-- Semua Bulan --</option>
                                <?php foreach ($list_bulan as $bln) : ?>
                                    <option value="<?= $bln ?>" <?= $filter_bulan == $bln ? 'selected' : '' ?>><?= $bln ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <div class="p-3 bg-white shadow-sm rounded-4 border-start border-primary border-4">
                    <span class="text-muted small d-block">Saldo Kas Terkini</span>
                    <h3 class="fw-bold text-primary mb-0">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-3">Tanggal</th>
                            <th class="py-3">Deskripsi</th>
                            <th class="py-3">Kategori</th>
                            <th class="py-3 text-end">Masuk (Rp)</th>
                            <th class="py-3 text-end">Keluar (Rp)</th>
                            <th class="py-3 pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query_riwayat)) :
                            // LOGIKA DETEKTIF: Tentukan jenis transaksi secara mandiri
                            // Jika ID Kategori 1, 2, atau 3, maka itu 'Masuk'
                            $is_masuk = in_array($row['id_kategori'], [1, 2, 3]);
                            $jenis_label = $is_masuk ? 'Masuk' : 'Keluar';
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="d-block fw-bold"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                </td>

                                <td>
                                    <?php if ($is_masuk) : ?>
                                        <span class="fw-bold"><?= $row['nama_anggota'] ?? 'Umum' ?></span>
                                        <small class="d-block text-muted">Bulan: <?= $row['bulan'] ?></small>
                                    <?php else : ?>
                                        <span class="fw-bold"><?= $row['keterangan'] ?></span>
                                        <small class="d-block text-muted text-uppercase">Pengeluaran Kelas</small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge <?= $is_masuk ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2 py-1 mb-1 d-inline-block">
                                        <?= $is_masuk ? 'Pemasukan' : 'Pengeluaran' ?>
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1 d-inline-block">
                                        <?= $row['nama_kategori'] ?>
                                    </span>
                                </td>

                                <td class="text-end fw-bold text-success">
                                    <?= $is_masuk ? 'Rp ' . number_format($row['nominal'], 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end fw-bold text-danger">
                                    <?= !$is_masuk ? 'Rp ' . number_format($row['nominal'], 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-center pe-4">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm">
                                        <i class="bi bi-printer text-primary"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</body>

</html>