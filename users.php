<?php
// users.php - Kelola data pengguna admin
require_once 'includes/header.php';
$userId = $_SESSION['user_id'];
$businessId = $_SESSION['business_id'];

$message = '';

// Ambil semua pengguna
$stmt = $pdo->prepare("SELECT id, email, name, role, is_active, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

// Tambah pengguna baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name) || empty($email) || empty($password)) {
        $message = '<div class="alert alert-danger">Nama, email, dan password harus diisi.</div>';
    } elseif (strlen($password) < 6) {
        $message = '<div class="alert alert-danger">Password minimal 6 karakter.</div>';
    } else {
        // Cek email duplikat
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $message = '<div class="alert alert-danger">Email sudah terdaftar.</div>';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role, business_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$email, $hash, $name, $role, $businessId, $is_active]);
            $message = '<div class="alert alert-success">Pengguna berhasil ditambahkan.</div>';
            // Refresh daftar
            $stmt = $pdo->prepare("SELECT id, email, name, role, is_active, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll();
        }
    }
}

// Update pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Jangan izinkan nonaktifkan diri sendiri
    if ($id === $userId && !$is_active) {
        $message = '<div class="alert alert-danger">Tidak dapat menonaktifkan akun sendiri.</div>';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $email, $role, $is_active, $id]);
        $message = '<div class="alert alert-success">Pengguna berhasil diperbarui.</div>';
        $stmt = $pdo->prepare("SELECT id, email, name, role, is_active, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll();
    }
}

// Hapus pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === $userId) {
        $message = '<div class="alert alert-danger">Tidak dapat menghapus akun sendiri.</div>';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<div class="alert alert-success">Pengguna berhasil dihapus.</div>';
        $stmt = $pdo->prepare("SELECT id, email, name, role, is_active, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll();
    }
}
?>
<script>
document.getElementById('pageTitle').textContent = 'Kelola Pengguna';
document.getElementById('pageSubtitle').textContent = 'Kelola data akun admin dan pengguna';
</script>

<?php echo $message; ?>

<div class="section">
  <div class="section-header">
    <i class="bi bi-people"></i> Kelola Pengguna
    <button class="btn btn-primary" style="float:right;" onclick="openModal('modalAddUser')"><i class="bi bi-plus-lg me-1"></i> Tambah Pengguna</button>
  </div>
  <div class="section-body p-0">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:8%">#</th>
            <th>Nama</th>
            <th>Email</th>
            <th style="width:12%">Role</th>
            <th style="width:10%">Status</th>
            <th style="width:16%">Dibuat Pada</th>
            <th style="width:16%">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['id']) ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span class="badge" style="background:<?= $u['role'] === 'admin' ? '#cfe2ff;color:#084298' : '#f8f9fa;color:#495057' ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span>
            </td>
            <td>
              <span class="badge" style="background:<?= $u['is_active'] ? '#d1e7dd;color:#0f5132' : '#f8d7da;color:#842029' ?>">
                <?= $u['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
              </span>
            </td>
            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($u['created_at']))) ?></td>
            <td>
              <button class="btn btn-sm btn-outline" onclick="editUser(<?= htmlspecialchars(json_encode([
                  'id' => $u['id'],
                  'name' => $u['name'],
                  'email' => $u['email'],
                  'role' => $u['role'],
                  'is_active' => (int)$u['is_active'],
              ])) ?>)"><i class="bi bi-pencil"></i></button>
              <?php if ($u['id'] !== $userId): ?>
                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline" style="color:#dc3545;"><i class="bi bi-trash"></i></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah / Edit Pengguna -->
<div class="modal-overlay" id="modalAddUser">
  <div class="modal" style="max-width:500px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_user">
      <input type="hidden" id="editUserId" name="id">
      <div class="modal-header">
        <div class="modal-title" id="modalUserTitle">Tambah Pengguna</div>
        <button class="modal-close" onclick="closeModal('modalAddUser')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-grid">
          <div>
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="form-col-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="admin">Admin</option>
              <option value="user">User</option>
            </select>
          </div>
          <div>
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="form-col-2">
            <label class="form-label" style="display:flex; align-items:center; gap:0.5rem;">
              <input type="checkbox" name="is_active" value="1" checked>
              Aktif
            </label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal('modalAddUser')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); }); });

function editUser(data) {
  document.getElementById('modalUserTitle').textContent = 'Edit Pengguna';
  document.getElementById('editUserId').value = data.id;
  document.querySelector('#modalAddUser input[name="name"]').value = data.name;
  document.querySelector('#modalAddUser input[name="email"]').value = data.email;
  document.querySelector('#modalAddUser select[name="role"]').value = data.role;
  document.querySelector('#modalAddUser input[name="is_active"]').checked = data.is_active == 1;
  // Hapus required dari password saat edit
  document.querySelector('#modalAddUser input[name="password"]').required = false;
  document.querySelector('#modalAddUser input[name="password"]').placeholder = 'Kosongkan jika tidak ingin mengubah';
  document.querySelector('#modalAddUser form').setAttribute('action', '');
  // Ubah aksi menjadi update_user
  const form = document.querySelector('#modalAddUser form');
  const actionInput = document.querySelector('#modalAddUser input[name="action"]');
  actionInput.value = 'update_user';
  openModal('modalAddUser');
}

// Reset form saat membuka modal tambah baru
document.querySelector('#modalAddUser .modal-close').addEventListener('click', function() {
  const form = document.querySelector('#modalAddUser form');
  const actionInput = document.querySelector('#modalAddUser input[name="action"]');
  actionInput.value = 'add_user';
  form.reset();
  document.querySelector('#modalAddUser input[name="is_active"]').checked = true;
  document.querySelector('#modalAddUser input[name="password"]').required = true;
  document.querySelector('#modalAddUser input[name="password"]').placeholder = '';
  document.getElementById('modalUserTitle').textContent = 'Tambah Pengguna';
  document.getElementById('editUserId').value = '';
});
</script>

<?php require_once 'includes/footer.php'; ?>
