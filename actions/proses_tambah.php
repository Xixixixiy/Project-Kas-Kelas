<?php
session_start();
include "../config/database.php";

// --- 1. VALIDASI AKSES & INPUT ---
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'bendahara') {
    header("Location: ../login.php");
    exit;
}

// Debugging: Cek apa saja yang dikirim dari form (Hapus bagian ini jika sudah lancar)
if (empty($_POST['id_user']) || empty($_POST['id_kategori']) || empty($_POST['minggu'])) {
    $_SESSION['error'] = "Data tidak lengkap! Pastikan Anda sudah mengisi semua field yang diperlukan.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// --- 2. DEKLARASI VARIABEL ---
$id_pembayar   = $_POST['id_user'];
$id_kategori   = $_POST['id_kategori'];
$nominal_total = $_POST['nominal'];
$keterangan    = $_POST['keterangan'] ?? 'Pembayaran Kas';
$bulan         = $_POST['bulan'];
$minggu_input  = $_POST['minggu'];
$tahun         = $_POST['tahun'];
$tanggal       = date('Y-m-d H:i:s');

// --- 3. LOGIKA FIFO ---
$target_minggu = (int) str_replace(['M', '-'], '', $minggu_input);
$berhasil = 0;

// A. Hitung lubang kosong
$lubang_kosong = 0;
for ($j = 1; $j <= $target_minggu; $j++) {
    $m_cek = "M-$j";
    $sql_cek = "SELECT id_transaksi FROM transaksi 
                WHERE id_user = '$id_pembayar' 
                AND bulan = '$bulan' 
                AND tahun = '$tahun' 
                AND minggu = '$m_cek'";
    $res_cek = mysqli_query($conn, $sql_cek);
    if (mysqli_num_rows($res_cek) == 0) {
        $lubang_kosong++;
    }
}

// B. Eksekusi Insert
if ($target_minggu > 0 && $lubang_kosong > 0) {
    $nominal_per_minggu = $nominal_total / $lubang_kosong;

    for ($i = 1; $i <= $target_minggu; $i++) {
        $minggu_ins = "M-$i";
        $cek_akhir = mysqli_query($conn, "SELECT id_transaksi FROM transaksi WHERE id_user = '$id_pembayar' AND bulan = '$bulan' AND tahun = '$tahun' AND minggu = '$minggu_ins'");

        if (mysqli_num_rows($cek_akhir) == 0) {
            // Sesuai Screenshot: id_user, id_kategori, nominal, keterangan, bulan, tahun, minggu, created_at
            $query_ins = "INSERT INTO transaksi (id_user, id_kategori, nominal, keterangan, bulan, tahun, minggu, created_at)
                          VALUES ('$id_pembayar', '$id_kategori', '$nominal_per_minggu', '$keterangan', '$bulan', '$tahun', '$minggu_ins', '$tanggal')";

            if (mysqli_query($conn, $query_ins)) {
                $berhasil++;
            } else {
                // JIKA ERROR SQL, TAMPILKAN DI SINI
                die("Gagal Query Insert: " . mysqli_error($conn));
            }
        }
    }
} else {
    echo "<script>alert('Minggu ini sudah lunas atau data tidak valid!'); window.location='../transaksi_masuk.php';</script>";
    exit;
}

// DEBUG TEMPORER
if ($berhasil == 0) {
    echo "Debug Info:<br>";
    echo "Target Minggu: $target_minggu <br>";
    echo "Lubang Kosong: $lubang_kosong <br>";
    echo "ID Pembayar: $id_pembayar <br>";
    exit;
}

// --- 4. FEEDBACK ---
if ($berhasil > 0) {
    echo "<script>alert('Berhasil mencatat $berhasil minggu!'); window.location='../transaksi_masuk.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan data tanpa pesan error SQL.'); window.history.back();</script>";
}
