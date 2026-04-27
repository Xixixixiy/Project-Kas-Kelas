<?php
// Tentukan folder aktif berdasarkan role untuk link dashboard
$role_folder = (strtolower($_SESSION['role']) == 'bendahara') ? 'bendahara' : 'wali_kelas';
?>

<nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="<?= $base_url ?>dashboard.php">Kas Kelas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>"
                        href="<?= $base_url . $role_folder ?>/dashboard.php">Dashboard</a>
                </li>

                <?php if (strtolower($_SESSION['role']) == 'bendahara') : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'transaksi_masuk.php') ? 'active' : '' ?>"
                            href="<?= $base_url ?>bendahara/transaksi_masuk.php">Kelola Kas</a>
                    </li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] == 'Wali Kelas') : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'kelola_siswa.php') ? 'active' : '' ?>"
                            href="<?= $base_url ?>wali_kelas/kelola_siswa.php">
                            <i class="bi bi-people me-1"></i> Kelola Siswa
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'status_kas.php') ? 'active' : '' ?>"
                        href="<?= $base_url ?>status_kas.php">Status Kas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'detail_kas.php') ? 'active' : '' ?>"
                        href="<?= $base_url ?>detail_kas.php">Detail Kas</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <span class="text-muted"><?php echo $_SESSION['nama']; ?></span>
                <a href="<?= $base_url ?>actions/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </div>
</nav>