<?php
session_start();
include '../connection/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$id_kelas = $_SESSION['id_kelas'];

// Mengambil data murid berdasarkan id_kelas
$query = mysqli_query($conn, "SELECT * FROM murid WHERE id_kelas = '$id_kelas'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Murid</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <h2>Data Murid</h2>

    <a href="../index.php" class="btn btn-secondary mb-3">Kembali</a>
    <a href="tambah_murid.php" class="btn btn-primary mb-3">Tambah Murid</a>

    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Nama Murid</th>
            <th>Status</th>
        </tr>

        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)) {
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
        <?php } ?>

    </table>
</body>

</html>