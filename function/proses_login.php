<?php
session_start();
include "../connection/connection.php";

if (!isset($_POST['nisn_nip']) || !isset($_POST['password'])) {
    echo "Data login tidak lengkap!";
    exit;
}

$nisn_nip = $_POST['nisn_nip'];
$password = $_POST['password'];

// QUERY USER
$query = mysqli_query($conn, "SELECT * FROM user WHERE nisn_nip='$nisn_nip' AND password='$password'");

if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);

    // SIMPAN SESSION
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['nama']    = $data['nama'];
    $_SESSION['role']    = $data['role'];
    $_SESSION['id_kelas'] = $data['id_kelas'];

    header("Location: ../dashboard_bendahara.php");
    exit;
} else {
    echo "<script>alert('Login gagal!'); window.history.back();</script>";
}
