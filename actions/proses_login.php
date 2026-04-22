<?php
session_start();
include "../config/database.php"; // Pastikan path ke config benar

if (isset($_POST['nisn_nip']) && isset($_POST['password'])) {
    
    $identitas = mysqli_real_escape_string($conn, $_POST['nisn_nip']);
    $password  = $_POST['password'];

    // QUERY DISESUAIKAN DENGAN STRUKTUR BARU
    // Kita ambil data user, nama dari anggota_kelas, dan nama role-nya
    $sql = "SELECT u.*, r.role, a.nama_anggota 
            FROM user u
            JOIN role r ON u.id_role = r.id_role
            JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
            WHERE u.identitas = '$identitas' 
            AND u.status = 'Aktif'";

    $result = mysqli_query($conn, $sql);

    // CEK APAKAH QUERY BERHASIL
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);

            // Verifikasi Password (Plain text dulu sesuai permintaanmu)
            if ($password === $data['password']) {
                $_SESSION['id_user']  = $data['id_user'];
                $_SESSION['nama']     = $data['nama_lengkap'];
                $_SESSION['role']     = $data['role'];
                $_SESSION['id_kelas'] = $data['id_kelas'];

                // Redirect berdasarkan role
                if ($data['role'] == 'Bendahara') {
                    header("Location: ../dashboard_bendahara.php");
                } elseif ($data['role'] == 'wali_kelas') {
                    header("Location: ../dashboard_wali.php");
                } else {
                    header("Location: ../index.php"); // Untuk murid/umum
                }
                exit;
            } else {
                echo "<script>alert('Login Gagal!'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('User tidak ditemukan atau Akun Non-Aktif!'); window.history.back();</script>";
        }
    } else {
        // JIKA QUERY ERROR, tampilkan pesan errornya untuk debug
        die("Error Query: " . mysqli_error($conn));
    }
}
?>