<?php
session_start();
include "../config/database.php";

// Proteksi
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Bendahara') {
    header("Location: login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// 1. Total Pemasukan & Pengeluaran (Filter per Kelas)
$query_pemasukan = mysqli_query($conn, "
    SELECT SUM(t.nominal) as total
    FROM transaksi t
    JOIN user u ON t.id_user = u.id_user
    JOIN kategori k ON t.id_kategori = k.id_kategori
    WHERE u.id_kelas = '$id_kelas' AND k.jenis = 'Masuk'
");
$pemasukan = mysqli_fetch_assoc($query_pemasukan)['total'] ?? 0;

$query_pengeluaran = mysqli_query($conn, "
    SELECT SUM(t.nominal) as total 
    FROM transaksi t
    JOIN user u ON t.id_user = u.id_user
    JOIN kategori k ON t.id_kategori = k.id_kategori
    WHERE u.id_kelas = '$id_kelas' AND k.jenis = 'Keluar'
");
$pengeluaran = mysqli_fetch_assoc($query_pengeluaran)['total'] ?? 0;

$saldo = $pemasukan - $pengeluaran;

// 2. Statistik Murid (Partisipasi)
$q_total_murid = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE id_kelas = '$id_kelas' AND id_role = 1 AND status = 'Aktif'");
$total_murid = mysqli_fetch_assoc($q_total_murid)['total'] ?? 0;

// Menghitung berapa murid yang sudah pernah bayar di tahun berjalan
$q_sudah_bayar = mysqli_query($conn, "
    SELECT COUNT(DISTINCT t.id_user) as total 
    FROM transaksi t
    JOIN user u ON t.id_user = u.id_user
    WHERE u.id_kelas = '$id_kelas' AND u.id_role = 1 AND t.tahun = '" . date('Y') . "'
");
$sudah_bayar = mysqli_fetch_assoc($q_sudah_bayar)['total'] ?? 0;

// 3. Riwayat Transaksi (Perhatikan nama kolom: nama_anggota)
$transaksi = mysqli_query($conn, "
    SELECT t.*, k.nama_kategori, k.jenis, a.nama_anggota 
    FROM transaksi t
    JOIN kategori k ON t.id_kategori = k.id_kategori
    JOIN user u ON t.id_user = u.id_user
    JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
    WHERE u.id_kelas = '$id_kelas'
    ORDER BY t.created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body class="bg-light">
    <?php include "../layout/navbar.php"; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <span class="text-muted">Periode: 2026</span>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <small class="text-muted text-uppercase fw-semibold">Total Saldo</small>
                        <h1 class="fw-bold mt-3 text-primary">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></h1>
                    </div>
                </div>
            </div>

            <div class="col-md-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Partisipasi Pembayaran Murid</small>
                        <?php $persen = $total_murid > 0 ? ($sudah_bayar / $total_murid) * 100 : 0; ?>
                        <div class="progress mt-3" style="height: 20px; border-radius: 10px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $persen; ?>%"><?php echo round($persen); ?>%</div>
                        </div>
                        <p class="mt-2 small text-muted"><?php echo $sud_bayar ?? $sudah_bayar; ?> dari <?php echo $total_murid; ?> murid telah membayar.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 p-3">
                    <h5>Grafik Kas</h5>
                    <div style="height: 300px;">
                        <canvas id="perbandinganKasChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Transaksi Terakhir</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Cek apakah query berhasil sebelum fetch
                                if ($transaksi && mysqli_num_rows($transaksi) > 0) {
                                    while ($row = mysqli_fetch_assoc($transaksi)) { ?>
                                        <tr>
                                            <td><?php echo $row['nama_anggota']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($row['jenis'] == 'Masuk' ? 'success' : 'danger'); ?>">
                                                    <?php echo $row['nama_kategori']; ?>
                                                </span>
                                            </td>
                                            <td>Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="3" class="text-center p-3 text-muted">Belum ada transaksi</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('perbandinganKasChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Masuk', 'Keluar'],
                datasets: [{
                    data: [<?php echo $pemasukan; ?>, <?php echo $pengeluaran; ?>],
                    backgroundColor: ['#0d6efd', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>

</html>