<?php
session_start();
include __DIR__ . "/../config/database.php";

// --- 1. AMBIL DATA DARI FORM ---
$id_kelas    = $_SESSION['id_kelas']; // Kita ambil dari session agar data terkunci pada kelas tersebut
$jumlah      = $_POST['jumlah'];
$keterangan  = $_POST['keterangan'];
$jenis       = "Keluar"; // Sesuai input hidden atau set manual
$tgl_today   = date('Y-m-d'); // Mengambil tanggal hari ini otomatis

$bulan_sekarang = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
][(int)date('m') - 1]; // Ambil nama bulan dari array

// --- 2. VALIDASI BACKEND (Opsional tapi penting) ---
// Kita cek lagi saldonya di sini buat jaga-jaga kalau user "nakal" bypass Javascript
$q_masuk  = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Masuk'");
$q_keluar = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis='Keluar'");
$saldo    = mysqli_fetch_assoc($q_masuk)['total'] - mysqli_fetch_assoc($q_keluar)['total'];

if ($jumlah > $saldo) {
    echo "<script>alert('Error: Saldo tidak mencukupi!'); window.location='pengeluaran.php';</script>";
    exit;
}

// --- 3. QUERY SIMPAN ---
// Perhatikan: Kita hanya mengisi kolom yang perlu saja. 
// id_murid, bulan, dan minggu dibiarkan kosong karena ini pengeluaran kelas.
$query = mysqli_query($conn, "INSERT INTO transaksi (id_kelas, tanggal, jenis, jumlah, keterangan, bulan) 
          VALUES ('$id_kelas', '$tgl_today', 'Keluar', '$jumlah', '$keterangan', '$bulan_sekarang')");

// --- 4. REDIRECT SETELAH BERHASIL ---
if ($query) {
    echo "<script>
            alert('Berhasil! Pengeluaran telah dicatat.');
            window.location='pengeluaran.php';
          </script>";
} else {
    echo "Gagal simpan data: " . mysqli_error($conn);
}
