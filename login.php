<?php
session_start();

$usersFile = __DIR__ . '/users.json';

function loadUsers() {
  global $usersFile;
  if (!file_exists($usersFile)) return [];
  return json_decode(file_get_contents($usersFile), true);
}

function saveUsers($users) {
  global $usersFile;
  file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

$users = loadUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim($_POST['email']));
  $password = $_POST['password'];

  // Admin Login
  if ($email === 'admin@gmail.com' && $password === '12345') {
    $_SESSION['userEmail'] = $email;
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <style>body{background:black;transition:opacity 0.5s;}body.fade-out{opacity:0;}</style>
    <script>
      localStorage.setItem('userEmail', '$email');
      localStorage.setItem('isLoggedIn', 'true');
      document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('fade-out');
        setTimeout(() => { window.location.href = 'transaksi.php'; }, 500);
      });
    </script></head><body></body></html>";
    exit;
  }

  // Register
  if (isset($_POST['register'])) {
    foreach ($users as $u) {
      if ($u['email'] === $email) {
        echo "<script>alert('Email sudah terdaftar!'); window.location.href='login.php';</script>";
        exit;
      }
    }
    $users[] = ['email' => $email, 'password' => $password];
    saveUsers($users);
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <style>body{background:black;transition:opacity 0.5s;}body.fade-out{opacity:0;}</style>
    <script>
      alert('Registrasi berhasil! Silakan login.');
      document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('fade-out');
        setTimeout(() => { window.location.href = 'login.php'; }, 500);
      });
    </script></head><body></body></html>";
    exit;
  }

  // Login
  if (isset($_POST['login'])) {
    foreach ($users as $u) {
      if ($u['email'] === $email && $u['password'] === $password) {
        $_SESSION['userEmail'] = $email;
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
        <style>body{background:black;transition:opacity 0.5s;}body.fade-out{opacity:0;}</style>
        <script>
          localStorage.setItem('userEmail', '$email');
          localStorage.setItem('isLoggedIn', 'true');
          document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('fade-out');
            setTimeout(() => { window.location.href = 'index.php'; }, 500);
          });
        </script></head><body></body></html>";
        exit;
      }
    }

    echo "<script>alert('Email atau password salah!'); window.location.href='login.php';</script>";
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login / Register</title>
  <style>
    .full-bg-wrapper {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 1000px;
      overflow: hidden;
      z-index: -1;
    }

    .bg-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      filter: brightness(0.6);
    }

    .bg-overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,1));
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #000;
      color: white;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      opacity: 1;
      transition: opacity 0.5s ease-in-out;
    }

    body.fade-out {
      opacity: 0;
    }

    .form-container {
      background-color: #111;
      padding: 30px;
      border-radius: 10px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 15px red;
    }

    .form-slide {
      display: none;
    }

    .form-slide.active {
      display: block;
    }

    h2 {
      text-align: center;
      color: white;
    }

    input {
      width: 94.5%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      border: 1px solid #444;
      background: #222;
      color: white;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: rgb(255, 0, 17);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
    }

    button:hover {
      background-color: rgb(37, 211, 102);
    }

    .top-right {
      position: absolute;
      top: 10px;
      right: 20px;
    }

    .auth-btn {
      padding: 8px 16px;
      background: rgb(255, 0, 21);
      border: 1px solid rgb(255, 0, 0);
      color: white;
      border-radius: 6px;
      cursor: pointer;
    }

    .auth-btn:hover {
      background: transparent;
      color: white;
      border: 1px solid white;
    }

    .toggle {
      text-align: center;
      margin-top: 15px;
      cursor: pointer;
      color: white;
    }

    .toggle:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="full-bg-wrapper">
    <img src="jett.jpg" class="bg-image" alt="bg valorant" />
    <div class="bg-overlay"></div>
  </div>

  <div class="top-right">
    <button id="authButton" class="auth-btn">Kembali Ke Menu Top-Up</button>
  </div>

  <div class="form-container">
    <!-- Login Form -->
    <form id="loginForm" class="form-slide active" method="POST" onsubmit="fadeOut()">
      <h2>Login</h2>
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit" name="login">Login</button>
      <div class="toggle" onclick="switchForm('register')">Belum punya akun? Daftar</div>
    </form>

    <!-- Register Form -->
    <form id="registerForm" class="form-slide" method="POST" onsubmit="fadeOut()">
      <h2>Register</h2>
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit" name="register">Register</button>
      <div class="toggle" onclick="switchForm('login')">Sudah punya akun? Login</div>
    </form>
  </div>

  <script>
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    function switchForm(to) {
      document.body.classList.add("fade-out");
      setTimeout(() => {
        loginForm.classList.remove("active");
        registerForm.classList.remove("active");
        if (to === "register") {
          registerForm.classList.add("active");
        } else {
          loginForm.classList.add("active");
        }
        document.body.classList.remove("fade-out");
      }, 300);
    }

    function fadeOut() {
      document.body.classList.add("fade-out");
    }

    document.getElementById("authButton").addEventListener("click", function () {
      document.body.classList.add("fade-out");
      setTimeout(() => {
        window.location.href = "index.php";
      }, 500);
    });
  </script>
</body>
</html>
