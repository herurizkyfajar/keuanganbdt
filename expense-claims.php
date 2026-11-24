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
  <div class="modal modal-modern" style="max-width:840px;">
    <form method="POST" action="" onsubmit="return validateExpense(this)">
      <input type="hidden" name="action" value="add_expense_claim">
      <div class="mdlg-header">
        <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
          <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
          <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
          <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
        </svg>
        <div class="mdlg-header-icon"><i class="bi bi-wallet2"></i></div>
        <div class="mdlg-header-text">
          <div class="mdlg-header-title">Buat Klaim Biaya</div>
          <div class="mdlg-header-sub">Input detail klaim karyawan</div>
        </div>
        <button type="button" class="mdlg-close" onclick="closeModal('modalAddExpense')" aria-label="Tutup">&times;</button>
      </div>
      <div class="mdlg-body">
        <div class="mdlg-grid">
          <div class="mdlg-field">
            <label class="mdlg-label" for="expEmployee"><i class="bi bi-person"></i> Nama Karyawan <span class="req">*</span></label>
            <input type="text" id="expEmployee" name="employee_name" class="mdlg-input" placeholder="Contoh: Budi Santoso" required>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="expDate"><i class="bi bi-calendar-event"></i> Tanggal Klaim <span class="req">*</span></label>
            <input type="date" id="expDate" name="claim_date" class="mdlg-input" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="expStatus"><i class="bi bi-envelope"></i> Status</label>
            <select id="expStatus" name="status" class="mdlg-input">
              <option value="draft">Draft</option>
              <option value="submitted">Diajukan</option>
            </select>
          </div>
        </div>

        <div class="table-responsive mdlg-table-wrap">
          <table class="mdlg-table" id="expLines">
            <colgroup>
              <col style="width:22%">
              <col style="width:48%">
              <col style="width:22%">
              <col style="width:8%">
            </colgroup>
            <thead>
              <tr>
                <th>Akun</th>
                <th>Deskripsi</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right"></th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < 2; $i++): ?>
                <tr>
                  <td>
                    <select name="line_account_id[]" class="mdlg-input" required>
                      <option value="">Pilih akun</option>
                      <?php foreach ($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><input type="text" name="line_desc[]" class="mdlg-input" placeholder="Deskripsi"></td>
                  <td class="text-right"><input type="number" name="line_amount[]" class="mdlg-input" step="0.01" min="0" value="0"></td>
                  <td class="text-right mdlg-act"><button type="button" class="mdlg-btn-del" title="Hapus baris"><i class="bi bi-trash"></i></button></td>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>

        <button type="button" class="mdlg-btn-add" id="addExpenseLine"><i class="bi bi-plus-lg"></i> Tambah Baris</button>

        <div class="mt-2">
          <label class="mdlg-label" for="expNotes"><i class="bi bi-chat-text"></i> Catatan</label>
          <textarea id="expNotes" name="notes" class="mdlg-input" rows="2"></textarea>
        </div>
      </div>
      <div class="mdlg-footer">
        <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
          <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
        </svg>
        <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalAddExpense')">Batal</button>
        <button type="submit" class="mdlg-btn-save"><i class="bi bi-save"></i> Simpan Klaim</button>
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
document.querySelector('#expLines tbody')?.addEventListener('click', function (e) {
  const btn = e.target.closest('.mdlg-btn-del');
  if (!btn) return;
  const tbody = document.querySelector('#expLines tbody');
  if (tbody.children.length > 1) btn.closest('tr').remove();
});
function validateExpense(form) { return true; }
</script>

<?php require_once 'includes/footer.php'; ?>