<?php
session_start();
include __DIR__ . "/config/database.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
    
$id_kelas = $_SESSION['id_kelas'];
$role_user = strtolower(trim($_SESSION['role']));

// Daftar bulan untuk dropdown
$list_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'Semua';
$kondisi_bulan = ($filter_bulan != 'Semua') ? " AND t.bulan = '$filter_bulan'" : "";

// --- 2. LOGIKA SALDO (Tetap tampil untuk semua role sebagai transparansi) ---
$sql_masuk = "SELECT SUM(t.nominal) as total FROM transaksi t 
              JOIN kategori k ON t.id_kategori = k.id_kategori 
              JOIN user u ON t.id_user = u.id_user 
              WHERE u.id_kelas = '$id_kelas' AND k.jenis = 'Masuk' $kondisi_bulan";
$q_masuk = mysqli_query($conn, $sql_masuk);

$sql_keluar = "SELECT SUM(t.nominal) as total FROM transaksi t 
               JOIN kategori k ON t.id_kategori = k.id_kategori 
               JOIN user u ON t.id_user = u.id_user
               WHERE u.id_kelas = '$id_kelas' AND k.jenis = 'Keluar' $kondisi_bulan";
$q_keluar = mysqli_query($conn, $sql_keluar);

$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

// --- 3. LOGIKA FILTER TABEL (Murid hanya lihat Keluar) ---
$filter_murid = ($role_user == 'murid') ? " AND k.jenis = 'Keluar'" : "";

$sql_riwayat = "SELECT t.*, a.nama_anggota, k.nama_kategori, k.jenis
        FROM transaksi t 
        JOIN user u ON t.id_user = u.id_user 
        LEFT JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
        JOIN kategori k ON t.id_kategori = k.id_kategori
        WHERE u.id_kelas = '$id_kelas' 
        $kondisi_bulan 
        $filter_murid
        ORDER BY t.created_at DESC";

$query_riwayat = mysqli_query($conn, $sql_riwayat) or die(mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="id">

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
                <p class="text-muted small">
                    <?= ($role_user == 'murid') ? 'Mode Transparansi: Hanya menampilkan pengeluaran kelas' : 'Daftar mutasi uang masuk dan keluar secara kronologis'; ?>
                </p>

                <form method="GET" action="" class="mt-2">
                    <select name="bulan" class="form-select shadow-sm border-0" onchange="this.form.submit()" style="width: 200px;">
                        <option value="Semua" <?= $filter_bulan == 'Semua' ? 'selected' : '' ?>>-- Semua Bulan --</option>
                        <?php foreach ($list_bulan as $bln) : ?>
                            <option value="<?= $bln ?>" <?= $filter_bulan == $bln ? 'selected' : '' ?>><?= $bln ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
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
                            <?php if ($role_user != 'murid') : ?>
                                <th class="py-3 text-end">Masuk (Rp)</th>
                            <?php endif; ?>
                            <th class="py-3 text-end">Keluar (Rp)</th>
                            <th class="py-3 pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_riwayat) > 0) : ?>
                            <?php while ($row = mysqli_fetch_assoc($query_riwayat)) :
                                $is_masuk = ($row['jenis'] == 'Masuk');
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($is_masuk) : ?>
                                            <span class="fw-bold"><?= $row['nama_anggota'] ?? 'Umum' ?></span>
                                            <small class="d-block text-muted small">Bulan: <?= $row['bulan'] ?></small>
                                        <?php else : ?>
                                            <span class="fw-bold"><?= $row['keterangan'] ?: $row['nama_kategori'] ?></span>
                                            <small class="d-block text-muted text-uppercase small">Keperluan Kelas</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $is_masuk ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2 py-1 mb-1">
                                            <?= $is_masuk ? 'Pemasukan' : 'Pengeluaran' ?>
                                        </span>
                                    </td>
                                    <?php if ($role_user != 'murid') : ?>
                                        <td class="text-end fw-bold text-success">
                                            <?= $is_masuk ? 'Rp ' . number_format($row['nominal'], 0, ',', '.') : '-' ?>
                                        </td>
                                    <?php endif; ?>
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
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Tidak ada riwayat transaksi pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>