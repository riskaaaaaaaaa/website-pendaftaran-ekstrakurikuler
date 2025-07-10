<?php
session_start();
include "config.php";

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
// Proses ekspor data ke CSV
if (isset($_GET['ekspor']) && $_GET['ekspor'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan_pendaftaran.csv');

    $output = fopen('php://output', 'w');

    // Header CSV
    fputcsv($output, array('No', 'Nama Siswa', 'Kelas', 'Ekskul', 'Tanggal Daftar'));

    // Query data untuk CSV
    $query_csv = "
        SELECT s.nama AS nama_siswa, s.kelas, e.nama_ekskul, p.tanggal_daftar
        FROM pendaftaran p
        JOIN siswa s ON p.id_siswa = s.id_siswa
        JOIN ekskul e ON p.id_ekskul = e.id_ekskul
        WHERE p.status_pendaftaran = 'diterima'
    ";

    if ($filter_ekskul != '') {
        $query_csv .= " AND p.id_ekskul = " . intval($filter_ekskul);
    }

    $query_csv .= " ORDER BY p.tanggal_daftar DESC";
    $result_csv = $conn->query($query_csv);

    // Data CSV
    $no = 1;
    while ($row_csv = $result_csv->fetch_assoc()) {
        fputcsv($output, array(
            $no++,
            $row_csv['nama_siswa'],
            $row_csv['kelas'],
            $row_csv['nama_ekskul'],
            $row_csv['tanggal_daftar']
        ));
    }

    fclose($output);
    exit;
}

// Ambil filter ekskul jika ada
$filter_ekskul = $_GET['ekskul'] ?? '';

// Query utama pendaftar yang diterima
$query = "
    SELECT p.id_pendaftaran, s.nama AS nama_siswa, s.kelas, e.nama_ekskul, 
           p.tanggal_daftar
    FROM pendaftaran p
    JOIN siswa s ON p.id_siswa = s.id_siswa
    JOIN ekskul e ON p.id_ekskul = e.id_ekskul
    WHERE p.status_pendaftaran = 'diterima'
";

if ($filter_ekskul != '') {
    $query .= " AND p.id_ekskul = " . intval($filter_ekskul);
}

$query .= " ORDER BY p.tanggal_daftar DESC";
$result = $conn->query($query);

// Ambil daftar ekskul untuk dropdown
$ekskul_list = $conn->query("SELECT * FROM ekskul");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pendaftaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            body {
                height: 100%;
                margin: 0;
                overflow: hidden;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
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

        ::-webkit-scrollbar {
            display: none;
        }

        .report-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .btn-print {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-print:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-download {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-download:hover {
            background-color: #138496;
            border-color: #117a8b;
        }

        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        @media print {

            .sidebar,
            .no-print {
                display: none !important;
            }

            .content {
                margin-left: 0;
                width: 100%;
            }

            body {
                background-color: white;
            }

            .report-header {
                border: 2px solid #333;
                background: white;
                color: black;
            }

            .table-container {
                box-shadow: none;
                border: 1px solid #dee2e6;
            }
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
                <div class="report-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Pendaftaran Ekstrakurikuler</h2>
                            <p class="lead mb-0">Data siswa yang telah diterima dalam kegiatan ekstrakurikuler</p>
                        </div>
                        <div class="d-flex gap-2 no-print">
                            <button onclick="window.print()" class="btn btn-print text-white">
                                <i class="bi bi-printer"></i> Cetak Laporan
                            </button>
                            
                        </div>
                    </div>
                </div>

                <!-- Form Filter -->
                <form method="get" class="row mb-4 no-print">
                    <div class="col-md-8">
                        <select name="ekskul" class="form-select">
                            <option value="">-- Semua Ekstrakurikuler --</option>
                            <?php while ($e = $ekskul_list->fetch_assoc()): ?>
                                <option value="<?= $e['id_ekskul'] ?>" <?= ($filter_ekskul == $e['id_ekskul']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['nama_ekskul']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="laporan.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                    </div>
                </form>

                <!-- Informasi Filter -->
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle"></i> Menampilkan data pendaftar yang <strong>diterima</strong>
                    <?php if ($filter_ekskul):
                        $ekskul_name = $conn->query("SELECT nama_ekskul FROM ekskul WHERE id_ekskul = $filter_ekskul")->fetch_assoc()['nama_ekskul'];
                    ?>
                        untuk ekstrakurikuler: <strong><?= htmlspecialchars($ekskul_name) ?></strong>
                    <?php endif; ?>
                </div>

                <!-- Tabel Data -->
                <div class="table-container">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Ekstrakurikuler</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $total = 0;
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()):
                                    $total++;
                            ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_ekskul']) ?></td>
                                        <td><?= date('d M Y', strtotime($row['tanggal_daftar'])) ?></td>
                                    </tr>
                            <?php
                                endwhile;
                            } else {
                                echo '<tr><td colspan="5" class="text-center">Tidak ada data pendaftar yang diterima</td></tr>';
                            }
                            ?>
                        </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total Siswa Diterima:</td>
                                <td class="fw-bold"><?= $total ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Info Cetak -->
                <div class="alert alert-secondary mt-4 no-print">
                    <i class="bi bi-info-circle"></i> Gunakan tombol <strong>Cetak Laporan</strong> untuk mencetak data ini.
                </div>
            </main>
        </div>
    </div>
</body>

</html>