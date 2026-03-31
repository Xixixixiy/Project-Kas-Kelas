<?php
session_start();
include "connection/koneksi.php";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM user WHERE username='$username' AND password='$password'");
    $user = mysqli_fetch_assoc($query);

    if ($user) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id_kelas'] = $user['id_kelas'];
        $_SESSION['id_murid'] = $user['id_murid'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Login gagal!";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

    <div class="col-md-4 mx-auto">
        <h3 class="mb-3">Login Kas Kelas</h3>

        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST">
            <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

            <button name="login" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

</body>

</html>