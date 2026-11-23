<?php
// suppliers.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE business_id = ? AND is_active = 1 ORDER BY name ASC");
$stmt->execute([$businessId]);
$suppliers = $stmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_supplier') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $tax = $_POST['tax_number'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO suppliers (business_id, name, email, phone, address, tax_number) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$businessId, $name, $email, $phone, $address, $tax]);
    header('Location: suppliers.php?saved=1'); exit;
}
$saved = isset($_GET['saved']);
?>
<script>
document.getElementById('pageTitle').textContent = 'Pemasok';
document.getElementById('pageSubtitle').textContent = 'Data pemasok dan utang dagang';
document.getElementById('pageActions').innerHTML = `
  <button class="btn btn-primary" onclick="openModal('modalAddSupplier')"><i class="bi bi-plus-lg me-1"></i> Tambah Pemasok</button>
`;
</script>

<?php echo $message; ?>
<?php if ($saved): ?><div class="alert alert-success">Pemasok berhasil ditambahkan.</div><?php endif; ?>

<div class="stat-grid" style="margin-bottom:1rem;">
  <div class="stat-card">
    <div class="stat-icon bg-warning"><i class="bi bi-person-badge"></i></div>
    <div class="stat-info">
      <h3><?= count($suppliers) ?></h3>
      <p>Pemasok Aktif</p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon bg-danger"><i class="bi bi-currency-dollar"></i></div>
    <div class="stat-info">
      <h3><?= number_format(array_sum(array_column($suppliers, 'balance')), 0, ',', '.') ?></h3>
      <p>Total Hutang</p>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-person-badge"></i> Daftar Pemasok</div>
  </div>
  <div class="section-body p-0">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:24%">NAMA</th>
            <th style="width:20%">EMAIL</th>
            <th style="width:16%">TELEPON</th>
            <th class="text-right" style="width:16%">SALDO</th>
            <th style="width:14%"></th>
          </tr>
        </thead>
        <tbody>
        <?php if ($suppliers): foreach ($suppliers as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
            <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
            <td class="text-right amount-col"><?= number_format($s['balance'], 0, ',', '.') ?></td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm"><i class="bi bi-pencil"></i></a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pemasok.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalAddSupplier">
  <div class="modal" style="max-width:560px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_supplier">
      <div class="modal-header">
        <div class="modal-title">Tambah Pemasok</div>
        <button class="modal-close" onclick="closeModal('modalAddSupplier')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-col-2">
            <label class="form-label">Nama Pemasok</label>
            <input type="text" name="name" class="form-control" placeholder="Contoh: CV Supplier Sejahtera" required>
          </div>
          <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="supplier@email.com">
          </div>
          <div>
            <label class="form-label">Telepon</label>
            <input type="text" name="phone" class="form-control" placeholder="08123456789">
          </div>
          <div class="form-col-2">
            <label class="form-label">Alamat</label>
            <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
          </div>
          <div>
            <label class="form-label">NPWP</label>
            <input type="text" name="tax_number" class="form-control" placeholder="01.234.567.8-901.000">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal('modalAddSupplier')">Batal</button>
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); }); });
</script>

<?php require_once 'includes/footer.php'; ?>