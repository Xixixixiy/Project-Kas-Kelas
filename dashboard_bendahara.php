<?php
session_start();
include "connection/connection.php";

// proteksi
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'bendahara') {
    echo "Akses ditolak!";
    exit;
}

// ambil id_kelas dari session
$id_kelas = $_SESSION['id_kelas'];

// hitung saldo, pemasukan, pengeluaran
$query = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN jenis='Masuk' THEN jumlah ELSE 0 END) as pemasukan,
        SUM(CASE WHEN jenis='Keluar' THEN jumlah ELSE 0 END) as pengeluaran
    FROM transaksi
    WHERE id_kelas = '$id_kelas'
");

$data = mysqli_fetch_assoc($query);

$pemasukan = $data['pemasukan'] ?? 0;
$pengeluaran = $data['pengeluaran'] ?? 0;
$saldo = $pemasukan - $pengeluaran;


// ambil transaksi terbaru
$transaksi = mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE id_kelas = '$id_kelas'
    ORDER BY tanggal DESC
    LIMIT 5
");

// total murid
$q_murid = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM murid 
    WHERE id_kelas = '$id_kelas'
");
$total_murid = mysqli_fetch_assoc($q_murid)['total'];


// murid yang sudah bayar
$q_bayar = mysqli_query($conn, "
    SELECT COUNT(DISTINCT id_murid) as sudah_bayar
    FROM transaksi
    WHERE id_kelas = '$id_kelas' AND jenis='Masuk' AND id_murid IS NOT NULL
");
$sudah_bayar = mysqli_fetch_assoc($q_bayar)['sudah_bayar'];

$belum_bayar = $total_murid - $sudah_bayar;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Kas Kelas</title>

    <!-- Bootstrap CSS dan Icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Javascript untuk chart/diagram -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CSS custom -->
    <link rel="stylesheet" href="style/style.css">
</head>

<body class="bg-light">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container-fluid">

            <a class="navbar-brand fw-bold text-primary" href="#">
                Kas Kelas
            </a>

            <!-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button> -->

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard_bendahara.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="kelolaKas/pemasukkan.php">
                            Kelola Kas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="status_kas.php">
                            Status Kas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="detail_kas.php">
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

    <!-- CONTENT -->
    <div class="container mt-4">

        <div class="container mt-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Dashboard</h4>
                <span class="text-muted">Periode: Januari 2026</span>
            </div>

            <div class="row">

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body d-flex flex-column justify-content-center text-center">

                            <small class="text-muted text-uppercase fw-semibold" style="letter-spacing: 1px;">
                                Pembayaran Kas
                            </small>

                            <h1 class="fw-bold mt-3 mb-1 text-primary">
                                <?php echo $sudah_bayar; ?> <span class="text-muted fs-4">/ <?php echo $total_murid; ?></span>
                            </h1>

                            <?php
                            // Hitung persentase untuk progress bar [cite: 5, 53]
                            $persen = $total_murid > 0 ? ($sudah_bayar / $total_murid) * 100 : 0;
                            ?>

                            <div class="px-4 mt-3">
                                <div class="progress" style="height: 10px; border-radius: 10px;">
                                    <div class="progress-bar bg-success shadow-sm"
                                        role="progressbar"
                                        style="width: <?php echo $persen; ?>%"
                                        aria-valuenow="<?php echo $persen; ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <strong><?php echo round($persen); ?>%</strong> murid sudah lunas
                                </small>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h5>Perbandingan Kas</h5>
                            <div class="d-flex justify-content-center align-items-center" style="position: relative; height: 250px;">
                                <canvas id="perbandinganKasChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Ringkasan kas -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Saldo Kas</small>
                            <h4 class="fw-bold text-primary">
                                Rp <?php echo number_format($saldo, 0, ',', '.'); ?>
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Pemasukan</small>
                            <h4 class="fw-bold text-success">
                                Rp <?php echo number_format($pemasukan, 0, ',', '.'); ?>
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <small class="text-muted">Pengeluaran</small>
                            <h4 class="fw-bold text-danger">
                                Rp <?php echo number_format($pengeluaran, 0, ',', '.'); ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat transaksi -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">
                    Riwayat Transaksi
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($transaksi)) { ?>
                                <tr>
                                    <td><?php echo $row['tanggal']; ?></td>
                                    <td><?php echo $row['keterangan']; ?></td>
                                    <td>
                                        <?php if ($row['jenis'] == 'Masuk') { ?>
                                            <span class="badge bg-success">Masuk</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Keluar</span>
                                        <?php } ?>
                                    </td>
                                    <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <script>
            // 1. Ambil data dari PHP (Gunakan variabel yang sudah ada di atas)
            // Kita kasih nilai default 0 jika variabel PHP-nya kosong
            const dataMasuk = <?= $pemasukan ?? 0 ?>;
            const dataKeluar = <?= $pengeluaran ?? 0 ?>;
            const sisaSaldo = dataMasuk - dataKeluar;

            // 2. Cek apakah ada data. Kalau 0 semua, kita kasih angka bayangan agar chart muncul
            // Ini trik supaya pas presentasi chart-nya nggak hilang kalau datanya kosong
            const chartData = (dataMasuk === 0 && dataKeluar === 0) ? [1, 0] : [sisaSaldo, dataKeluar];
            const chartLabels = (dataMasuk === 0 && dataKeluar === 0) ? ['Belum ada data', 'Pengeluaran'] : ['Saldo Kas', 'Pengeluaran'];

            // 3. Inisialisasi Chart
            const ctx = document.getElementById('perbandinganKasChart').getContext('2d');

            // Pastikan library Chart.js sudah ter-load
            if (typeof Chart !== 'undefined') {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            data: chartData,
                            backgroundColor: [
                                '#0d6efd', // Biru Primary
                                '#dc3545' // Merah Danger
                            ],
                            hoverOffset: 4,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.raw || 0;
                                        return label + ': Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        cutout: '70%' // Membuat lubang tengah lebih besar (lebih modern)
                    }
                });
            } else {
                console.error("Library Chart.js tidak ditemukan! Pastikan koneksi internet stabil.");
            }
        </script>
</body>

</html>