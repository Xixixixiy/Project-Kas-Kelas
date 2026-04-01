<?php
session_start();
include "../connection/connection.php";

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM user WHERE nisn_nip='$username'");
$data = mysqli_fetch_assoc($query);

if ($data) {

    if ($password == $data['password']) {

        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['id_kelas'] = $data['id_kelas'];

        // redirect sesuai role
        if ($data['role'] == 'bendahara') {
            header("Location: ../dashboard_bendahara.php");
        } elseif ($data['role'] == 'murid') {
            header("Location: dashboard_murid.php");
        } else {
            header("Location: dashboard_admin.php");
        }

        exit;

    } else {
        echo "Password salah";
    }

} else {
    echo "User tidak ditemukan";
}
?>