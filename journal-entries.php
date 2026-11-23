<?php
// journal-entries.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$stmt = $pdo->prepare("SELECT id, code, name FROM accounts WHERE business_id = ? AND is_active = 1 ORDER BY code ASC");
$stmt->execute([$businessId]);
$accounts = $stmt->fetchAll();

$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$txDetail = null;
$txLines = [];
if ($viewId) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND business_id = ?");
    $stmt->execute([$viewId, $businessId]);
    $txDetail = $stmt->fetch();
    if ($txDetail) {
        $stmt = $pdo->prepare("
          SELECT l.*, a.code, a.name 
          FROM transaction_lines l 
          JOIN accounts a ON a.id = l.account_id 
          WHERE l.transaction_id = ?
        ");
        $stmt->execute([$viewId]);
        $txLines = $stmt->fetchAll();
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_tx') {
    try {
        $pdo->beginTransaction();
        $ref   = $_POST['reference'] ?? '';
        $desc  = $_POST['description'] ?? '';
        $tgl   = $_POST['transaction_date'] ?? date('Y-m-d');
        $type  = $_POST['type'] ?? 'journal';
        $stmt = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (?,?,?,?,?)");
        $stmt->execute([$businessId, $ref, $desc, $tgl, $type]);
        $txId = $pdo->lastInsertId();
        $totalDebit = 0; $totalCredit = 0;
        foreach ($_POST['line_account_id'] ?? [] as $idx => $accId) {
            if (!$accId) continue;
            $debit  = (float) ($_POST['line_debit'][$idx] ?? 0);
            $credit = (float) ($_POST['line_credit'][$idx] ?? 0);
            $lDesc  = $_POST['line_desc'][$idx] ?? '';
            $totalDebit += $debit; $totalCredit += $credit;
            $stmt = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?,?,?,?,?)");
            $stmt->execute([$txId, $accId, $lDesc, $debit, $credit]);
        }
        if ($totalDebit !== $totalCredit) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">Total debit dan kredit harus sama!</div>';
        } else {
            $pdo->commit();
            header('Location: journal-entries.php?view=' . $txId . '&saved=1'); exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
$saved = isset($_GET['saved']);
?>
<script>
document.getElementById('pageTitle').textContent = 'Jurnal Umum';
document.getElementById('pageSubtitle').textContent = 'Catat semua transaksi keuangan dalam bentuk debit dan kredit';
document.getElementById('pageActions').innerHTML = `
  <button class="btn btn-primary" onclick="openModal('modalAddTx')"><i class="bi bi-plus-lg me-1"></i> Buat Jurnal</button>
`;
</script>

<?php echo $message; ?>
<?php if ($saved): ?><div class="alert alert-success">Jurnal berhasil disimpan.</div><?php endif; ?>

<?php if ($viewId && $txDetail): ?>
<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-journal"></i> Detail Jurnal #<?= $txDetail['id'] ?></div>
    <a href="journal-entries.php" class="btn btn-outline btn-sm">Kembali</a>
  </div>
  <div class="section-body">
    <div class="row g-3 mb-3">
      <div class="col-md-4"><strong>Referensi:</strong> <?= htmlspecialchars($txDetail['reference'] ?? '-') ?></div>
      <div class="col-md-4"><strong>Tanggal:</strong> <?= date('d M Y', strtotime($txDetail['transaction_date'])) ?></div>
      <div class="col-md-4"><strong>Tipe:</strong> <?= ucfirst($txDetail['type']) ?></div>
    </div>
    <p><strong>Keterangan:</strong> <?= nl2br(htmlspecialchars($txDetail['description'] ?? '-')) ?></p>
    <div class="table-responsive mt-3">
      <table class="data-table">
        <thead>
          <tr><th>KODE</th><th>AKUN</th><th class="text-right" style="width:16%">DEBIT</th><th class="text-right" style="width:16%">KREDIT</th><th style="width:20%">KETERANGAN BARIS</th></tr>
        </thead>
        <tbody>
        <?php $totD = 0; $totK = 0; foreach ($txLines as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['code']) ?></td>
            <td><?= htmlspecialchars($l['name']) ?></td>
            <td class="text-right amount-col"><?= number_format($l['debit'], 0, ',', '.') ?></td>
            <td class="text-right amount-col"><?= number_format($l['credit'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($l['description']) ?></td>
          </tr>
          <?php $totD += $l['debit']; $totK += $l['credit']; endforeach; ?>
          <tr class="fw-bold" style="border-top:2px solid var(--border);">
            <td colspan="2">Total</td>
            <td class="text-right amount-col"><?= number_format($totD, 0, ',', '.') ?></td>
            <td class="text-right amount-col"><?= number_format($totK, 0, ',', '.') ?></td>
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
    <div><i class="bi bi-journal-text"></i> Daftar Jurnal</div>
  </div>
  <div class="section-body p-0">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:8%">NO</th>
            <th style="width:12%">TANGGAL</th>
            <th style="width:15%">REFERENSI</th>
            <th style="width:13%">TIPE</th>
            <th>KETERANGAN</th>
            <th class="text-right" style="width:15%">DEBIT</th>
            <th class="text-right" style="width:15%">KREDIT</th>
            <th class="text-right" style="width:10%"></th>
          </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $pdo->prepare("
          SELECT t.id, t.reference, t.description, t.transaction_date, t.type,
                 COALESCE(SUM(l.debit),0) AS total_debit,
                 COALESCE(SUM(l.credit),0) AS total_credit
          FROM transactions t
          LEFT JOIN transaction_lines l ON l.transaction_id = t.id
          WHERE t.business_id = ?
          GROUP BY t.id
          ORDER BY t.transaction_date DESC, t.id DESC
          LIMIT 100
        ");
        $stmt->execute([$businessId]);
        $rows = $stmt->fetchAll();
        if ($rows): $no = 1; foreach ($rows as $r): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d M Y', strtotime($r['transaction_date'])) ?></td>
            <td><?= htmlspecialchars($r['reference'] ?? '-') ?></td>
            <td><span class="badge" style="background:#e9ecef;color:#495057;"><?= ucfirst($r['type']) ?></span></td>
            <td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
            <td class="text-right amount-col text-debit"><?= number_format($r['total_debit'], 0, ',', '.') ?></td>
            <td class="text-right amount-col text-credit"><?= number_format($r['total_credit'], 0, ',', '.') ?></td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm" href="journal-entries.php?view=<?= $r['id'] ?>"><i class="bi bi-eye"></i></a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted py-3">Belum ada jurnal.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Tambah Jurnal -->
<div class="modal-overlay" id="modalAddTx">
  <div class="modal" style="max-width:800px;">
    <form method="POST" action="" onsubmit="return validateTx(this)">
      <input type="hidden" name="action" value="add_tx">
      <div class="modal-header">
        <div class="modal-title">Buat Jurnal Baru</div>
        <button class="modal-close" onclick="closeModal('modalAddTx')">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Referensi</label>
            <input type="text" name="reference" class="form-control" placeholder="JE-0001">
          </div>
          <div class="col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipe</label>
            <select name="type" class="form-select">
              <option value="journal">Jurnal Umum</option>
              <option value="receipt">Penerimaan</option>
              <option value="payment">Pembayaran</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Keterangan</label>
            <input type="text" name="description" class="form-control" placeholder="Keterangan">
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table" id="txLines">
            <thead>
              <tr>
                <th style="width:26%">AKUN</th>
                <th class="text-right" style="width:16%">DEBIT</th>
                <th class="text-right" style="width:16%">KREDIT</th>
                <th style="width:26%">KETERANGAN BARIS</th>
                <th class="text-right" style="width:10%"></th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < 2; $i++): ?>
                <tr>
                  <td>
                    <select name="line_account_id[]" class="form-select form-select-sm" required>
                      <option value="">Pilih akun</option>
                      <?php foreach ($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td class="text-right"><input type="number" name="line_debit[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                  <td class="text-right"><input type="number" name="line_credit[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                  <td><input type="text" name="line_desc[]" class="form-control form-control-sm" placeholder="Keterangan"></td>
                  <td class="text-right"><button type="button" class="btn btn-outline btn-sm rem"><i class="bi bi-trash"></i></button></td>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <div class="text-end mt-2">
          <button type="button" class="btn btn-outline btn-sm" id="addLine">+ Tambah Baris</button>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal('modalAddTx')">Batal</button>
        <button class="btn btn-primary">Simpan Jurnal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); }); });
document.getElementById('addLine')?.addEventListener('click', function () {
  const tbody = document.querySelector('#txLines tbody');
  const row = tbody.querySelector('tr').cloneNode(true);
  row.querySelectorAll('input,select').forEach(el => el.value = '');
  tbody.appendChild(row);
});
document.querySelectorAll('.rem').forEach(btn => {
  btn.addEventListener('click', function () {
    const tbody = document.querySelector('#txLines tbody');
    if (tbody.children.length > 1) this.closest('tr').remove();
  });
});
function validateTx(form) {
  let d = 0, k = 0;
  form.querySelectorAll('input[name="line_debit[]"]').forEach(el => d += parseFloat(el.value) || 0);
  form.querySelectorAll('input[name="line_credit[]"]').forEach(el => k += parseFloat(el.value) || 0);
  if (Math.abs(d - k) > 0.005) {
    alert('Total debit dan kredit harus sama!');
    return false;
  }
  return true;
}
</script>

<?php require_once 'includes/footer.php'; ?>