<?php
session_start();
include "../config/database.php";

// Validasi Akses
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'murid') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_kelas = $_SESSION['id_kelas'];

// --- LOGIKA PERHITUNGAN TUNGGAKAN ---
$iuran_per_minggu = 5000;
$tgl_mulai_kas = "2024-01-01"; // GANTI SESUAI AWAL SEMESTER KAMU (format Y-m-d)

// Hitung jumlah minggu yang sudah berjalan dari tgl_mulai sampai sekarang
$start_date = new DateTime($tgl_mulai_kas);
$today = new DateTime();
$diff = $start_date->diff($today);
$minggu_berjalan = floor($diff->days / 7) + 1; // +1 karena minggu pertama langsung dihitung

// Ambil total yang sudah dibayar oleh murid ini (Kategori 'Masuk')
$q_total = mysqli_query($conn, "SELECT SUM(t.nominal) as total 
                                FROM transaksi t 
                                JOIN kategori k ON t.id_kategori = k.id_kategori 
                                WHERE t.id_user = '$id_user' AND k.jenis = 'Masuk'");
$total_bayar = mysqli_fetch_assoc($q_total)['total'] ?? 0;

// Hitung Selisih
$target_seharusnya = $minggu_berjalan * $iuran_per_minggu;
$sisa_tunggakan = $target_seharusnya - $total_bayar;
$jumlah_minggu_nunggak = $sisa_tunggakan / $iuran_per_minggu;

// --- AMBIL DATA UNTUK TABEL ---
// 1. Riwayat Pribadi
$query_pribadi = mysqli_query($conn, "SELECT t.*, k.nama_kategori 
    FROM transaksi t 
    JOIN kategori k ON t.id_kategori = k.id_kategori 
    WHERE t.id_user = '$id_user' AND k.jenis = 'Masuk' 
    ORDER BY t.created_at DESC");

// 2. Transparansi Pengeluaran Kelas
$query_pengeluaran = mysqli_query($conn, "SELECT t.*, k.nama_kategori, a.nama_anggota as pj
    FROM transaksi t 
    JOIN kategori k ON t.id_kategori = k.id_kategori 
    JOIN user u ON t.id_user = u.id_user
    JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
    WHERE u.id_kelas = '$id_kelas' AND k.jenis = 'Keluar' 
    ORDER BY t.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Murid - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <?php include "../layout/navbar.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">

                <?php if ($sisa_tunggakan > 0) : ?>
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center p-4 mb-4">
                        <i class="bi bi-exclamation-octagon-fill fs-1 me-4"></i>
                        <div>
                            <h5 class="alert-heading fw-bold">Ups! Anda punya tunggakan.</h5>
                            <p class="mb-0">Anda belum membayar kas selama <strong><?= $jumlah_minggu_nunggak ?> minggu</strong>.
                                Total tunggakan: <strong>Rp <?= number_format($sisa_tunggakan, 0, ',', '.') ?></strong></p>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center p-4 mb-4">
                        <i class="bi bi-check-circle-fill fs-1 me-4"></i>
                        <div>
                            <h5 class="alert-heading fw-bold">Pembayaran Aman!</h5>
                            <p class="mb-0">Terima kasih <?= $_SESSION['nama'] ?>, kas Anda sudah lunas sampai minggu ini.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-primary">Riwayat Pembayaran Saya</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($query_pribadi)) : ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                            <td><?= $row['nama_kategori'] ?></td>
                                            <td class="text-success fw-bold">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4 bg-primary text-white" style="border-radius: 15px;">
                    <div class="card-body text-center py-4">
                        <p class="mb-1 opacity-75">Total Kas yang Saya Bayar</p>
                        <h2 class="fw-bold">Rp <?= number_format($total_bayar, 0, ',', '.') ?></h2>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-danger">Transparansi Pengeluaran</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php while ($exp = mysqli_fetch_assoc($query_pengeluaran)) : ?>
                                <div class="list-group-item p-3 border-0 border-bottom">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted"><?= date('d/m/y', strtotime($exp['created_at'])) ?></small>
                                        <span class="text-danger fw-bold small">-Rp <?= number_format($exp['nominal'], 0, ',', '.') ?></span>
                                    </div>
                                    <h6 class="mb-0" style="font-size: 0.9rem;"><?= $exp['nama_kategori'] ?></h6>
                                    <small class="text-muted italic" style="font-size: 0.75rem;">Oleh: <?= $exp['pj'] ?></small>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center py-3">
                        <a href="../detail_kas.php" class="text-decoration-none small fw-bold">Lihat Semua Alur Kas →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>