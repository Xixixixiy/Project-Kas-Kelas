<?php
session_start();
include __DIR__ . "/../config/database.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_POST['id_user'];
    $status_lama = $_POST['status_sekarang'];

    // Toggle status
    $status_baru = ($status_lama == 'Aktif') ? 'Non-Aktif' : 'Aktif';

    $sql = "UPDATE user SET status = '$status_baru' WHERE id_user = '$id_user'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Status siswa berhasil diubah!');
                window.location = '../wali_kelas/kelola_siswa.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
