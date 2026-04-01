## Kode Backup

- DASHBOARD BENDAHARA:
    <!DOCTYPE html>
    <html lang="en">
      <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Aplikasi Kas Kelas</title>

          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
          <link rel="stylesheet" href="style/style.css">

    </head>
    <body>
        <div class="container-fluid">
            <div class="d-flex">
                <!-- SIDEBAR -->
                <div class="bg-white border-end vh-100" style="width: 260px;">
                    <div class="p-3 border-bottom mb-3">
                        <h4 class="mb-0 text-primary fw-bold">Kas Kelas</h4>
                        <small class="text-muted">XI PPLG 2</small>
                    </div>
                    <ul class="nav flex-column p-2">
                        <li class="nav-item">
                            <a class="nav-link active d-flex align-items-center gap-2 rounded bg-primary text-white"
                                href="#">
                                <i class="bi bi-grid-fill"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item mt-2">
                            <a class="nav-link d-flex align-items-center gap-2 text-dark" href="./kelolaKas/pemasukkan.php">
                                <i class="bi bi-wallet2"></i>
                                Kelola Kas
                            </a>
                        </li>
                        <li class="nav-item mt-2">
                            <a class="nav-link d-flex align-items-center gap-2 text-dark" href="statusKas.html">
                                <i class="bi bi-check-circle-fill"></i>
                                Status Kas
                            </a>
                        </li>
                        <li class="nav-item mt-2">
                            <a class="nav-link d-flex align-items-center gap-2 text-dark" href="">
                                <i class="bi bi-receipt"></i>
                                Detail Kas
                            </a>
                        </li>
                    </ul>
                    <div class="position-absolute bottom-0 w-100 p-3">
                        <a class="nav-link text-danger d-flex align-items-center gap-2" href="#">
                            <i class="bi bi-box-arrow-left"></i>
                            <h6 class="mb-0">Keluar</h6>
                        </a>
                    </div>
                </div>
                <div class="flex-grow-1 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Dashboard</h4>
                        <span class="text-muted">Periode: April 2026</span>
                    </div>
                    <!-- Ringkasan kas -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <small class="text-muted">Saldo Kas</small>
                                    <h4 class="fw-bold text-primary">Rp 1.250.000</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <small class="text-muted">Pemasukan</small>
                                    <h4 class="fw-bold text-success">Rp 500.000</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <small class="text-muted">Pengeluaran</small>
                                    <h4 class="fw-bold text-danger">Rp 250.000</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Riwayat transaksi -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white fw-semibold">
                            Riwayat Transaksi
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Jenis</th>
                                        <th>Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>03 Mei 2026</td>
                                        <td>Kas April M-4</td>
                                        <td><span class="badge bg-success">Masuk</span></td>
                                        <td>Rp 50.000</td>
                                    </tr>
                                    <tr>
                                        <td>02 Mei 2026</td>
                                        <td>Beli ATK</td>
                                        <td><span class="badge bg-danger">Keluar</span></td>
                                        <td>Rp 75.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
  </body>
  </html>
