<?php
// items.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$stmt = $pdo->prepare("SELECT * FROM items WHERE business_id = ? AND is_active = 1 ORDER BY name ASC");
$stmt->execute([$businessId]);
$items = $stmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    $code = $_POST['code'] ?? '';
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? 'service';
    $salesPrice = (float) ($_POST['sales_price'] ?? 0);
    $purchasePrice = (float) ($_POST['purchase_price'] ?? 0);
    $stmt = $pdo->prepare("INSERT INTO items (business_id, code, name, description, type, sales_price, purchase_price) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$businessId, $code, $name, $desc, $type, $salesPrice, $purchasePrice]);
    header('Location: items.php?saved=1'); exit;
}
$saved = isset($_GET['saved']);
?>
<script>
document.getElementById('pageTitle').textContent = 'Barang & Jasa';
document.getElementById('pageSubtitle').textContent = 'Daftar produk dan jasa untuk faktur';
document.getElementById('pageActions').innerHTML = `
  <button class="btn btn-primary" onclick="openModal('modalAddItem')"><i class="bi bi-plus-lg me-1"></i> Tambah Item</button>
`;
</script>

<?php echo $message; ?>
<?php if ($saved): ?><div class="alert alert-success">Item berhasil ditambahkan.</div><?php endif; ?>

<div class="stat-grid" style="margin-bottom:1rem;">
  <div class="stat-card">
    <div class="stat-icon bg-info"><i class="bi bi-box-seam"></i></div>
    <div class="stat-info">
      <h3><?= count($items) ?></h3>
      <p>Item Aktif</p>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-box-seam"></i> Daftar Barang & Jasa</div>
  </div>
  <div class="section-body p-0">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:12%">KODE</th>
            <th style="width:26%">NAMA</th>
            <th style="width:12%">TIPE</th>
            <th class="text-right" style="width:18%">HARGA JUAL</th>
            <th class="text-right" style="width:18%">HARGA BELI</th>
            <th style="width:10%"></th>
          </tr>
        </thead>
        <tbody>
        <?php if ($items): foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['code'] ?? '-') ?></td>
            <td><?= htmlspecialchars($it['name']) ?></td>
            <td><span class="badge" style="background:#e9ecef;color:#495057;"><?= ucfirst($it['type']) ?></span></td>
            <td class="text-right amount-col"><?= number_format($it['sales_price'], 0, ',', '.') ?></td>
            <td class="text-right amount-col"><?= number_format($it['purchase_price'], 0, ',', '.') ?></td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm"><i class="bi bi-pencil"></i></a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada item.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalAddItem">
  <div class="modal" style="max-width:560px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_item">
      <div class="modal-header">
        <div class="modal-title">Tambah Barang / Jasa</div>
        <button class="modal-close" onclick="closeModal('modalAddItem')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-grid">
          <div>
            <label class="form-label">Kode Item</label>
            <input type="text" name="code" class="form-control" placeholder="ITEM-001">
          </div>
          <div class="form-col-2">
            <label class="form-label">Nama Item</label>
            <input type="text" name="name" class="form-control" placeholder="Contoh: Laptop ASUS" required>
          </div>
          <div>
            <label class="form-label">Tipe</label>
            <select name="type" class="form-select">
              <option value="service">Jasa</option>
              <option value="product">Produk</option>
            </select>
          </div>
          <div>
            <label class="form-label">Deskripsi</label>
            <input type="text" name="description" class="form-control" placeholder="Deskripsi singkat">
          </div>
          <div>
            <label class="form-label">Harga Jual</label>
            <input type="number" name="sales_price" class="form-control" step="0.01" min="0" value="0">
          </div>
          <div>
            <label class="form-label">Harga Beli</label>
            <input type="number" name="purchase_price" class="form-control" step="0.01" min="0" value="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal('modalAddItem')">Batal</button>
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