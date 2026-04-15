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

// --- 4. LOGIKA FIFO OTOMATIS ---

$target_minggu = (int) str_replace(['M', '-'], '', $minggu);
$berhasil = 0;

// HITUNG DULU: Berapa sih lubang yang mau diisi?
$lubang_kosong = 0;
for ($j = 1; $j <= $target_minggu; $j++) {
    $m_cek = "M-$j";
    $cek_dulu = mysqli_query($conn, "SELECT id_transaksi FROM transaksi WHERE id_murid = '$id_murid' AND bulan = '$bulan' AND minggu = '$m_cek' AND jenis = 'Masuk'");
    if (mysqli_num_rows($cek_dulu) == 0) {
        $lubang_kosong++;
    }
}

// BAGI NOMINALNYA: Total uang dibagi jumlah lubang
// Jadi kalau input 20.000 untuk 4 minggu, nominal_per_minggu jadi 5.000
$nominal_per_minggu = ($lubang_kosong > 0) ? ($jumlah / $lubang_kosong) : 0;

if ($target_minggu > 0 && $lubang_kosong > 0) {
    for ($i = 1; $i <= $target_minggu; $i++) {
        $minggu_cek = "M-$i";

        $cek = mysqli_query($conn, "SELECT * FROM transaksi 
            WHERE id_murid = '$id_murid' AND bulan = '$bulan' 
            AND minggu = '$minggu_cek' AND jenis = 'Masuk'");

        if (mysqli_num_rows($cek) == 0) {
            // Gunakan $nominal_per_minggu, BUKAN $jumlah (total)
            $query = "INSERT INTO transaksi (id_murid, id_kelas, tanggal, minggu, bulan, jenis, jumlah, keterangan)
                      VALUES ('$id_murid', '$id_kelas', '$tanggal', '$minggu_cek', '$bulan', '$jenis', '$nominal_per_minggu', '$keterangan')";

            if (mysqli_query($conn, $query)) {
                $berhasil++;
            }
        }
    }
} else {
    echo "<script>
            alert('Tidak ada minggu yang perlu diisi atau target minggu tidak valid!');
            window.location='../kelolaKas/pemasukkan.php';
          </script>";
}

// --- 5. FEEDBACK ---
if ($berhasil > 0) {
    echo "<script>
            alert('Berhasil mencatat $berhasil minggu!'); 
            window.location='../kelolaKas/pemasukkan.php';
          </script>";
} else {
    echo "<script>
            alert('Tidak ada data baru yang disimpan (Mungkin sudah lunas)'); 
            window.location='../kelolaKas/pemasukkan.php';
          </script>";
}
