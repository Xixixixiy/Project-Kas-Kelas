<?php
session_start();
include "../connection/connection.php";

// VALIDASI
if (
    !isset($_POST['id_murid']) ||
    !isset($_POST['jenis']) ||
    !isset($_POST['jumlah']) ||
    !isset($_POST['bulan']) ||
    !isset($_POST['minggu'])
) {
    echo "Data tidak lengkap!";
    exit;
}

$id_murid   = $_POST['id_murid'];
$jenis      = $_POST['jenis'];
$jumlah     = $_POST['jumlah'];
$keterangan = $_POST['keterangan'] ?? '';
$bulan      = $_POST['bulan'];
$minggu     = $_POST['minggu'];
$tanggal    = date('Y-m-d');

// AMBIL id_kelas DARI DATABASE
$get = mysqli_query($conn, "SELECT id_kelas FROM murid WHERE id_murid = '$id_murid'");
$data = mysqli_fetch_assoc($get);

if (!$data) {
    echo "Murid tidak ditemukan!";
    exit;
}

$id_kelas = $data['id_kelas'];

// CEK DUPLIKASI (khusus Masuk)
if ($jenis == 'Masuk') {
    $cek = mysqli_query($conn, "SELECT * FROM transaksi 
        WHERE id_murid = '$id_murid' 
        AND bulan = '$bulan' 
        AND minggu = '$minggu'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
                alert('Sudah bayar di $bulan $minggu');
                window.history.back();
              </script>";
        exit;
    }
}

// LOGIKA id_murid
$id_murid_val = ($jenis == 'Keluar') ? "NULL" : "'$id_murid'";

// INSERT
$query = "INSERT INTO transaksi 
    (id_murid, id_kelas, tanggal, minggu, bulan, jenis, jumlah, keterangan)
    VALUES 
    ($id_murid_val, '$id_kelas', '$tanggal', '$minggu', '$bulan', '$jenis', '$jumlah', '$keterangan')";

if (mysqli_query($conn, $query)) {
    echo "<script>alert('Berhasil!'); window.location='../kelolaKas/pemasukkan.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}

?>