<?php
session_start();
include "config.php";

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit();
}



// Inisialisasi variabel
$judul = $isi = "";
$isEdit = false;
$id_edit = 0;

// Cek jika sedang dalam mode edit
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
  $isEdit = true;
  $id_edit = (int)$_GET['edit'];
  $query = $conn->query("SELECT * FROM pengumuman WHERE id = $id_edit");
  if ($query && $query->num_rows > 0) {
    $row = $query->fetch_assoc();
    $judul = $row['judul'];
    $isi = $row['isi'];
  } else {
    header("Location: kelola_pengumuman.php");
    exit;
  }
}

date_default_timezone_set('Asia/Jakarta');

// Proses simpan/update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $judul = $conn->real_escape_string($_POST['judul']);
  $isi   = $conn->real_escape_string($_POST['isi']);
  $tanggal = date("Y-m-d H:i:s");

  if (!empty($_POST['id_edit'])) {
    $id = (int)$_POST['id_edit'];
    $conn->query("UPDATE pengumuman SET judul='$judul', isi='$isi' WHERE id=$id");
    header("Location: kelola_pengumuman.php?update=1");
    exit;
  } else {
    $conn->query("INSERT INTO pengumuman (judul, isi, tanggal) VALUES ('$judul', '$isi', '$tanggal')");
    header("Location: kelola_pengumuman.php?saved=1");
    exit;
  }
}

// Hapus data dengan prepared statement
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
  $id_hapus = (int)$_GET['hapus'];

  // Cek apakah ID ada dulu
  $cek = $conn->prepare("SELECT id FROM pengumuman WHERE id = ?");
  $cek->bind_param("i", $id_hapus);
  $cek->execute();
  $result = $cek->get_result();

  if ($result->num_rows > 0) {
    $hapus = $conn->prepare("DELETE FROM pengumuman WHERE id = ?");
    $hapus->bind_param("i", $id_hapus);
    $hapus->execute();
    header("Location: kelola_pengumuman.php?hapus=1");
    exit;
  } else {
    header("Location: kelola_pengumuman.php?error=notfound");
    exit;
  }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pengumuman</title>
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

        <h2 class="mb-4"><i class="fas fa-bullhorn me-2"></i>Kelola Pengumuman</h2>

        <!-- Notifikasi -->
        <?php if (isset($_GET['saved'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> Pengumuman berhasil disimpan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php elseif (isset($_GET['update'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> Pengumuman berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php elseif (isset($_GET['hapus'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-trash-alt me-2"></i> Pengumuman berhasil dihapus.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'notfound'): ?>
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> Data pengumuman berhasil dihapus.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- Form Tambah/Edit Pengumuman -->
        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-edit me-2"></i><?= $isEdit ? 'Edit Pengumuman' : 'Tambah Pengumuman Baru' ?>
          </div>
          <div class="card-body">
            <form method="POST">
              <input type="hidden" name="id_edit" value="<?= $isEdit ? $id_edit : '' ?>">
              <div class="mb-3">
                <label for="judul" class="form-label">Judul Pengumuman</label>
                <input type="text" class="form-control" id="judul" name="judul"
                  value="<?= htmlspecialchars($judul) ?>" required
                  placeholder="Masukkan judul pengumuman">
              </div>
              <div class="mb-3">
                <label for="isi" class="form-label">Isi Pengumuman</label>
                <textarea class="form-control" id="isi" name="isi" rows="5" required
                  placeholder="Masukkan isi pengumuman"><?= htmlspecialchars($isi) ?></textarea>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-save me-1"></i><?= $isEdit ? 'Update' : 'Simpan' ?>
                </button>
                <?php if ($isEdit): ?>
                  <a href="kelola_pengumuman.php" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Batal
                  </a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Daftar Pengumuman -->
        <div class="card">
          <div class="card-header">
            <i class="fas fa-list me-2"></i>Daftar Pengumuman
          </div>
          <div class="card-body">
            <?php
            $result = $conn->query("SELECT * FROM pengumuman ORDER BY tanggal DESC");
            if ($result->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Judul</th>
                      <th>Isi</th>
                      <th>Tanggal</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $no = 1;
                    while ($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td class="align-middle"><?= $no++ ?></td>
                        <td class="align-middle"><?= htmlspecialchars($row['judul']) ?></td>
                        <td>
                          <div class="pengumuman-content">
                            <?= nl2br(htmlspecialchars($row['isi'])) ?>
                          </div>
                        </td>
                        <td class="align-middle"><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                        <td class="align-middle">
                          <div class="d-flex gap-2">
                            <a href="kelola_pengumuman.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                              <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="kelola_pengumuman.php?hapus=<?= $row['id'] ?>"
                              class="btn btn-danger btn-sm"
                              onclick="return confirm('Yakin ingin menghapus pengumuman berjudul: <?= addslashes($row['judul']) ?>?')">
                              <i class="fas fa-trash-alt"></i> Hapus
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="alert alert-info text-center py-4">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <h4>Belum ada pengumuman</h4>
                <p class="mb-0">Silakan tambahkan pengumuman baru menggunakan form di atas</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    </div>
  </div>



  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Validasi form di modal
    document.querySelector('form[name="update_account"]')?.addEventListener('submit', function(e) {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Password baru dan konfirmasi password tidak cocok!');
      }
    });

    // Hilangkan parameter URL setelah alert ditampilkan
    if (window.location.search.includes('saved') ||
      window.location.search.includes('update') ||
      window.location.search.includes('hapus') ||
      window.location.search.includes('error')) {
      setTimeout(() => {
        window.history.replaceState({}, document.title, window.location.pathname);
      }, 3000);
    }
  </script>
</body>

</html>