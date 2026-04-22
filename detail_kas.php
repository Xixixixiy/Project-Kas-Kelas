<?php
session_start();
include "connection/connection.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'bendahara') {
    header("Location: login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// --- 2. LOGIKA FILTER BULAN & SALDO ---
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'Semua';

// Buat kondisi tambahan untuk SQL
$kondisi_bulan = ($filter_bulan != 'Semua') ? " AND t.bulan = '$filter_bulan'" : "";

// A. HITUNG TOTAL SALDO (Hanya untuk kelas ini dan bulan yang dipilih)
// Kita join ke tabel kategori (k) untuk mengecek k.jenis
$q_masuk = mysqli_query($conn, "SELECT SUM(t.jumlah) as total 
                                FROM transaksi t 
                                JOIN kategori k ON t.id_kategori = k.id_kategori 
                                WHERE t.id_kelas = '$id_kelas' 
                                AND k.jenis = 'Masuk' $kondisi_bulan");

$q_keluar = mysqli_query($conn, "SELECT SUM(t.jumlah) as total 
                                 FROM transaksi t 
                                 JOIN kategori k ON t.id_kategori = k.id_kategori 
                                 WHERE t.id_kelas = '$id_kelas' 
                                 AND k.jenis = 'Keluar' $kondisi_bulan");

$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

// B. AMBIL SEMUA RIWAYAT TRANSAKSI
$sql = "SELECT t.*, m.nama as nama_murid, k.nama_kategori, k.jenis 
        FROM transaksi t 
        LEFT JOIN murid m ON t.id_murid = m.id_murid 
        JOIN kategori k ON t.id_kategori = k.id_kategori
        WHERE t.id_kelas = '$id_kelas' 
        $kondisi_bulan
        ORDER BY t.tanggal DESC, t.id_transaksi DESC";

$query_riwayat = mysqli_query($conn, $sql);

// Untuk daftar bulan di combobox (opsional: bisa hardcode atau ambil dari DB)
$list_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
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
                        <?php while ($row = mysqli_fetch_assoc($query_riwayat)) : ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="d-block fw-bold"><?= date('d M Y', strtotime($row['tanggal'])) ?></span>
                                </td>

                                <td>
                                    <?php if ($row['jenis'] == 'Masuk') : ?>
                                        <span class="fw-bold"><?= $row['nama_murid'] ?></span>
                                        <small class="d-block text-muted">Bulan: <?= $row['bulan'] ?></small>
                                    <?php else : ?>
                                        <span class="fw-bold"><?= $row['keterangan'] ?></span>
                                        <small class="d-block text-muted text-uppercase">Pengeluaran Umum</small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge <?= $row['jenis'] == 'Masuk' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2 py-1 mb-1 d-inline-block">
                                        <?= $row['jenis'] == 'Masuk' ? 'Pemasukkan' : 'Pengeluaran' ?>
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1 d-inline-block">
                                        <?= $row['nama_kategori'] ?>
                                    </span>
                                </td>

                                <td class="text-end fw-bold text-success">
                                    <?= $row['jenis'] == 'Masuk' ? '+ ' . number_format($row['jumlah'], 0, ',', '.') : '-' ?>
                                </td>

                                <td class="text-end fw-bold text-danger">
                                    <?= $row['jenis'] == 'Keluar' ? '- ' . number_format($row['jumlah'], 0, ',', '.') : '-' ?>
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