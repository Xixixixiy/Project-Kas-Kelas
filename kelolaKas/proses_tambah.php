<?php
session_start();
include "../connection/connection.php";

// --- 1. VALIDASI AKSES & INPUT ---
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'bendahara') {
    header("Location: ../login.php");
    exit;
}

if (
    empty($_POST['id_murid']) || empty($_POST['id_kategori']) ||
    empty($_POST['jumlah']) || empty($_POST['bulan']) ||
    empty($_POST['minggu']) || empty($_POST['tahun'])
) {
    echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
    exit;
}

// --- 2. DEKLARASI VARIABEL ---
$id_user    = $_SESSION['id_user']; // Ambil ID Bendahara dari session
$id_murid   = $_POST['id_murid'];
$id_kategori = $_POST['id_kategori'];
$jenis      = $_POST['jenis']; // 'Masuk'
$jumlah     = $_POST['jumlah'];
$keterangan = $_POST['keterangan'] ?? '';
$bulan      = $_POST['bulan'];
$minggu     = $_POST['minggu'];
$tahun      = $_POST['tahun']; // Ambil tahun dari form
$tanggal    = date('Y-m-d');

// --- 3. PENCARIAN DATA KELAS ---
$get = mysqli_query($conn, "SELECT id_kelas FROM murid WHERE id_murid = '$id_murid'");
$data = mysqli_fetch_assoc($get);
if (!$data) {
    echo "Murid tidak ditemukan!";
    exit;
}
$id_kelas = $data['id_kelas'];

// --- 4. LOGIKA FIFO (First In First Out) OTOMATIS ---

// Ubah M-3 menjadi integer 3
$target_minggu = (int) str_replace(['M', '-'], '', $minggu);
$berhasil = 0;

// A. Hitung berapa minggu yang belum dibayar dari M-1 sampai target
$lubang_kosong = 0;
for ($j = 1; $j <= $target_minggu; $j++) {
    $m_cek = "M-$j";
    // Cek di tabel transaksi baru (sesuaikan filter bulan DAN tahun)
    $cek_dulu = mysqli_query($conn, "SELECT id_transaksi FROM transaksi WHERE id_murid = '$id_murid' AND bulan = '$bulan' AND tahun = '$tahun' AND minggu = '$m_cek'");
    if (mysqli_num_rows($cek_dulu) == 0) {
        $lubang_kosong++;
    }
}

// B. Bagi nominal total dengan jumlah minggu yang baru akan dibayar
$nominal_per_minggu = ($lubang_kosong > 0) ? ($jumlah / $lubang_kosong) : 0;

if ($target_minggu > 0 && $lubang_kosong > 0) {
    for ($i = 1; $i <= $target_minggu; $i++) {
        $minggu_cek = "M-$i";

        // Cek lagi apakah minggu ini sudah ada datanya
        $cek = mysqli_query($conn, "SELECT id_transaksi FROM transaksi 
                                     WHERE id_murid = '$id_murid' AND bulan = '$bulan' 
                                     AND tahun = '$tahun' AND minggu = '$minggu_cek'");

        if (mysqli_num_rows($cek) == 0) {
            // INSERT ke struktur tabel baru
            // Kolom: id_user, id_kelas, id_kategori, id_murid, jumlah, tanggal, keterangan, bulan, tahun, minggu
            $query = "INSERT INTO transaksi (id_user, id_kelas, id_kategori, id_murid, jumlah, tanggal, keterangan, bulan, tahun, minggu)
                      VALUES ('$id_user', '$id_kelas', '$id_kategori', '$id_murid', '$nominal_per_minggu', '$tanggal', '$keterangan', '$bulan', '$tahun', '$minggu_cek')";

            if (mysqli_query($conn, $query)) {
                $berhasil++;
            }
        }
    }
} else {
    echo "<script>
            alert('Tidak ada minggu yang perlu diisi!');
            window.location='pemasukkan.php';
          </script>";
    exit;
}

// --- 5. FEEDBACK ---
if ($berhasil > 0) {
    echo "<script>
            alert('Berhasil mencatat $berhasil minggu!'); 
            window.location='pemasukkan.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal menyimpan data atau data sudah ada.'); 
            window.location='pemasukkan.php';
          </script>";
}
