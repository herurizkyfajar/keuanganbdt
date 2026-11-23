<?php
// profile.php - Kelola profil akun pengguna
require_once 'includes/header.php';
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, email, name, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name) || empty($email)) {
        $message = '<div class="alert alert-danger">Nama dan email harus diisi.</div>';
    } else {
        // Check if email is already used by another user
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $chk->execute([$email, $userId]);
        if ($chk->fetch()) {
            $message = '<div class="alert alert-danger">Email sudah digunakan oleh pengguna lain.</div>';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $userId]);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $message = '<div class="alert alert-success">Profil berhasil diperbarui.</div>';
            $user['name'] = $name;
            $user['email'] = $email;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = '<div class="alert alert-danger">Semua kolom password harus diisi.</div>';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $message = '<div class="alert alert-danger">Password saat ini salah.</div>';
    } elseif ($newPassword !== $confirmPassword) {
        $message = '<div class="alert alert-danger">Password baru dan konfirmasi tidak cocok.</div>';
    } elseif (strlen($newPassword) < 6) {
        $message = '<div class="alert alert-danger">Password baru minimal 6 karakter.</div>';
    } else {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);
        $message = '<div class="alert alert-success">Password berhasil diubah.</div>';
    }
}
?>
<script>
document.getElementById('pageTitle').textContent = 'Profil Akun';
document.getElementById('pageSubtitle').textContent = 'Kelola data pribadi dan kata sandi';
</script>

<?php echo $message; ?>

<div class="section">
  <div class="section-header"><i class="bi bi-person"></i> Profil Akun</div>
  <div class="section-body">
    <form method="POST" action="">
      <input type="hidden" name="action" value="update_profile">
      <div class="form-grid">
        <div class="form-col-2">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
        </div>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>
        <div>
          <label class="form-label">Role</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars(ucfirst($user['role'] ?? 'user')) ?>" disabled>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Simpan Perubahan</button>
    </form>
  </div>
</div>

<div class="section" style="margin-top:2rem;">
  <div class="section-header"><i class="bi bi-key"></i> Ubah Kata Sandi</div>
  <div class="section-body">
    <form method="POST" action="" style="max-width:420px;">
      <input type="hidden" name="action" value="change_password">
      <div class="form-group">
        <label class="form-label">Password Saat Ini</label>
        <input type="password" name="current_password" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password Baru</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Ubah Password</button>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
