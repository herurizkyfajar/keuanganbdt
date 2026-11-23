<?php
// login.php - Halaman login standalone (tanpa sidebar/header app)
require_once __DIR__ . '/config/database.php';
session_start();

// If already logged in, redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT id, email, password, name, role, is_active FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_active']) {
                $error = 'Akun Anda tidak aktif. Hubungi administrator.';
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                if (empty($_SESSION['business_id'])) {
                    $biz = $pdo->query("SELECT id FROM businesses ORDER BY id ASC LIMIT 1")->fetch();
                    $_SESSION['business_id'] = $biz ? (int)$biz['id'] : null;
                    $_SESSION['business_name'] = $biz ? $biz['name'] : 'Manajer Akuntansi';
                }

                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Manajer Akuntansi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}
body {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: #f8f9fa;
}
.login-wrapper {
  width: 100%;
  max-width: 420px;
  padding: 2rem;
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 0.75rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  box-sizing: border-box;
}
.login-wrapper h2 {
  margin: 0 0 1.5rem;
  font-size: 1.25rem;
  color: #333;
  text-align: center;
}
.login-wrapper .form-group {
  margin-bottom: 1rem;
}
.login-wrapper label {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.85rem;
  color: #495057;
  font-weight: 600;
}
.login-wrapper input {
  width: 100%;
  padding: 0.6rem 0.8rem;
  border: 1px solid #ced4da;
  border-radius: 0.5rem;
  font-size: 0.95rem;
  box-sizing: border-box;
}
.login-wrapper input:focus {
  outline: none;
  border-color: #0d6efd;
}
.login-wrapper .btn {
  width: 100%;
  padding: 0.65rem;
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 0.5rem;
}
.login-wrapper .password-input-wrapper {
  position: relative;
}
.login-wrapper .password-toggle {
  position: absolute;
  right: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.4rem;
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
}
.login-wrapper .password-toggle:hover {
  color: #0d6efd;
}
.login-wrapper .password-toggle i {
  font-size: 1rem;
}
.login-wrapper .btn:hover {
  background: #0b5ed7;
}
.login-wrapper .error {
  color: #dc3545;
  font-size: 0.85rem;
  margin-bottom: 1rem;
  text-align: center;
  background: #f8d7da;
  padding: 0.6rem;
  border-radius: 0.5rem;
  border: 1px solid #f5c2c7;
}
.login-wrapper .footer {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 0.85rem;
  color: #6c757d;
}
</style>
</head>
<body>
<div class="login-wrapper">
  <h2>Masuk ke Akun</h2>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" action="">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" placeholder="email@gmail.com" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <div class="password-input-wrapper">
        <input type="password" id="password" name="password" placeholder="••••••••" required>
        <button type="button" id="togglePassword" class="password-toggle" aria-label="Tampilkan password">
          <i class="bi bi-eye"></i>
        </button>
      </div>
    </div>
    <button type="submit" class="btn">Masuk</button>
  </form>
  <div class="footer">
    Belum punya akun? Silakan hubungi administrator.
  </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
  const passwordInput = document.getElementById('password');
  const icon = this.querySelector('i');
  const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordInput.setAttribute('type', type);
  icon.classList.toggle('bi-eye');
  icon.classList.toggle('bi-eye-slash');
});
</script>
</body>
</html>
