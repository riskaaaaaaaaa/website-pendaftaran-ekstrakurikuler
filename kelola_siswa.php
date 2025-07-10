<?php
session_start();
include "config.php";

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Proses Tambah Siswa
if (isset($_POST['tambah'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    $nisn = $conn->real_escape_string($_POST['nisn']);
    $jk = $conn->real_escape_string($_POST['jenis_kelamin']);
    $alamat = $conn->real_escape_string($_POST['alamat']);

    $conn->query("INSERT INTO siswa (nama, kelas, nisn, jenis_kelamin, alamat) VALUES ('$nama', '$kelas', '$nisn', '$jk', '$alamat')");
    header("Location: kelola_siswa.php");
    exit;
}

// Proses Update
if (isset($_POST['update'])) {
    $id = intval($_POST['id_siswa']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    $nisn = $conn->real_escape_string($_POST['nisn']);
    $jk = $conn->real_escape_string($_POST['jenis_kelamin']);
    $alamat = $conn->real_escape_string($_POST['alamat']);

    $conn->query("UPDATE siswa SET nama='$nama', kelas='$kelas', nisn='$nisn', jenis_kelamin='$jk', alamat='$alamat' WHERE id_siswa=$id");
    header("Location: kelola_siswa.php");
    exit;
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM siswa WHERE id_siswa = $id");
    header("Location: kelola_siswa.php");
    exit;
}

// Pencarian
$cari = $_GET['cari'] ?? '';
$siswa = $conn->query("SELECT * FROM siswa WHERE nama LIKE '%$cari%' ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1 {
            background: linear-gradient(120deg, #4e73df, #224abe);
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

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-success {
            background: linear-gradient(120deg, #1cc88a, #13855c);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(120deg, #13855c, #1cc88a);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(28, 200, 138, 0.4);
        }

        .btn-danger {
            background: linear-gradient(120deg, #e74a3b, #be2617);
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(120deg, #be2617, #e74a3b);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 74, 59, 0.4);
        }

        .btn-warning {
            background: linear-gradient(120deg, #f6c23e, #dda20a);
            border: none;
            color: #212529;
        }

        .btn-warning:hover {
            background: linear-gradient(120deg, #dda20a, #f6c23e);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(246, 194, 62, 0.4);
        }

        .pengumuman-content {
            max-height: 150px;
            overflow: hidden;
            position: relative;
        }

        .pengumuman-content::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(to bottom, transparent, #f8f9fc);
        }
        ::-webkit-scrollbar {
            display: none;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
    </style>
</head>

<body>
    <h1 class="text-center " style="color:rgb(253, 253, 253);">Pendaftaran Ekstrakurikuler SMA</h1>

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
                    <a href="kelola_pengumuman.php" class="active"><i class="fas fa-bullhorn"></i> Kelola Pengumuman</a>
                    <a href="kelola_ekskul.php"><i class="fas fa-futbol"></i> Kelola Ekstrakurikuler</a>
                    <a href="daftar.php"><i class="fas fa-list"></i> Data Pendaftar</a>
                    <a href="kelola_siswa.php"><i class="fas fa-users"></i> Data Siswa</a>
                    <a href="laporann.php" class="active"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</a>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                <div class="container">
                    <h3 class="mb-4">Kelola Data Siswa</h3>

                    <form method="get" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="cari" class="form-control" placeholder="Cari nama siswa..." value="<?= htmlspecialchars($cari) ?>">
                            <button class="btn btn-outline-secondary" type="submit">Cari</button>
                        </div>
                    </form>

                    <!-- Tombol Tambah -->
                    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#tambahModal">➕ Tambah Siswa</button>

                    <div class="table-container">
                    <table class="table table-bordered bg-white shadow-sm">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>NISN</th>
                                <th>Jenis Kelamin</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($row = $siswa->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['kelas']) ?></td>
                                    <td><?= htmlspecialchars($row['nisn']) ?></td>
                                    <td><?= $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_siswa'] ?>">✏ Edit</button>
                                        <a href="?hapus=<?= $row['id_siswa'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus siswa ini?')">🗑 Hapus</a>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="editModal<?= $row['id_siswa'] ?>" tabindex="-1" aria-labelledby="editLabel<?= $row['id_siswa'] ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST">
                                            <input type="hidden" name="id_siswa" value="<?= $row['id_siswa'] ?>">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editLabel<?= $row['id_siswa'] ?>">Edit Data Siswa</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-2">
                                                        <label>Nama</label>
                                                        <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($row['nama']) ?>" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label>Kelas</label>
                                                        <input type="text" class="form-control" name="kelas" value="<?= htmlspecialchars($row['kelas']) ?>" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label>NISN</label>
                                                        <input type="text" class="form-control" name="nisn" value="<?= htmlspecialchars($row['nisn']) ?>">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label>Jenis Kelamin</label>
                                                        <select class="form-select" name="jenis_kelamin" required>
                                                            <option value="L" <?= $row['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                            <option value="P" <?= $row['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label>Alamat</label>
                                                        <textarea class="form-control" name="alamat" rows="2"><?= htmlspecialchars($row['alamat']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="update" class="btn btn-primary">Simpan</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Modal Tambah -->
                <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tambahLabel">Tambah Siswa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label>Nama</label>
                                        <input type="text" class="form-control" name="nama" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Kelas</label>
                                        <input type="text" class="form-control" name="kelas" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>NISN</label>
                                        <input type="text" class="form-control" name="nisn">
                                    </div>
                                    <div class="mb-2">
                                        <label>Jenis Kelamin</label>
                                        <select class="form-select" name="jenis_kelamin" required>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label>Alamat</label>
                                        <textarea class="form-control" name="alamat" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="tambah" class="btn btn-success">Tambah</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>