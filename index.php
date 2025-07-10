<?php
session_start();
include "config.php";

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $conn->real_escape_string($_POST['username']);
  $passwordInput = $conn->real_escape_string($_POST['password']);

  // Pastikan ambil id_user juga
  $query = "SELECT id, username, password, role FROM users WHERE username = '$username'";
  $result = $conn->query($query);

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($passwordInput, $user['password'])) {
      $_SESSION['username'] = $user['username'];
      $_SESSION['role']     = $user['role'];
      $_SESSION['id_user']  = $user['id'];

      if ($user['role'] == 'admin') {
        header("Location: dashboard.php");
        exit();
      } else {
        $id_user = $user['id'];
        $qSiswa = $conn->query("SELECT id_siswa FROM siswa WHERE id_user = $id_user LIMIT 1");
        if ($qSiswa && $data = $qSiswa->fetch_assoc()) {
          $_SESSION['id_siswa'] = $data['id_siswa'];
          header("Location: siswa/siswa_home.php");
          exit();
        } else {
          $error = "Akun siswa tidak ditemukan di database siswa.";
        }
      }
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "Username tidak ditemukan!";
  }
}
?>




<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    ::-webkit-scrollbar {
      display: none;
    }

    body {
      background: linear-gradient(to right,rgb(204, 206, 240),rgb(168, 172, 236));
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .card {
      border: none;
      border-radius: 1rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .card-header {
      background-color: rgb(166, 169, 235);
      color: white;
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
    }

    .form-control {
      border-radius: 0.75rem;
    }

    .btn-primary {
      background-color: rgb(143, 148, 236);
      border: none;
      border-radius: 0.75rem;
    }

    .btn-primary:hover {
      background-color: #3d42b2;
    }

    .form-check-label {
      font-size: 0.9rem;
    }

    .card-footer a {
      color:rgb(115, 120, 221);
      text-decoration: none;
    }

    .card-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5"> <!-- Perubahan di sini -->
        <div class="card">
          <div class="card-header text-center py-3">
            <h4>Halaman Login</h4>
          </div>
          <div class="card-body px-4">
            <?php if ($error): ?>
              <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
              <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="showPasswordCheck">
                <label class="form-check-label" for="showPasswordCheck">
                  Tampilkan Password
                </label>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login Sekarang</button>
              </div>
            </form>
          </div>
          <div class="card-footer text-center py-3">
            <small>Belum punya akun? <a href="register.php">Daftar di sini</a></small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const showPasswordCheck = document.getElementById('showPasswordCheck');
    const passwordField = document.getElementById('password');

    showPasswordCheck.addEventListener('change', function () {
      passwordField.type = this.checked ? 'text' : 'password';
    });
  </script>
</body>


</html>