<?php
include '../connection/connection.php';

function getMurid($conn)
{
    $sql = "SELECT nisn, nama FROM murid";
    return $conn->query($sql);
}

function simpanData($conn)
{

    $nisn = $_POST['murid'];
    $bulan = $_POST['bulan'];
    $minggu = $_POST['minggu_ke'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    $stmt = $conn->prepare("INSERT INTO transaksi (nisn, bulan, minggu_ke, jumlah, keterangan, jenis) VALUES (?, ?, ?, ?, ?, 'Masuk')");
    $stmt->bind_param("isiis", $nisn, $bulan, $minggu, $jumlah, $keterangan);

    if ($stmt->execute()) {
        header("Location: pemasukkan.php?success=1");
        exit();
    } else {
        echo "Gagal menyimpan data";
    }
}
