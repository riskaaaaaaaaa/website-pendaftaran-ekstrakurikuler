<?php
session_start();
include "config.php";

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Proses pengaturan akun
$account_success = $account_error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_account'])) {
    $current_password = $conn->real_escape_string($_POST['current_password']);
    $new_username = $conn->real_escape_string($_POST['new_username']);
    $new_password = $conn->real_escape_string($_POST['new_password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    // Ambil data user saat ini
    $id_user = $_SESSION['id_user'];
    $query = "SELECT password FROM users WHERE id = $id_user";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password saat ini
        if (password_verify($current_password, $user['password'])) {
            // Validasi username baru
            if (!empty($new_username)) {
                // Cek apakah username baru sudah ada
                $check_username = $conn->query("SELECT id FROM users WHERE username = '$new_username' AND id != $id_user");
                if ($check_username->num_rows > 0) {
                    $account_error = "Username sudah digunakan!";
                }
            }

            // Validasi password baru
            if (!empty($new_password) && $new_password !== $confirm_password) {
                $account_error = "Password baru dan konfirmasi password tidak cocok!";
            }

            // Jika tidak ada error, lakukan update
            if (empty($account_error)) {
                $update_fields = [];

                if (!empty($new_username)) {
                    $update_fields[] = "username = '$new_username'";
                    $_SESSION['username'] = $new_username;
                }

                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_fields[] = "password = '$hashed_password'";
                }

                if (!empty($update_fields)) {
                    $update_sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = $id_user";
                    if ($conn->query($update_sql)) {
                        $account_success = "Pengaturan akun berhasil diperbarui!";
                    } else {
                        $account_error = "Terjadi kesalahan saat memperbarui data: " . $conn->error;
                    }
                } else {
                    $account_error = "Tidak ada perubahan yang dilakukan.";
                }
            }
        } else {
            $account_error = "Password saat ini salah!";
        }
    } else {
        $account_error = "User tidak ditemukan!";
    }
}

// Ambil data jumlah siswa
$siswa_result = $conn->query("SELECT COUNT(*) AS total_siswa FROM siswa");
$total_siswa = $siswa_result->fetch_assoc()['total_siswa'] ?? 0;

// Ambil data jumlah ekskul
$ekskul_result = $conn->query("SELECT COUNT(*) AS total_ekskul FROM ekskul");
$total_ekskul = $ekskul_result->fetch_assoc()['total_ekskul'] ?? 0;

// Ambil data jumlah pendaftar
$pendaftar_result = $conn->query("SELECT COUNT(*) AS total_pendaftar FROM pendaftaran");
$total_pendaftar = $pendaftar_result->fetch_assoc()['total_pendaftar'] ?? 0;

// Ambil data untuk chart dan tabel
$data_result = $conn->query("
  SELECT p.id_pendaftaran, s.nama AS nama_siswa, e.nama_ekskul, p.status_pendaftaran
  FROM pendaftaran p
  JOIN siswa s ON p.id_siswa = s.id_siswa
  JOIN ekskul e ON p.id_ekskul = e.id_ekskul
  ORDER BY p.id_pendaftaran DESC
");

// Ambil data untuk grafik chart
$chart_result = $conn->query("
  SELECT e.nama_ekskul, COUNT(*) AS total 
  FROM pendaftaran p
  JOIN ekskul e ON p.id_ekskul = e.id_ekskul
  GROUP BY e.nama_ekskul
");
$chart_labels = [];
$chart_data = [];
while ($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = $row['nama_ekskul'];
    $chart_data[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, #2c3e50, #1a2530);
            color: white;
            position: fixed;
            left: 0;
            padding-top: 1rem;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
            z-index: 100;
        }

        .sidebar a {
            color: #e9ecef;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #4e73df;
            color: #fff;
        }

        .sidebar a i {
            width: 24px;
            margin-right: 10px;
            text-align: center;
        }

        .content {
            margin-left: 16.6667%;
            padding: 1rem;
            height: 100vh;
            overflow-y: auto;
            background-color: #f8f9fc;
        }

        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 1.5rem;
            border: none;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .account-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(120deg, #4e73df, #224abe);
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .btn-primary {
            background: linear-gradient(120deg, #4e73df, #224abe);
            border: none;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(120deg, #224abe, #4e73df);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(78, 115, 223, 0.4);
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }

        .table thead th {
            background-color: #4e73df;
            color: white;
            font-weight: 600;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-details h5 {
            margin-bottom: 0;
            font-size: 1.05rem;
        }

        .user-details p {
            margin-bottom: 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.8);
            border-color: rgba(220, 53, 69, 0.8);
        }

        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e3e6f0;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: #4e73df;
        }

        .card-header {
            background: linear-gradient(120deg, #4e73df, #224abe);
            color: white;
            font-weight: 600;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
    </style>
</head>

<body>
    <h1 class="text-center bg-primary" style="color:rgb(253, 253, 253);">Pendaftaran Ekstrakurikuler</h1>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="user-info">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=4e73df&color=ffffff" alt="Admin">
                    <div class="user-details">
                        <h5><?= htmlspecialchars($_SESSION['username']) ?></h5>
                        <p>Administrator</p>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="kelola_pengumuman.php"><i class="fas fa-bullhorn"></i> Kelola Pengumuman</a>
                    <a href="kelola_ekskul.php"><i class="fas fa-futbol"></i> Kelola Ekstrakurikuler</a>
                    <a href="daftar.php"><i class="fas fa-list"></i> Data Pendaftar</a>
                    <a href="kelola_siswa.php"><i class="fas fa-users"></i> Data Siswa</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#accountModal"><i class="fas fa-user-cog"></i> Pengaturan Akun</a>
                </div>

                <div class="mt-auto p-3 text-center" style="position: absolute; bottom: 0; width: 100%;">
                    <a href="logout.php" class="btn logout-btn btn-sm" onclick="return confirm('Yakin ingin logout?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
                    <div class="container">
                        <a class="navbar-brand" href="#"><i class="fas fa-graduation-cap me-2"></i>Ekstrakurikuler SMA</a>
                        <div class="ms-auto">
                            <span class="text-light me-3"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            <a href="logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin logout?')">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Jumlah Siswa</h5>
                                        <p class="stat-number"><?= $total_siswa ?></p>
                                    </div>
                                    <i class="fas fa-users fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Jumlah Ekstrakurikuler</h5>
                                        <p class="stat-number"><?= $total_ekskul ?></p>
                                    </div>
                                    <i class="fas fa-futbol fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Jumlah Pendaftar</h5>
                                        <p class="stat-number"><?= $total_pendaftar ?></p>
                                    </div>
                                    <i class="fas fa-clipboard-list fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card mt-4">
                            <div class="card-header">Data Pendaftar Terbaru</div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Ekstrakurikuler</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        while ($row = $data_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_ekskul']) ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?= $row['status_pendaftaran'] === 'diterima' ? 'bg-success' : ($row['status_pendaftaran'] === 'ditolak' ? 'bg-danger' : 'bg-warning') ?>">
                                                        <?= ucfirst($row['status_pendaftaran']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mt-4">
                            <div class="card-header">Grafik Pendaftar per Ekskul</div>
                            <div class="card-body">
                                <canvas id="chartHasiltopsis"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Pengaturan Akun -->
    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountModalLabel"><i class="fas fa-user-cog me-2"></i>Pengaturan Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($account_success): ?>
                        <div class="alert alert-success"><?= $account_success ?></div>
                    <?php endif; ?>
                    <?php if ($account_error): ?>
                        <div class="alert alert-danger"><?= $account_error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="dashboard.php">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <div class="form-text">Wajib diisi untuk verifikasi</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_username" class="form-label">Username Baru</label>
                            <input type="text" class="form-control" id="new_username" name="new_username"
                                value="<?= htmlspecialchars($_SESSION['username']) ?>" placeholder="Kosongkan jika tidak ingin mengubah">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password"
                                placeholder="Kosongkan jika tidak ingin mengubah">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <button type="submit" name="update_account" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart untuk pendaftar per ekskul
        const ctx = document.getElementById('chartHasiltopsis');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Jumlah Pendaftar',
                    data: <?= json_encode($chart_data) ?>,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#5a5c69', '#858796', '#3a3b45', '#f8f9fc', '#e74a3b'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Jumlah Pendaftar per Ekstrakurikuler',
                        font: {
                            size: 16
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Validasi form di modal
        document.querySelector('form[name="update_account"]').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
            }
        });
    </script>
</body>

</html>