<?php
session_start();
include "../connection/connection.php";

// --- 1. VALIDASI INPUT ---
// Memastikan semua data yang dikirim dari form tidak ada yang kosong
if (
    !isset($_POST['id_murid']) || !isset($_POST['jenis']) || !isset($_POST['jumlah']) ||
    !isset($_POST['bulan']) || !isset($_POST['minggu'])
) {
    echo "Data tidak lengkap!";
    exit;
}

// --- 2. DEKLARASI VARIABEL ---
$id_murid   = $_POST['id_murid'];
$jenis      = $_POST['jenis'];
$jumlah     = $_POST['jumlah'];
$keterangan = $_POST['keterangan'] ?? '';
$bulan      = $_POST['bulan'];
$minggu     = $_POST['minggu'];
$tanggal    = date('Y-m-d'); // Mengambil tanggal hari ini untuk catatan transaksi

// --- 3. PENCARIAN DATA KELAS ---
// Kita ambil id_kelas berdasarkan murid agar data transaksi terkunci pada kelas tersebut
$get = mysqli_query($conn, "SELECT id_kelas FROM murid WHERE id_murid = '$id_murid'");
$data = mysqli_fetch_assoc($get);

if (!$data) {
    echo "Murid tidak ditemukan!";
    exit;
}
$id_kelas = $data['id_kelas'];

// --- 4. CEK DUPLIKASI (LOGIKA ANTI-DOUBLE) ---
// Mencegah murid membayar di minggu dan bulan yang sama dua kali
if ($jenis == 'Masuk') {
    $cek = mysqli_query($conn, "SELECT * FROM transaksi 
        WHERE id_murid = '$id_murid' 
        AND bulan = '$bulan' 
        AND minggu = '$minggu'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
                alert('Gagal! Murid ini sudah tercatat membayar pada $bulan $minggu');
                window.history.back();
              </script>";
        exit;
    }
}

// --- 5. EKSEKUSI PENYIMPANAN ---
// Query untuk memasukkan data ke tabel transaksi
$query = "INSERT INTO transaksi (id_murid, id_kelas, tanggal, minggu, bulan, jenis, jumlah, keterangan)
          VALUES ('$id_murid', '$id_kelas', '$tanggal', '$minggu', '$bulan', '$jenis', '$jumlah', '$keterangan')";

if (mysqli_query($conn, $query)) {
    echo "<script>alert('Berhasil!'); window.location='../kelolaKas/pemasukkan.php';</script>";
} else {
    echo "Error Sistem: " . mysqli_error($conn);
}
