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
  <div class="modal modal-modern" style="max-width:560px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_item">
      <div class="mdlg-header">
        <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
          <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
          <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
          <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
        </svg>
        <div class="mdlg-header-icon"><i class="bi bi-box"></i></div>
        <div class="mdlg-header-text">
          <div class="mdlg-header-title">Tambah Barang / Jasa</div>
          <div class="mdlg-header-sub">Data item penjualan dan pembelian</div>
        </div>
        <button type="button" class="mdlg-close" onclick="closeModal('modalAddItem')" aria-label="Tutup">&times;</button>
      </div>
      <div class="mdlg-body">
        <div class="mdlg-grid">
          <div class="mdlg-field">
            <label class="mdlg-label" for="itemCode"><i class="bi bi-tag"></i> Kode Item</label>
            <input type="text" id="itemCode" name="code" class="mdlg-input" placeholder="ITEM-001">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="itemName"><i class="bi bi-fonts"></i> Nama Item <span class="req">*</span></label>
            <input type="text" id="itemName" name="name" class="mdlg-input" placeholder="Contoh: Laptop ASUS" required>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="itemType"><i class="bi bi-layers"></i> Tipe</label>
            <select id="itemType" name="type" class="mdlg-input">
              <option value="service">Jasa</option>
              <option value="product">Produk</option>
            </select>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="itemDesc"><i class="bi bi-journal-text"></i> Deskripsi</label>
            <input type="text" id="itemDesc" name="description" class="mdlg-input" placeholder="Deskripsi singkat">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="itemSalesPrice"><i class="bi bi-currency-dollar"></i> Harga Jual</label>
            <input type="number" id="itemSalesPrice" name="sales_price" class="mdlg-input" step="0.01" min="0" value="0">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="itemPurchasePrice"><i class="bi bi-currency-exchange"></i> Harga Beli</label>
            <input type="number" id="itemPurchasePrice" name="purchase_price" class="mdlg-input" step="0.01" min="0" value="0">
          </div>
        </div>
      </div>
      <div class="mdlg-footer">
        <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
          <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
        </svg>
        <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalAddItem')">Batal</button>
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