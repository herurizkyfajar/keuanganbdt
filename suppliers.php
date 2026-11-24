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
  <div class="modal modal-modern" style="max-width:560px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_supplier">
      <div class="mdlg-header">
        <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
          <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
          <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
          <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
        </svg>
        <div class="mdlg-header-icon"><i class="bi bi-building"></i></div>
        <div class="mdlg-header-text">
          <div class="mdlg-header-title">Tambah Pemasok</div>
          <div class="mdlg-header-sub">Data pemasok / vendor</div>
        </div>
        <button type="button" class="mdlg-close" onclick="closeModal('modalAddSupplier')" aria-label="Tutup">&times;</button>
      </div>
      <div class="mdlg-body">
        <div class="mdlg-grid">
          <div class="mdlg-field">
            <label class="mdlg-label" for="supName"><i class="bi bi-building"></i> Nama Pemasok <span class="req">*</span></label>
            <input type="text" id="supName" name="name" class="mdlg-input" placeholder="Contoh: CV Supplier Sejahtera" required>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="supEmail"><i class="bi bi-envelope"></i> Email</label>
            <input type="email" id="supEmail" name="email" class="mdlg-input" placeholder="supplier@email.com">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="supPhone"><i class="bi bi-telephone"></i> Telepon</label>
            <input type="text" id="supPhone" name="phone" class="mdlg-input" placeholder="08123456789">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="supAddress"><i class="bi bi-house"></i> Alamat</label>
            <textarea id="supAddress" name="address" class="mdlg-input" rows="2" placeholder="Alamat lengkap"></textarea>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="supNpwp"><i class="bi bi-hash"></i> NPWP</label>
            <input type="text" id="supNpwp" name="tax_number" class="mdlg-input" placeholder="01.234.567.8-901.000">
          </div>
        </div>
      </div>
      <div class="mdlg-footer">
        <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
          <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
        </svg>
        <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalAddSupplier')">Batal</button>
        <button type="submit" class="mdlg-btn-save"><i class="bi bi-save"></i> Simpan</button>
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