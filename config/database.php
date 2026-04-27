<?php
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "db_kas_v2";

$conn = mysqli_connect($servername, $username, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Sesuaikan dengan nama folder proyekmu di htdocs
$base_url = "http://localhost/projectKasKelas/";
?>