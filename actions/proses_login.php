<?php
session_start();
include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

// Gunakan identitas (NISN/NIK) sesuai database baru
$identitas = mysqli_real_escape_string($conn, $_POST['nisn_nip']);
$password  = $_POST['password'];

// Kita JOIN ke tabel role dan anggota_kelas untuk ambil data lengkap
$sql = "SELECT u.*, r.role, a.nama_lengkap 
        FROM user u
        JOIN role r ON u.id_role = r.id_role
        JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
        WHERE u.identitas = '$identitas' AND u.status = 'Aktif' 
        LIMIT 1";

$query = mysqli_query($conn, $sql);

if (mysqli_num_rows($query) === 1) {
    $user = mysqli_fetch_assoc($query);

    // Laravel menggunakan password_verify. 
    // Untuk sekarang kita pakai plain text dulu, tapi kedepannya gunakan: 
    // if (password_verify($password, $user['password']))
    if ($password === $user['password']) {

        // Simpan Session sesuai standar baru
        $_SESSION['login']      = true;
        $_SESSION['id_user']    = $user['id_user'];
        $_SESSION['nama']       = $user['nama_lengkap'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['id_kelas']   = $user['id_kelas'];

        // Laravel Way: Redirect berdasarkan Role
        if ($user['role'] === 'bendahara') {
            header("Location: ../dashboard_bendahara.php");
        } elseif ($user['role'] === 'wali_kelas') {
            header("Location: ../dashboard_wali.php");
        } else {
            header("Location: ../status_kas.php"); // Untuk murid
        }
        exit;
    }
}

// Jika gagal
echo "<script>alert('Identitas salah, password salah, atau akun non-aktif!'); window.history.back();</script>";
