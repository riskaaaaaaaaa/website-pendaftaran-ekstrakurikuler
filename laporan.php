<?php
include "config.php"; // Koneksi ke database

// Proses update status pendaftaran
if (isset($_GET['aksi'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $status = ($_GET['aksi'] === 'terima') ? 'diterima' : 'ditolak';
    $conn->query("UPDATE pendaftaran SET status_pendaftaran = '$status' WHERE id_pendaftaran = $id");
    header("Location: daftar.php");
    exit;
}

// Ambil filter jika ada
$filter_ekskul = $_GET['ekskul'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_tanggal = $_GET['tanggal'] ?? '';

// Query utama pendaftar
$query = "
    SELECT p.id_pendaftaran, s.nama AS nama_siswa, s.kelas, e.nama_ekskul, 
           p.tanggal_daftar, p.status_pendaftaran
    FROM pendaftaran p
    JOIN siswa s ON p.id_siswa = s.id_siswa
    JOIN ekskul e ON p.id_ekskul = e.id_ekskul
    WHERE 1=1
";

if ($filter_ekskul != '') {
    $query .= " AND p.id_ekskul = " . intval($filter_ekskul);
}

if ($filter_status != '') {
    $status = $conn->real_escape_string($filter_status);
    $query .= " AND p.status_pendaftaran = '$status'";
}

if ($filter_tanggal != '') {
    $tanggal = $conn->real_escape_string($filter_tanggal);
    $query .= " AND DATE(p.tanggal_daftar) = '$tanggal'";
}

$query .= " ORDER BY p.tanggal_daftar DESC";
$result = $conn->query($query);

// Ambil daftar ekskul untuk dropdown
$ekskul_list = $conn->query("SELECT * FROM ekskul");

// Hitung statistik pendaftaran
$stats_query = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status_pendaftaran = 'diterima' THEN 1 ELSE 0 END) AS diterima,
        SUM(CASE WHEN status_pendaftaran = 'ditolak' THEN 1 ELSE 0 END) AS ditolak,
        SUM(CASE WHEN status_pendaftaran = 'pending' THEN 1 ELSE 0 END) AS pending
    FROM pendaftaran
";
$stats = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendaftaran Ekstrakurikuler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 1rem;
            overflow-y: auto;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .sidebar a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            border-radius: 5px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
        }
        
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            margin-left: 250px;
            padding: 2rem;
            overflow-y: auto;
            height: 100%;
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            color: white;
            border-radius: 12px;
        }
        
        .stat-card.total { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
        .stat-card.diterima { background: linear-gradient(135deg, #4cc9f0, #4895ef); }
        .stat-card.ditolak { background: linear-gradient(135deg, #f72585, #b5179e); }
        .stat-card.pending { background: linear-gradient(135deg, #7209b7, #560bad); }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stat-card .label {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .report-title {
            color: var(--primary);
            border-bottom: 2px solid var(--success);
            padding-bottom: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .report-title i {
            background: var(--success);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .btn-report {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-report:hover {
            background: var(--secondary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }
        
        .badge-pending { background: #7209b7; }
        .badge-diterima { background: #4cc9f0; }
        .badge-ditolak { background: #f72585; }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                text-align: center;
            }
            
            .sidebar a span {
                display: none;
            }
            
            .sidebar a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .stat-card .number {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 sidebar">
            <h4 class="text-center mt-3 mb-4"><i class="fas fa-school"></i> <span>EkskulApp</span></h4>
            <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="kelola_pengumuman.php"><i class="fas fa-bullhorn"></i> <span>Pengumuman</span></a>
            <a href="kelola_ekskul.php"><i class="fas fa-futbol"></i> <span>Ekstrakurikuler</span></a>
            <a href="daftar.php"><i class="fas fa-list"></i> <span>Data Pendaftar</span></a>
            <a href="kelola_siswa.php"><i class="fas fa-users"></i> <span>Data Siswa</span></a>
            <a href="#" class="active"><i class="fas fa-chart-bar"></i> <span>Laporan</span></a>
        </nav>

        <main class="col-md-10 content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-chart-line me-2"></i> Laporan Pendaftaran</h2>
                <div class="d-flex">
                    <button class="btn btn-report me-2"><i class="fas fa-file-pdf me-2"></i> Ekspor PDF</button>
                    <button class="btn btn-success"><i class="fas fa-file-excel me-2"></i> Ekspor Excel</button>
                </div>
            </div>
            
            <!-- Statistik -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card total">
                        <i class="fas fa-file-alt"></i>
                        <div class="number"><?= $stats['total'] ?></div>
                        <div class="label">Total Pendaftar</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card diterima">
                        <i class="fas fa-check-circle"></i>
                        <div class="number"><?= $stats['diterima'] ?></div>
                        <div class="label">Diterima</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card ditolak">
                        <i class="fas fa-times-circle"></i>
                        <div class="number"><?= $stats['ditolak'] ?></div>
                        <div class="label">Ditolak</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card pending">
                        <i class="fas fa-clock"></i>
                        <div class="number"><?= $stats['pending'] ?></div>
                        <div class="label">Pending</div>
                    </div>
                </div>
            </div>
            
            <!-- Grafik dan Filter -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="chart-container">
                        <h5 class="mb-4">Statistik Pendaftaran per Ekskul</h5>
                        <canvas id="ekskulChart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container">
                        <h5 class="mb-4">Distribusi Status Pendaftaran</h5>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <h4 class="report-title">
                    <span><i class="fas fa-filter me-2"></i> Filter Laporan</span>
                </h4>
                <form method="get" class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ekstrakurikuler</label>
                        <select name="ekskul" class="form-select">
                            <option value="">Semua Ekskul</option>
                            <?php while ($e = $ekskul_list->fetch_assoc()): ?>
                                <option value="<?= $e['id_ekskul'] ?>" <?= ($filter_ekskul == $e['id_ekskul']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['nama_ekskul']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status Pendaftaran</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= ($filter_status == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="diterima" <?= ($filter_status == 'diterima') ? 'selected' : '' ?>>Diterima</option>
                            <option value="ditolak" <?= ($filter_status == 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Pendaftaran</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($filter_tanggal) ?>">
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-filter me-2"></i> Terapkan Filter</button>
                        <a href="laporan.php" class="btn btn-secondary px-4"><i class="fas fa-sync me-2"></i> Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Tabel Data -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-table me-2"></i> Data Pendaftaran</span>
                    <span>Total Data: <?= $result->num_rows ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Ekskul</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_ekskul']) ?></td>
                                        <td><?= htmlspecialchars($row['tanggal_daftar']) ?></td>
                                        <td>
                                            <?php
                                            $status = $row['status_pendaftaran'];
                                            $badge_class = ($status == 'diterima') ? 'badge-diterima' : 
                                                            (($status == 'ditolak') ? 'badge-ditolak' : 'badge-pending');
                                            ?>
                                            <span class="badge rounded-pill <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-primary action-btn" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-outline-success action-btn" title="Ekspor">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($result->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-3"></i>
                                            <p>Tidak ada data pendaftaran</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Inisialisasi grafik
    document.addEventListener('DOMContentLoaded', function() {
        // Grafik ekskul
        const ekskulCtx = document.getElementById('ekskulChart').getContext('2d');
        const ekskulChart = new Chart(ekskulCtx, {
            type: 'bar',
            data: {
                labels: ['Basket', 'Futsal', 'Paskibra', 'Pramuka', 'PMR', 'Robotik'],
                datasets: [{
                    label: 'Jumlah Pendaftar',
                    data: [32, 45, 22, 30, 28, 18],
                    backgroundColor: [
                        '#4361ee', '#4cc9f0', '#f72585', '#7209b7', '#3a0ca3', '#4895ef'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Grafik status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Diterima', 'Ditolak', 'Pending'],
                datasets: [{
                    data: [<?= $stats['diterima'] ?>, <?= $stats['ditolak'] ?>, <?= $stats['pending'] ?>],
                    backgroundColor: [
                        '#4cc9f0', '#f72585', '#7209b7'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '60%'
            }
        });
    });
</script>
</body>
</html>