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

- REVISI DASHBOARD:
  <div class="container mt-4">

      <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="fw-bold mb-0">Dashboard</h4>
          <span class="text-muted">Periode: April 2026</span>
      </div>

      <div class="row">

          <div class="col-md-4 mb-4">
              <div class="card shadow-sm border-0 h-100"> <div class="card-body">
                      <small class="text-muted">Pembayaran Kas</small>
                      <h3 class="fw-bold mt-2">
                          <?php echo $sudah_bayar; ?> / <?php echo $total_murid; ?>
                      </h3>

                      <?php
                      $persen = $total_murid > 0 ? ($sudah_bayar / $total_murid) * 100 : 0;
                      ?>

                      <div class="progress mt-2" style="height: 8px;">
                          <div class="progress-bar bg-success" style="width: <?php echo $persen; ?>%"></div>
                      </div>

                      <small class="text-muted">
                          <?php echo round($persen); ?>% sudah bayar
                      </small>
                  </div>
              </div>
          </div>

          <div class="col-md-8 mb-4">
              <div class="card shadow-sm border-0 h-100">
                  <div class="card-body">
                      <small class="text-muted">Perbandingan Kas</small>
                      <div style="height: 200px;">
                          <canvas id="chartKeuangan"></canvas>
                      </div>
                  </div>
              </div>
          </div>

      </div>

      <div class="row g-3 mb-4">
          </div>

      ```

### 3. Tambahkan Script Diagram

Di bagian paling bawah (sebelum penutup `</body>`), tambahkan koding JavaScript untuk menggambar diagramnya menggunakan data dari PHP:

```javascript
<script>
    // 1. Ambil elemen canvas tempat diagram akan digambar
    const ctx = document.getElementById('chartKeuangan').getContext('2d');

    // 2. Buat diagram baru
    new Chart(ctx, {
        type: 'bar', // Jenis diagram batang (bisa diganti 'pie' atau 'doughnut')
        data: {
            labels: ['Pemasukan', 'Pengeluaran'], // Label di bawah batang
            datasets: [{
                label: 'Jumlah Rupiah',
                // Kita ambil data langsung dari variabel PHP yang sudah kamu hitung di atas
                data: [<?php echo $pemasukan; ?>, <?php echo $pengeluaran; ?>],
                backgroundColor: [
                    'rgba(25, 135, 84, 0.2)', // Warna hijau untuk pemasukan
                    'rgba(220, 53, 69, 0.2)'  // Warna merah untuk pengeluaran
                ],
                borderColor: [
                    '#198754', // Border hijau
                    '#dc3545'  // Border merah
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Agar diagram mengikuti ukuran kotak pembungkusnya
            scales: {
                y: {
                    beginAtZero: true // Mulai angka dari 0
                }
            }
        }
    });
</script>
```
