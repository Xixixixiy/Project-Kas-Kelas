<?php
session_start();
include 'connection/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Mengambil nama kelas dari database
$id_kelas = $_SESSION['id_kelas'];

$query = mysqli_query($conn, "SELECT nama_kelas FROM kelas WHERE id_kelas = '$id_kelas'");
$data = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>Dashboard</h2>

    <p><strong>Role:</strong> <?= $_SESSION['role']; ?></p>
    <p><strong>Kelas:</strong> <?= $data['nama_kelas'] ?></p>

    <a href="murid/data_murid.php" class="btn btn-primary">Data Murid</a>
    <a href="transaksi/data_transaksi.php" class="btn btn-success">Transaksi</a>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</body>

</html>