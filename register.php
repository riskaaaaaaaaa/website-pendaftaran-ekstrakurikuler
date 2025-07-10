<?php
include "config.php";

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $conn->real_escape_string($_POST['password']);
  $confirm  = $conn->real_escape_string($_POST['confirm']);
  $role     = $conn->real_escape_string($_POST['role']);

  // Untuk siswa
  $nama     = isset($_POST['nama']) ? $conn->real_escape_string($_POST['nama']) : '';
  $kelas    = isset($_POST['kelas']) ? $conn->real_escape_string($_POST['kelas']) : '';

  if ($password != $confirm) {
    $error = "Konfirmasi password tidak cocok.";
  } else {
    $check = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
      $error = "Username sudah terdaftar.";
    } else {
      $hashed_password = password_hash($password, PASSWORD_BCRYPT);
      $sql_user = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";

      if ($conn->query($sql_user) === TRUE) {
        $user_id = $conn->insert_id;

        if ($role == 'user') {
          // Simpan ke tabel siswa juga
          $sql_siswa = "INSERT INTO siswa (id_user, nama, kelas) VALUES ($user_id, '$nama', '$kelas')";
          $conn->query($sql_siswa);
        }

        $success = "Akun berhasil dibuat. Silakan login.";
      } else {
        $error = "Gagal menyimpan akun: " . $conn->error;
      }
    }
  }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Buat Akun</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    ::-webkit-scrollbar {
      display: none;
    }

    body {
      background: linear-gradient(to right, rgb(204, 206, 240), rgb(168, 172, 236));
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .card {
      border-radius: 1rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .card-header {
      background-color: rgb(166, 169, 235);
      color: white;
      border-radius: 1rem 1rem 0 0;
    }

    .form-control {
      border-radius: 0.75rem;
    }

    .btn-primary {
      background-color: rgb(143, 148, 236);
      border: none;
    }

    .btn-primary:hover {
      background-color: #3d42b2;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header text-center">
            <h4>Buat Akun</h4>
          </div>
          <div class="card-body">
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

            <form method="POST" action="">
              <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>

              <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm" class="form-control" required>
              </div>

              <div class="mb-3">
                <label>Daftar Sebagai</label>
                <select name="role" id="roleSelect" class="form-select" required>
                  <option value="">-- Pilih Role --</option>
                  <option value="admin">Admin</option>
                  <option value="user">User</option>
                </select>
              </div>

              <!-- Hanya muncul jika role = user -->
              <div id="siswaFields" style="display: none;">
                <div class="mb-3">
                  <label>Nama Lengkap</label>
                  <input type="text" name="nama" class="form-control">
                </div>
                <div class="mb-3">
                  <label>Kelas</label>
                  <input type="text" name="kelas" class="form-control">
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Daftar</button>
              </div>
            </form>
          </div>
          <div class="card-footer text-center">
            <small>Sudah punya akun? <a href="index.php">Login di sini</a></small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JS tampilkan input Nama & Kelas jika role = user -->
  <script>
    const roleSelect = document.getElementById('roleSelect');
    const siswaFields = document.getElementById('siswaFields');

    roleSelect.addEventListener('change', function() {
      siswaFields.style.display = this.value === 'user' ? 'block' : 'none';
    });
  </script>
</body>

</html>