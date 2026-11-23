<?php
// sales-invoices.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$stmt = $pdo->prepare("SELECT id, name FROM customers WHERE business_id = ? AND is_active = 1 ORDER BY name ASC");
$stmt->execute([$businessId]);
$customers = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT id, code, name, sales_price FROM items WHERE business_id = ? AND is_active = 1 ORDER BY name ASC");
$stmt->execute([$businessId]);
$items = $stmt->fetchAll();

$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$inv = null;
$lines = [];
if ($viewId) {
    $stmt = $pdo->prepare("SELECT s.*, c.name AS customer_name FROM sales_invoices s LEFT JOIN customers c ON c.id = s.customer_id WHERE s.id = ? AND s.business_id = ?");
    $stmt->execute([$viewId, $businessId]);
    $inv = $stmt->fetch();
    if ($inv) {
        $stmt = $pdo->prepare("SELECT l.*, i.code AS item_code FROM sales_invoice_lines l LEFT JOIN items i ON i.id = l.item_id WHERE l.sales_invoice_id = ?");
        $stmt->execute([$viewId]);
        $lines = $stmt->fetchAll();
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_sales_invoice') {
    try {
        $pdo->beginTransaction();
        $customer = (int) ($_POST['customer_id'] ?? 0);
        $number   = $_POST['invoice_number'] ?? '';
        $date     = $_POST['invoice_date'] ?? date('Y-m-d');
        $due      = $_POST['due_date'] ?? null;
        $status   = $_POST['status'] ?? 'draft';
        $notes    = $_POST['notes'] ?? '';
        $subtotal = 0; $tax = 0; $total = 0;
        $stmt = $pdo->prepare("INSERT INTO sales_invoices (business_id, customer_id, invoice_number, invoice_date, due_date, status, subtotal, tax_amount, total, notes) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$businessId, $customer, $number, $date, $due, $status, 0, 0, 0, $notes]);
        $invId = $pdo->lastInsertId();
        foreach ($_POST['line_item_id'] ?? [] as $idx => $itemId) {
            $desc    = $_POST['line_desc'][$idx] ?? '';
            $qty     = (float) ($_POST['line_qty'][$idx] ?? 1);
            $price   = (float) ($_POST['line_price'][$idx] ?? 0);
            $amount  = $qty * $price;
            $accId   = $_POST['line_account_id'][$idx] ?? null;
            $subtotal += $amount;
            $stmt = $pdo->prepare("INSERT INTO sales_invoice_lines (sales_invoice_id, item_id, description, quantity, unit_price, amount, account_id) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$invId, $itemId ?: null, $desc, $qty, $price, $amount, $accId]);
        }
        $tax = 0;
        $total = $subtotal + $tax;
        $pdo->prepare("UPDATE sales_invoices SET subtotal=?, tax_amount=?, total=? WHERE id=?")->execute([$subtotal, $tax, $total, $invId]);
        $pdo->commit();
        header('Location: sales-invoices.php?view=' . $invId . '&saved=1'); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
$saved = isset($_GET['saved']);
?>
<script>
document.getElementById('pageTitle').textContent = 'Faktur Penjualan';
document.getElementById('pageSubtitle').textContent = 'Buat dan kelola faktur penjualan kepada pelanggan';
document.getElementById('pageActions').innerHTML = `
  <button class="btn btn-primary" onclick="openModal('modalAddInvoice')"><i class="bi bi-plus-lg me-1"></i> Faktur Baru</button>
`;
</script>

<?php echo $message; ?>
<?php if ($saved): ?><div class="alert alert-success">Faktur penjualan berhasil dibuat.</div><?php endif; ?>

<div class="stat-grid" style="margin-bottom:1rem;">
  <div class="stat-card">
    <div class="stat-icon bg-primary"><i class="bi bi-receipt-cutoff"></i></div>
    <div class="stat-info">
      <h3><?= $pdo->query("SELECT COUNT(*) FROM sales_invoices WHERE business_id=$businessId")->fetchColumn() ?></h3>
      <p>Total Faktur</p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon bg-success"><i class="bi bi-currency-dollar"></i></div>
    <div class="stat-info">
      <h3><?= number_format($pdo->query("SELECT COALESCE(SUM(total),0) FROM sales_invoices WHERE business_id=$businessId AND status='paid'")->fetchColumn(), 0, ',', '.') ?></h3>
      <p>Terbayar</p>
    </div>
  </div>
</div>

<?php if ($viewId && $inv): ?>
<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-receipt-cutoff"></i> Faktur #<?= htmlspecialchars($inv['invoice_number']) ?></div>
    <a href="sales-invoices.php" class="btn btn-outline btn-sm">Kembali</a>
  </div>
  <div class="section-body">
    <div class="row g-3 mb-3">
      <div class="col-md-3"><strong>Pelanggan:</strong> <?= htmlspecialchars($inv['customer_name']) ?></div>
      <div class="col-md-3"><strong>Tanggal:</strong> <?= date('d M Y', strtotime($inv['invoice_date'])) ?></div>
      <div class="col-md-3"><strong>Jatuh Tempo:</strong> <?= $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '-' ?></div>
      <div class="col-md-3">
        <span class="badge badge-<?= $inv['status'] ?>"><?= ucfirst($inv['status']) ?></span>
      </div>
    </div>
    <p><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($inv['notes'] ?? '-')) ?></p>
    <div class="table-responsive mt-3">
      <table class="data-table">
        <thead>
          <tr><th>ITEM</th><th>DESKRIPSI</th><th class="text-right" style="width:10%">QTY</th><th class="text-right" style="width:18%">HARGA SATUAN</th><th class="text-right" style="width:18%">JUMLAH</th></tr>
        </thead>
        <tbody>
        <?php $tot = 0; foreach ($lines as $l): $tot += $l['amount']; ?>
          <tr>
            <td><?= htmlspecialchars($l['item_code'] ?? '-') ?></td>
            <td><?= htmlspecialchars($l['description']) ?></td>
            <td class="text-right"><?= number_format($l['quantity'], 0, ',', '.') ?></td>
            <td class="text-right amount-col"><?= number_format($l['unit_price'], 0, ',', '.') ?></td>
            <td class="text-right amount-col"><?= number_format($l['amount'], 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
          <tr class="fw-bold" style="border-top:2px solid var(--border);">
            <td colspan="3">Total</td>
            <td class="text-right amount-col"><?= number_format($inv['total'], 0, ',', '.') ?></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php else: ?>
<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-receipt-cutoff"></i> Daftar Faktur Penjualan</div>
  </div>
  <div class="section-body p-0">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:14%">NOMOR FAKTUR</th>
            <th style="width:20%">PELANGGAN</th>
            <th style="width:12%">TANGGAL</th>
            <th style="width:12%">JATUH TEMPO</th>
            <th class="text-right" style="width:16%">TOTAL</th>
            <th style="width:14%">STATUS</th>
            <th class="text-right" style="width:10%"></th>
          </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $pdo->prepare("
          SELECT s.*, c.name AS customer_name 
          FROM sales_invoices s LEFT JOIN customers c ON c.id = s.customer_id
          WHERE s.business_id = ? ORDER BY s.invoice_date DESC, s.id DESC LIMIT 100
        ");
        $stmt->execute([$businessId]);
        $rows = $stmt->fetchAll();
        if ($rows): foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['invoice_number']) ?></td>
            <td><?= htmlspecialchars($r['customer_name'] ?? '-') ?></td>
            <td><?= date('d M Y', strtotime($r['invoice_date'])) ?></td>
            <td><?= $r['due_date'] ? date('d M Y', strtotime($r['due_date'])) : '-' ?></td>
            <td class="text-right amount-col"><?= number_format($r['total'], 0, ',', '.') ?></td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm" href="sales-invoices.php?view=<?= $r['id'] ?>"><i class="bi bi-eye"></i></a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7" class="text-center text-muted py-3">Belum ada faktur penjualan.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Tambah Faktur -->
<div class="modal-overlay" id="modalAddInvoice">
  <div class="modal" style="max-width:820px;">
    <form method="POST" action="" onsubmit="return calcInvoice(this)">
      <input type="hidden" name="action" value="add_sales_invoice">
      <div class="modal-header">
        <div class="modal-title">Buat Faktur Penjualan</div>
        <button class="modal-close" onclick="closeModal('modalAddInvoice')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Pelanggan</label>
            <select name="customer_id" class="form-select" required>
              <option value="">Pilih pelanggan</option>
              <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Nomor Faktur</label>
            <input type="text" name="invoice_number" class="form-control" placeholder="INV-0001" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tanggal Faktur</label>
            <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Jatuh Tempo</label>
            <input type="date" name="due_date" class="form-control">
          </div>
        </div>
        <div class="table-responsive mb-2">
          <table class="data-table" id="invLines">
            <thead>
              <tr>
                <th style="width:14%">ITEM</th>
                <th style="width:30%">DESKRIPSI</th>
                <th class="text-right" style="width:10%">QTY</th>
                <th class="text-right" style="width:18%">HARGA SATUAN</th>
                <th class="text-right" style="width:18%">JUMLAH</th>
                <th class="text-right" style="width:8%"></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <select name="line_item_id[]" class="form-select form-select-sm">
                    <option value="">Pilih item</option>
                    <?php foreach ($items as $it): ?>
                      <option value="<?= $it['id'] ?>" data-price="<?= $it['sales_price'] ?>"><?= htmlspecialchars($it['code'] . ' - ' . $it['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="text" name="line_desc[]" class="form-control form-control-sm" placeholder="Deskripsi"></td>
                <td class="text-right"><input type="number" name="line_qty[]" class="form-control form-control-sm qty" step="0.01" min="0" value="1"></td>
                <td class="text-right"><input type="number" name="line_price[]" class="form-control form-control-sm price" step="0.01" min="0" value="0"></td>
                <td class="text-right amount-col line-amount">0</td>
                <td class="text-right"><button type="button" class="btn btn-outline btn-sm rem"><i class="bi bi-trash"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="text-end mt-2">
          <button type="button" class="btn btn-outline btn-sm" id="addInvLine">+ Tambah Baris</button>
        </div>
        <div class="row mt-3">
          <div class="col-md-6">
            <label class="form-label">Catatan</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-md-6 d-flex flex-column justify-content-end">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="draft">Draft</option>
              <option value="sent">Terkirim</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal('modalAddInvoice')">Batal</button>
        <button class="btn btn-primary">Simpan Faktur</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); }); });
document.getElementById('addInvLine')?.addEventListener('click', function () {
  const tbody = document.querySelector('#invLines tbody');
  const row = tbody.querySelector('tr').cloneNode(true);
  row.querySelectorAll('input,select').forEach(el => el.value = '');
  row.querySelector('.line-amount').textContent = '0';
  tbody.appendChild(row);
});
document.querySelectorAll('.rem').forEach(btn => {
  btn.addEventListener('click', function () {
    const tbody = document.querySelector('#invLines tbody');
    if (tbody.children.length > 1) this.closest('tr').remove();
  });
});
document.addEventListener('input', function (e) {
  const row = e.target.closest('tr');
  if (!row) return;
  const qty = row.querySelector('input[name="line_qty[]"]');
  const price = row.querySelector('input[name="line_price[]"]');
  const amount = row.querySelector('.line-amount');
  if (qty && price && amount) {
    amount.textContent = ((parseFloat(qty.value) || 0) * (parseFloat(price.value) || 0)).toLocaleString('id-ID', {minimumFractionDigits:0});
  }
});
document.addEventListener('change', function (e) {
  if (e.target.name === 'line_item_id[]') {
    const row = e.target.closest('tr');
    const priceInput = row.querySelector('input[name="line_price[]"]');
    const descInput = row.querySelector('input[name="line_desc[]"]');
    const opt = e.target.selectedOptions[0];
    if (opt && opt.dataset.price) {
      priceInput.value = opt.dataset.price;
      descInput.value = e.target.options[e.target.selectedIndex].text;
    }
  }
});
function calcInvoice(form) { return true; }
</script>

<?php require_once 'includes/footer.php'; ?>