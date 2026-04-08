<?php
session_start();
include "../connection/connection.php";

// --- 1. AMBIL DATA DARI FORM ---
$jumlah      = $_POST['jumlah'];
$keterangan  = $_POST['keterangan'];
$jenis       = "Keluar"; // Sesuai input hidden atau set manual
$tgl_today   = date('Y-m-d'); // Mengambil tanggal hari ini otomatis

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
$query = mysqli_query($conn, "INSERT INTO transaksi (jumlah, jenis, keterangan, tgl_transaksi) 
                              VALUES ('$jumlah', '$jenis', '$keterangan', '$tgl_today')");

// --- 4. REDIRECT SETELAH BERHASIL ---
if ($query) {
    echo "<script>
            alert('Berhasil! Pengeluaran telah dicatat.');
            window.location='pengeluaran.php';
          </script>";
} else {
    echo "Gagal simpan data: " . mysqli_error($conn);
}
