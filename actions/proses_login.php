<?php
session_start();

// Cek apakah file benar-benar ada secara fisik
$path_db = __DIR__ . "/../config/database.php";

if (!file_exists($path_db)) {
    die("Error: File database.php tidak ditemukan di: " . $path_db);
}

include $path_db;

// Tes apakah variabel $conn ada setelah di-include
if (!isset($conn)) {
    die("Error: Variabel \$conn tidak ditemukan. Periksa isi file config/database.php kamu.");
}

if (isset($_POST['nisn_nik']) && isset($_POST['password'])) {

    $identitas = mysqli_real_escape_string($conn, $_POST['nisn_nik']);
    $password  = $_POST['password'];

    $sql = "SELECT u.*, r.role, a.nama_anggota 
            FROM user u
            JOIN role r ON u.id_role = r.id_role
            JOIN anggota_kelas a ON u.id_anggota = a.id_anggota
            WHERE u.identitas = '$identitas' 
            AND u.status = 'Aktif'";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // Debug: Pastikan password di database sama dengan input
        if ($password === $data['password']) {
            $_SESSION['id_user']  = $data['id_user'];
            $_SESSION['nama']     = $data['nama_anggota'];
            $_SESSION['role']     = $data['role'];
            $_SESSION['id_kelas'] = $data['id_kelas'];

            // --- BAGIAN DEBUG MULAI DI SINI ---
            // Kita matikan redirect-nya dulu biar bisa liat datanya
            // echo "<h2>Login Berhasil!</h2>";
            // echo "Data Session yang tersimpan:<br>";
            // echo "Nama: " . $_SESSION['nama'] . "<br>";
            // echo "Role: " . $_SESSION['role'] . "<br>";
            // echo "ID Kelas: " . $_SESSION['id_kelas'] . "<br>";
            // echo "<hr>";

            $role_cek = strtolower(trim($data['role']));
            $target = "";
            if ($role_cek == 'bendahara') {
                $target = "bendahara/dashboard.php";
            } elseif ($role_cek == 'wali kelas' || $role_cek == 'wali_kelas') {
                $target = "wali_kelas/dashboard.php";
            } elseif ($role_cek == 'ketua kelas' || $role_cek == 'ketua_kelas') {
                $target = "ketua_kelas/dashboard.php";
            } else {
                $target = "murid/dashboard.php";
            }

            echo "Link tujuan manual: <a href='" . $base_url . $target . "'>KLIK DI SINI UNTUK KE DASHBOARD</a>";
            exit;
        } else {
            echo "<script>alert('Password Salah!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User tidak ditemukan atau Akun Tidak Aktif!'); window.history.back();</script>";
    }
}


echo $_SESSION['role'];
