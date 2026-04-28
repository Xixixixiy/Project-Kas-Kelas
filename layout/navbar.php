<?php
// 1. Ambil nama file saat ini untuk class 'active'
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Bersihkan nama role dari session (lowercase & ganti spasi jadi underscore jika perlu)
// Namun paling aman, kita sesuaikan dengan struktur folder kamu
$raw_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';

// 3. Tentukan folder berdasarkan role secara spesifik
$role_folder = '';
if ($raw_role == 'bendahara') {
    $role_folder = 'bendahara';
} elseif ($raw_role == 'wali kelas' || $raw_role == 'wali_kelas') {
    $role_folder = 'wali_kelas';
} elseif ($raw_role == 'ketua kelas' || $raw_role == 'ketua_kelas') {
    $role_folder = 'ketua_kelas';
} elseif ($raw_role == 'murid') {
    $role_folder = 'murid';
}

// 4. Pastikan base_url diakhiri dengan satu slash saja
$base = rtrim($base_url, '/') . '/';
?>

<nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="<?= $base . $role_folder ?>/dashboard.php">Kas Kelas</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>"
                        href="<?= $base . $role_folder ?>/dashboard.php">Dashboard</a>
                </li>

                <?php if ($raw_role == 'bendahara') : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'transaksi_masuk.php') ? 'active' : '' ?>"
                            href="<?= $base ?>bendahara/transaksi_masuk.php">Kelola Kas</a>
                    </li>
                <?php endif; ?>

                <?php if ($raw_role == 'wali kelas' || $raw_role == 'wali_kelas') : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'kelola_siswa.php') ? 'active' : '' ?>"
                            href="<?= $base ?>wali_kelas/kelola_siswa.php">Kelola Siswa</a>
                    </li>
                <?php endif; ?>

                <?php if ($raw_role !== 'murid') : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'status_kas.php') ? 'active' : '' ?>"
                            href="<?= $base ?>status_kas.php">Status Kas</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'detail_kas.php') ? 'active' : '' ?>"
                        href="<?= $base ?>detail_kas.php">Detail Kas</a>
                </li>

                <?php if ($raw_role == 'ketua kelas' || $raw_role == 'ketua_kelas') : ?>
                    <li class="nav-item">
                        <span class="nav-link disabled text-primary fw-bold">| Mode Pantau</span>
                    </li>
                <?php endif; ?>

            </ul>

            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small"><?php echo $_SESSION['nama']; ?> (<?= ucwords($raw_role) ?>)</span>
                <a href="<?= $base ?>actions/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </div>
</nav>