<nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">Kas Kelas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard_bendahara.php') ? 'active' : '' ?>" href="dashboard_bendahara.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'transaksi_masuk.php') ? 'active' : '' ?>" href="transaksi_masuk.php">Kelola Kas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'status_kas.php') ? 'active' : '' ?>" href="status_kas.php">Status Kas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'detail_kas.php') ? 'active' : '' ?>" href="detail_kas.php">Detail Kas</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted"><?php echo $_SESSION['nama']; ?></span>
                <a href="actions/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </div>
</nav>