<?php
session_start();
include __DIR__ . "/../config/database.php";

// --- 1. VALIDASI AKSES & INPUT ---
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) != 'bendahara') {
    header("Location: ../login.php");
    exit;
}

// Validasi dasar (Minggu hanya wajib jika kategori adalah Uang Kas / ID 1)
$id_kategori = $_POST['id_kategori'];
if (empty($_POST['id_user']) || empty($id_kategori) || ($id_kategori == 1 && empty($_POST['minggu']))) {
    $_SESSION['error'] = "Data tidak lengkap!";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// --- 2. DEKLARASI VARIABEL ---
$id_pembayar   = $_POST['id_user'];
$nominal_total = $_POST['nominal'];
$keterangan    = $_POST['keterangan'] ?? 'Pembayaran Kas';
$bulan         = $_POST['bulan'];
$tahun         = $_POST['tahun'];
$tanggal       = date('Y-m-d H:i:s');
$berhasil      = 0;

// --- 3. PERCABANGAN LOGIKA BERDASARKAN KATEGORI ---

if ($id_kategori != 1) {
    // --- JIKA KATEGORI ADALAH DENDA / SUMBANGAN (BUKAN KAS) ---
    // Langsung insert satu kali, kolom minggu dikosongkan atau diisi '-'
    $query_ins = "INSERT INTO transaksi (id_user, id_kategori, nominal, keterangan, bulan, tahun, minggu, created_at)
                  VALUES ('$id_pembayar', '$id_kategori', '$nominal_total', '$keterangan', '$bulan', '$tahun', '-', '$tanggal')";

    if (mysqli_query($conn, $query_ins)) {
        $berhasil = 1;
    } else {
        die("Gagal Simpan Denda: " . mysqli_error($conn));
    }
} else {
    // --- JIKA KATEGORI ADALAH UANG KAS (ID 1) -> PAKAI LOGIKA FIFO ---
    $minggu_input  = $_POST['minggu'];
    $target_minggu = (int) str_replace(['M', '-'], '', $minggu_input);

    // A. Hitung lubang kosong
    $lubang_kosong = 0;
    for ($j = 1; $j <= $target_minggu; $j++) {
        $m_cek = "M-$j";
        $sql_cek = "SELECT id_transaksi FROM transaksi 
                    WHERE id_user = '$id_pembayar' AND bulan = '$bulan' AND tahun = '$tahun' AND minggu = '$m_cek' AND id_kategori = 1";
        $res_cek = mysqli_query($conn, $sql_cek);
        if (mysqli_num_rows($res_cek) == 0) {
            $lubang_kosong++;
        }
    }

    // B. Eksekusi Insert FIFO
    if ($target_minggu > 0 && $lubang_kosong > 0) {
        $nominal_per_minggu = $nominal_total / $lubang_kosong;

        for ($i = 1; $i <= $target_minggu; $i++) {
            $minggu_ins = "M-$i";
            $cek_akhir = mysqli_query($conn, "SELECT id_transaksi FROM transaksi WHERE id_user = '$id_pembayar' AND bulan = '$bulan' AND tahun = '$tahun' AND minggu = '$minggu_ins' AND id_kategori = 1");

            if (mysqli_num_rows($cek_akhir) == 0) {
                $query_ins = "INSERT INTO transaksi (id_user, id_kategori, nominal, keterangan, bulan, tahun, minggu, created_at)
                              VALUES ('$id_pembayar', '1', '$nominal_per_minggu', '$keterangan', '$bulan', '$tahun', '$minggu_ins', '$tanggal')";

                if (mysqli_query($conn, $query_ins)) {
                    $berhasil++;
                }
            }
        }
    }
}

// --- 4. FEEDBACK ---
if ($berhasil > 0) {
    $pesan = ($id_kategori == 1) ? "Berhasil mencatat $berhasil minggu!" : "Berhasil mencatat data transaksi!";
    echo "<script>alert('$pesan'); window.location='../transaksi_masuk.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan data. Minggu mungkin sudah lunas.'); window.history.back();</script>";
}
