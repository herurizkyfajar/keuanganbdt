<?php
// transactions.php
require_once 'includes/header.php';

$businessId = $_SESSION['business_id'];

// Ambil daftar akun untuk dropdown
$stmt = $pdo->prepare("SELECT id, code, name FROM accounts WHERE business_id = ? AND is_active = 1 ORDER BY code ASC");
$stmt->execute([$businessId]);
$accounts = $stmt->fetchAll();

// Lihat detail transaksi tertentu
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

// Simpan transaksi baru
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_tx') {
    try {
        $pdo->beginTransaction();
        $ref       = $_POST['reference'] ?? '';
        $desc      = $_POST['description'] ?? '';
        $tgl       = $_POST['transaction_date'] ?? date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date) VALUES (?,?,?,?)");
        $stmt->execute([$businessId, $ref, $desc, $tgl]);
        $txId = $pdo->lastInsertId();

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($_POST['line_account_id'] ?? [] as $idx => $accId) {
            if (!$accId) continue;
            $debit  = (float) ($_POST['line_debit'][$idx] ?? 0);
            $credit = (float) ($_POST['line_credit'][$idx] ?? 0);
            $lDesc  = $_POST['line_desc'][$idx] ?? '';
            $totalDebit += $debit;
            $totalCredit += $credit;
            $stmt = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?,?,?,?,?)");
            $stmt->execute([$txId, $accId, $lDesc, $debit, $credit]);
        }

        if ($totalDebit !== $totalCredit) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">Total debit dan kredit harus sama!</div>';
        } else {
            $pdo->commit();
            header('Location: transactions.php?view=' . $txId . '&saved=1');
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger">Gagal menyimpan: ' . $e->getMessage() . '</div>';
    }
}

$saved = isset($_GET['saved']);
?>
<div class="row">
  <div class="col-12">
    <?php if ($saved): ?>
      <div class="alert alert-success alert-dismissible fade show">
        Transaksi berhasil disimpan. <button class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php echo $message; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Transaksi / Jurnal Umum</h5>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddTx">
        <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
      </button>
    </div>

    <?php if ($viewId && $txDetail): ?>
      <div class="card mb-4">
        <div class="card-header">
          Detail Transaksi #<?= $txDetail['id'] ?> &mdash; <?= htmlspecialchars($txDetail['reference']) ?>
        </div>
        <div class="card-body">
          <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($txDetail['transaction_date'])) ?></p>
          <p><strong>Keterangan:</strong> <?= htmlspecialchars($txDetail['description']) ?></p>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr><th>Kode</th><th>Akun</th><th class="text-end">Debit</th><th class="text-end">Kredit</th><th>Keterangan Baris</th></tr>
              </thead>
              <tbody>
              <?php $totD = 0; $totK = 0; foreach ($txLines as $l): ?>
                <tr>
                  <td><?= htmlspecialchars($l['code']) ?></td>
                  <td><?= htmlspecialchars($l['name']) ?></td>
                  <td class="text-end"><?= number_format($l['debit'], 0) ?></td>
                  <td class="text-end"><?= number_format($l['credit'], 0) ?></td>
                  <td><?= htmlspecialchars($l['description']) ?></td>
                </tr>
                <?php $totD += $l['debit']; $totK += $l['credit']; endforeach; ?>
                <tr class="fw-bold border-top">
                  <td colspan="2">Total</td>
                  <td class="text-end"><?= number_format($totD, 0) ?></td>
                  <td class="text-end"><?= number_format($totK, 0) ?></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
          <a class="btn btn-sm btn-outline-secondary" href="transactions.php">Kembali</a>
        </div>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th style="width:8%">No</th>
              <th style="width:12%">Tanggal</th>
              <th style="width:15%">Referensi</th>
              <th>Keterangan</th>
              <th class="text-end" style="width:15%">Debit</th>
              <th class="text-end" style="width:15%">Kredit</th>
              <th style="width:10%"></th>
            </tr>
          </thead>
          <tbody>
          <?php
          $stmt = $pdo->prepare("
            SELECT t.id, t.reference, t.description, t.transaction_date,
                   COALESCE(SUM(l.debit),0) AS total_debit,
                   COALESCE(SUM(l.credit),0) AS total_credit
            FROM transactions t
            LEFT JOIN transaction_lines l ON l.transaction_id = t.id
            WHERE t.business_id = ?
            GROUP BY t.id
            ORDER BY t.transaction_date DESC, t.id DESC
            LIMIT 50
          ");
          $stmt->execute([$businessId]);
          $rows = $stmt->fetchAll();
          if ($rows): $no = 1; foreach ($rows as $r): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= date('d M Y', strtotime($r['transaction_date'])) ?></td>
              <td><?= htmlspecialchars($r['reference'] ?? '-') ?></td>
              <td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
              <td class="text-end text-debit"><?= number_format($r['total_debit'], 0) ?></td>
              <td class="text-end text-credit"><?= number_format($r['total_credit'], 0) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="transactions.php?view=<?= $r['id'] ?>"><i class="bi bi-eye"></i></a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="7" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="modalAddTx" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="" onsubmit="return validateTx(this)">
      <input type="hidden" name="action" value="add_tx">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Buat Transaksi Baru</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Referensi</label>
              <input type="text" name="reference" class="form-control" placeholder="INV-0002">
            </div>
            <div class="col-md-4">
              <label class="form-label">Tanggal</label>
              <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Keterangan</label>
              <input type="text" name="description" class="form-control" placeholder="Keterangan transaksi">
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-sm" id="txLines">
              <thead>
                <tr>
                  <th style="width:22%">Akun</th>
                  <th style="width:18%" class="text-end">Debit</th>
                  <th style="width:18%" class="text-end">Kredit</th>
                  <th style="width:25%">Keterangan Baris</th>
                  <th style="width:10%"></th>
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
                  <td class="text-end"><input type="number" name="line_debit[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                  <td class="text-end"><input type="number" name="line_credit[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                  <td><input type="text" name="line_desc[]" class="form-control form-control-sm" placeholder="Keterangan"></td>
                  <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger rem">×</button></td>
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
                  <td class="text-end"><input type="number" name="line_debit[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                  <td class="text-end"><input type="number" name="line_credit[]" class="form-control form-control-sm" step="0.01" min="0" value="0"></td>
                  <td><input type="text" name="line_desc[]" class="form-control form-control-sm" placeholder="Keterangan"></td>
                  <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger rem">×</button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="text-end mt-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="addLine">+ Tambah Baris</button>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-primary">Simpan Transaksi</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
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