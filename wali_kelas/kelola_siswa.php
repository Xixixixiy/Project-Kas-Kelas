<?php
session_start();
include "../config/database.php";

// Validasi Akses
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'wali_kelas') {
    header("Location: ../login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// Query ambil data: identitas diambil dari tabel user (u)
$sql = "SELECT u.id_user, u.identitas, u.status, a.nama_anggota 
        FROM user u
        JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
        WHERE u.id_kelas = '$id_kelas' AND u.id_role = 1
        ORDER BY a.nama_anggota ASC";
$query_siswa = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Siswa - Wali Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <?php include "../layout/navbar.php"; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Kelola Siswa</h2>
                <p class="text-muted small">Aktifkan atau Non-Aktifkan siswa untuk akses aplikasi</p>
            </div>
            <div class="badge bg-primary px-3 py-2">
                Total Siswa: <?= mysqli_num_rows($query_siswa) ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th class="ps-4 py-3">No</th>
                                <th>Nama Siswa</th>
                                <th>NISN / Username</th>
                                <th>Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($s = mysqli_fetch_assoc($query_siswa)) :
                            ?>
                                <tr>
                                    <td class="ps-4"><?= $no++ ?></td>
                                    <td class="fw-bold"><?= $s['nama_anggota'] ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-card-text me-1"></i> <?= $s['identitas'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($s['status'] == 'Aktif') : ?>
                                            <span class="badge bg-success-subtle text-success border border-success">Aktif</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <form action="../actions/update_status_siswa.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_user" value="<?= $s['id_user'] ?>">
                                            <input type="hidden" name="status_sekarang" value="<?= $s['status'] ?>">

                                            <?php if ($s['status'] == 'Aktif') : ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                                    <i class="bi bi-person-x me-1"></i> Non-Aktifkan
                                                </button>
                                            <?php else : ?>
                                                <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                                    <i class="bi bi-person-check me-1"></i> Aktifkan
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>