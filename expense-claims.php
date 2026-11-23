<?php
// expense-claims.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$stmt = $pdo->prepare("SELECT id, code, name FROM accounts WHERE business_id = ? AND is_active = 1 ORDER BY code ASC");
$stmt->execute([$businessId]);
$accounts = $stmt->fetchAll();

$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$claim = null;
$lines = [];
if ($viewId) {
    $stmt = $pdo->prepare("SELECT * FROM expense_claims WHERE id = ? AND business_id = ?");
    $stmt->execute([$viewId, $businessId]);
    $claim = $stmt->fetch();
    if ($claim) {
        $stmt = $pdo->prepare("SELECT l.*, a.code, a.name FROM expense_claim_lines l LEFT JOIN accounts a ON a.id = l.account_id WHERE l.expense_claim_id = ?");
        $stmt->execute([$viewId]);
        $lines = $stmt->fetchAll();
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_expense_claim') {
    try {
        $pdo->beginTransaction();
        $emp   = $_POST['employee_name'] ?? '';
        $date  = $_POST['claim_date'] ?? date('Y-m-d');
        $status= $_POST['status'] ?? 'draft';
        $notes = $_POST['notes'] ?? '';
        $total = 0;
        $stmt = $pdo->prepare("INSERT INTO expense_claims (business_id, employee_name, claim_date, status, total, notes) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$businessId, $emp, $date, $status, 0, $notes]);
        $claimId = $pdo->lastInsertId();
        foreach ($_POST['line_account_id'] ?? [] as $idx => $accId) {
            if (!$accId) continue;
            $amount = (float) ($_POST['line_amount'][$idx] ?? 0);
            $desc   = $_POST['line_desc'][$idx] ?? '';
            $total += $amount;
            $stmt = $pdo->prepare("INSERT INTO expense_claim_lines (expense_claim_id, account_id, description, amount) VALUES (?,?,?,?)");
            $stmt->execute([$claimId, $accId, $desc, $amount]);
        }
        $pdo->prepare("UPDATE expense_claims SET total=? WHERE id=?")->execute([$total, $claimId]);
        $pdo->commit();
        header('Location: expense-claims.php?view=' . $claimId . '&saved=1'); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
$saved = isset($_GET['saved']);
?>
<script>
document.getElementById('pageTitle').textContent = 'Klaim Biaya';
document.getElementById('pageSubtitle').textContent = 'Catat dan kelola klaim biaya karyawan';
document.getElementById('pageActions').innerHTML = `
  <button class="btn btn-primary" onclick="openModal('modalAddExpense')"><i class="bi bi-plus-lg me-1"></i> Klaim Baru</button>
`;
</script>

<?php echo $message; ?>
<?php if ($saved): ?><div class="alert alert-success">Klaim biaya berhasil dibuat.</div><?php endif; ?>

<?php if ($viewId && $claim): ?>
<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-wallet2"></i> Klaim #<?= $claim['id'] ?></div>
    <a href="expense-claims.php" class="btn btn-outline btn-sm">Kembali</a>
  </div>
  <div class="section-body">
    <div class="row g-3 mb-3">
      <div class="col-md-3"><strong>Karyawan:</strong> <?= htmlspecialchars($claim['employee_name'] ?? '-') ?></div>
      <div class="col-md-3"><strong>Tanggal:</strong> <?= date('d M Y', strtotime($claim['claim_date'])) ?></div>
      <div class="col-md-3"><strong>Status:</strong> <span class="badge badge-<?= $claim['status'] ?>"><?= ucfirst($claim['status']) ?></span></div>
      <div class="col-md-3 text-end"><strong>Total:</strong> <?= number_format($claim['total'], 0, ',', '.') ?></div>
    </div>
    <p><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($claim['notes'] ?? '-')) ?></p>
    <div class="table-responsive mt-3">
      <table class="data-table">
        <thead>
          <tr><th>KODE</th><th>AKUN</th><th>DESKRIPSI</th><th class="text-right" style="width:18%">JUMLAH</th></tr>
        </thead>
        <tbody>
        <?php foreach ($lines as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['code']) ?></td>
            <td><?= htmlspecialchars($l['name']) ?></td>
            <td><?= htmlspecialchars($l['description']) ?></td>
            <td class="text-right amount-col"><?= number_format($l['amount'], 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php else: ?>
<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-wallet2"></i> Daftar Klaim Biaya</div>
  </div>
  <div class="section-body p-0">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:8%">ID</th>
            <th style="width:20%">KARYAWAN</th>
            <th style="width:12%">TANGGAL</th>
            <th class="text-right" style="width:16%">TOTAL</th>
            <th style="width:16%">STATUS</th>
            <th class="text-right" style="width:10%"></th>
          </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM expense_claims WHERE business_id = ? ORDER BY claim_date DESC, id DESC LIMIT 100");
        $stmt->execute([$businessId]);
        $rows = $stmt->fetchAll();
        if ($rows): foreach ($rows as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['employee_name'] ?? '-') ?></td>
            <td><?= date('d M Y', strtotime($r['claim_date'])) ?></td>
            <td class="text-right amount-col"><?= number_format($r['total'], 0, ',', '.') ?></td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm" href="expense-claims.php?view=<?= $r['id'] ?>"><i class="bi bi-eye"></i></a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada klaim biaya.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Tambah Klaim -->
<div class="modal-overlay" id="modalAddExpense">
  <div class="modal" style="max-width:760px;">
    <form method="POST" action="" onsubmit="return validateExpense(this)">
      <input type="hidden" name="action" value="add_expense_claim">
      <div class="modal-header">
        <div class="modal-title">Buat Klaim Biaya</div>
        <button class="modal-close" onclick="closeModal('modalAddExpense')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Nama Karyawan</label>
            <input type="text" name="employee_name" class="form-control" placeholder="Contoh: Budi Santoso" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tanggal Klaim</label>
            <input type="date" name="claim_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="draft">Draft</option>
              <option value="submitted">Diajukan</option>
            </select>
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table" id="expLines">
            <thead>
              <tr>
                <th style="width:22%">AKUN</th>
                <th style="width:30%">DESKRIPSI</th>
                <th class="text-right" style="width:22%">JUMLAH</th>
                <th class="text-right" style="width:10%"></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <select name="line_account_id[]" class="form-select form-select-sm" required>
                    <option value="">Pilih akun</option>
                    <?php foreach ($accounts as $a): ?>
                      <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="text" name="line_desc[]" class="form-control form-control-sm" placeholder="Deskripsi"></td>
                <td class="text-right"><input type="number" name="line_amount[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                <td class="text-right"><button type="button" class="btn btn-outline btn-sm rem"><i class="bi bi-trash"></i></button></td>
              </tr>
              <tr>
                <td>
                  <select name="line_account_id[]" class="form-select form-select-sm" required>
                    <option value="">Pilih akun</option>
                    <?php foreach ($accounts as $a): ?>
                      <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="text" name="line_desc[]" class="form-control form-control-sm" placeholder="Deskripsi"></td>
                <td class="text-right"><input type="number" name="line_amount[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                <td class="text-right"><button type="button" class="btn btn-outline btn-sm rem"><i class="bi bi-trash"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="text-end mt-2">
          <button type="button" class="btn btn-outline btn-sm" id="addExpenseLine">+ Tambah Baris</button>
        </div>
        <div class="mt-2">
          <label class="form-label">Catatan</label>
          <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal('modalAddExpense')">Batal</button>
        <button class="btn btn-primary">Simpan Klaim</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); }); });
document.getElementById('addExpenseLine')?.addEventListener('click', function () {
  const tbody = document.querySelector('#expLines tbody');
  const row = tbody.querySelector('tr').cloneNode(true);
  row.querySelectorAll('input,select').forEach(el => el.value = '');
  tbody.appendChild(row);
});
document.querySelectorAll('.rem').forEach(btn => {
  btn.addEventListener('click', function () {
    const tbody = document.querySelector('#expLines tbody');
    if (tbody.children.length > 1) this.closest('tr').remove();
  });
});
function validateExpense(form) { return true; }
</script>

<?php require_once 'includes/footer.php'; ?>