<?php
session_start();
include "connection/connection.php";

// --- 1. VALIDASI AKSES ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'bendahara') {
    header("Location: login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// --- 2. HITUNG TOTAL SALDO AKHIR ---
$q_masuk = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Masuk'");
$q_keluar = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Keluar'");
$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

// --- 3. AMBIL SEMUA RIWAYAT TRANSAKSI ---
// Kita JOIN dengan tabel murid untuk mendapatkan nama (khusus pemasukkan)
$query_riwayat = mysqli_query($conn, "SELECT t.*, m.nama as nama_murid 
    FROM transaksi t 
    LEFT JOIN murid m ON t.id_murid = m.id_murid 
    ORDER BY t.id_transaksi DESC"); // Data terbaru di atas
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Kas - Riwayat Transaksi</title>
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
                    <li class="nav-item"><a class="nav-link" href="dashboard_bendahara.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelolaKas/pemasukkan.php">Kelola Kas</a></li>
                    <li class="nav-item"><a class="nav-link" href="status_kas.php">Status Kas</a></li>
                    <li class="nav-item"><a class="nav-link active" href="detail_kas.php">Detail Kas</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted"><?php echo $_SESSION['nama']; ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Detail Riwayat Kas</h2>
                <p class="text-muted small">Daftar mutasi uang masuk dan keluar secara kronologis</p>
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
                                        <small class="d-block text-muted">Iuran: <?= $row['bulan'] ?> (<?= $row['minggu'] ?>)</small>
                                    <?php else : ?>
                                        <span class="fw-bold"><?= $row['keterangan'] ?></span>
                                        <small class="d-block text-muted text-uppercase">Pengeluaran Umum</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['jenis'] == 'Masuk') : ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2">Pemasukkan</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">Pengeluaran</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    <?= $row['jenis'] == 'Masuk' ? '+ ' . number_format($row['jumlah'], 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    <?= $row['jenis'] == 'Keluar' ? '- ' . number_format($row['jumlah'], 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-light btn-sm rounded-circle"><i class="bi bi-printer text-primary"></i></button>
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