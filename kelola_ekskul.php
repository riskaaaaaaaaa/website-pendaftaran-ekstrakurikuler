<?php
session_start();
include "config.php";

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
// Tambah atau edit ekskul
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $conn->real_escape_string($_POST['nama']);
    $pembina = $conn->real_escape_string($_POST['pembina']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $kuota = intval($_POST['kuota']);

    if (isset($_POST['id'])) {
        // Edit ekskul
        $id = intval($_POST['id']);
        $conn->query("UPDATE ekskul SET nama_ekskul='$nama', pembina='$pembina', deskripsi='$deskripsi', kuota=$kuota WHERE id_ekskul=$id");
    } else {
        // Cek duplikat nama ekskul
        $cek_duplikat = $conn->query("SELECT * FROM ekskul WHERE nama_ekskul = '$nama'");
        if ($cek_duplikat->num_rows > 0) {
            echo "<script>alert('Nama ekstrakurikuler sudah ada. Gunakan nama lain.'); window.location='kelola_ekskul.php';</script>";
            exit;
        }

        // Tambah ekskul baru
        $conn->query("INSERT INTO ekskul (nama_ekskul, pembina, deskripsi, kuota) VALUES ('$nama', '$pembina', '$deskripsi', $kuota)");
    }

    header("Location: kelola_ekskul.php");
    exit;
}

// Ambil semua data ekskul + jumlah pendaftar
$ekskul = $conn->query("
    SELECT e.*, 
        (SELECT COUNT(*) FROM pendaftaran p 
         WHERE p.id_ekskul = e.id_ekskul AND p.status_pendaftaran IN ('pending', 'diterima')) AS jumlah_terdaftar
    FROM ekskul e
    ORDER BY e.nama_ekskul ASC
");

// Jika mode edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM ekskul WHERE id_ekskul = $id_edit");
    if ($res->num_rows > 0) {
        $edit_data = $res->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Ekstrakurikuler</title>
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
                <h2 class="mb-4">📚 Kelola Ekstrakurikuler</h2>

                <!-- Form Tambah/Edit -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <?= $edit_data ? '✏️ Edit Ekskul' : '+ Tambah Ekskul Baru' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id_ekskul']) ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label>Nama Ekskul</label>
                                <input type="text" name="nama" required class="form-control" value="<?= htmlspecialchars($edit_data['nama_ekskul'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Pembina</label>
                                <input type="text" name="pembina" class="form-control" value="<?= htmlspecialchars($edit_data['pembina'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Kuota</label>
                                <input type="number" name="kuota" class="form-control" min="1" value="<?= htmlspecialchars($edit_data['kuota'] ?? '') ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success">💾 Simpan</button>
                            <?php if ($edit_data): ?>
                                <a href="kelola_ekskul.php" class="btn btn-secondary">🔁 Batal Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Daftar Ekskul -->
                <table class="table table-bordered bg-white shadow-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Ekskul</th>
                            <th>Pembina</th>
                            <th>Deskripsi</th>
                            <th>Kuota</th>
                            <th>Sisa Kuota</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = $ekskul->fetch_assoc()): ?>
                            <?php $sisa_kuota = max(0, $row['kuota'] - $row['jumlah_terdaftar']); ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_ekskul']) ?></td>
                                <td><?= htmlspecialchars($row['pembina']) ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td><?= htmlspecialchars($row['kuota']) ?></td>
                                <td><?= $sisa_kuota ?></td>
                                <td>
                                    <a href="?edit=<?= $row['id_ekskul'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm btn-hapus" data-id="<?= $row['id_ekskul'] ?>">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <!-- Script Hapus AJAX -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const hapusButtons = document.querySelectorAll(".btn-hapus");
            hapusButtons.forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id = this.getAttribute("data-id");
                    if (confirm("Yakin ingin menghapus ekskul ini?")) {
                        fetch("hapus_ekskul.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: "id=" + id
                            })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.pesan);
                                if (data.status === "success") {
                                    this.closest("tr").remove();
                                }
                            });
                    }
                });
            });
        });
    </script>

</body>

</html>