<?php
// reports.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$report = $_GET['report'] ?? 'pl';
$from = $_GET['from'] ?? date('Y-01-01');
$to   = $_GET['to'] ?? date('Y-m-d');

function getBalances($pdo, $businessId, $from, $to) {
    $stmt = $pdo->prepare("
      SELECT a.type,
             COALESCE(SUM(
               CASE 
                 WHEN a.type IN ('asset','bank','expense') THEN 
                   CASE WHEN t.transaction_date BETWEEN ? AND ? THEN l.debit ELSE 0 END
                 WHEN a.type IN ('liability','equity','revenue') THEN 
                   CASE WHEN t.transaction_date BETWEEN ? AND ? THEN l.credit ELSE 0 END
                 ELSE 0
               END
             ),0) AS balance
      FROM accounts a
      LEFT JOIN transaction_lines l ON l.account_id = a.id
      LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
      WHERE a.business_id = ? AND a.is_active = 1
      GROUP BY a.type
    ");
    $stmt->execute([$from, $to, $from, $to, $businessId, $businessId]);
    $r = [];
    while ($row = $stmt->fetch()) $r[$row['type']] = (float) $row['balance'];
    return $r;
}

$bal = getBalances($pdo, $businessId, $from, $to);

$revenue = $bal['revenue'] ?? 0;
$expense = $bal['expense'] ?? 0;
$profit = $revenue - $expense;

$asset = $bal['asset'] ?? 0;
$bank  = $bal['bank'] ?? 0;
$liability = $bal['liability'] ?? 0;
$equity = $bal['equity'] ?? 0;

function getAccountsByType($pdo, $businessId, $type, $from, $to) {
    $stmt = $pdo->prepare("
      SELECT a.code, a.name,
             COALESCE(SUM(
               CASE 
                 WHEN a.type IN ('asset','bank','expense') THEN 
                   CASE WHEN t.transaction_date BETWEEN ? AND ? THEN l.debit ELSE 0 END
                 WHEN a.type IN ('liability','equity','revenue') THEN 
                   CASE WHEN t.transaction_date BETWEEN ? AND ? THEN l.credit ELSE 0 END
                 ELSE 0
               END
             ),0) AS balance
      FROM accounts a
      LEFT JOIN transaction_lines l ON l.account_id = a.id
      LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
      WHERE a.business_id = ? AND a.type = ? AND a.is_active = 1
      GROUP BY a.id
      ORDER BY a.code ASC
    ");
    $stmt->execute([$from, $to, $from, $to, $businessId, $businessId, $type]);
    return $stmt->fetchAll();
}
$assetAccounts = getAccountsByType($pdo, $businessId, 'asset', $from, $to);
$bankAccounts  = getAccountsByType($pdo, $businessId, 'bank', $from, $to);
$liabilityAccounts = getAccountsByType($pdo, $businessId, 'liability', $from, $to);
$equityAccounts = getAccountsByType($pdo, $businessId, 'equity', $from, $to);
$revenueAccounts = getAccountsByType($pdo, $businessId, 'revenue', $from, $to);
$expenseAccounts = getAccountsByType($pdo, $businessId, 'expense', $from, $to);
?>
<script>
document.getElementById('pageTitle').textContent = 'Laporan Keuangan';
document.getElementById('pageSubtitle').textContent = 'Laba Rugi, Neraca, dan Neraca Saldo';
document.getElementById('pageActions').innerHTML = `
  <form method="GET" class="d-flex align-items-center gap-2" style="margin-bottom:0;">
    <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
    <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
    <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Tampilkan</button>
  </form>
`;
</script>

<div class="section">
  <div class="section-body">
    <form method="GET" class="row g-2 align-items-end">
      <input type="hidden" name="report" value="<?= $report ?>">
      <div class="col-md-2">
        <label class="form-label form-sm">Dari</label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label form-sm">Sampai</label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label form-sm">&nbsp;</label>
        <button class="btn btn-primary w-100">Tampilkan</button>
      </div>
      <div class="col-md-6 d-flex align-items-end gap-2">
        <a href="reports.php?report=pl&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-outline <?= $report=='pl'?'btn-primary':'btn-outline-secondary' ?> flex-fill">Laba Rugi</a>
        <a href="reports.php?report=bs&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-outline <?= $report=='bs'?'btn-primary':'btn-outline-secondary' ?> flex-fill">Neraca</a>
        <a href="reports.php?report=tb&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-outline <?= $report=='tb'?'btn-primary':'btn-outline-secondary' ?> flex-fill">Neraca Saldo</a>
      </div>
    </form>
  </div>
</div>

<?php if ($report === 'pl'): ?>
<div class="section">
  <div class="section-header"><i class="bi bi-graph-up"></i> Laporan Laba Rugi (<?= date('d M Y', strtotime($from)) ?> s/d <?= date('d M Y', strtotime($to)) ?>)</div>
  <div class="section-body">
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>PENDAPATAN</th><th class="text-right">JUMLAH</th></tr></thead>
        <tbody>
        <?php $revTotal = 0; foreach ($revenueAccounts as $a): $revTotal += $a['balance']; ?>
          <tr><td><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></td><td class="text-right amount-col"><?= number_format($a['balance'], 0, ',', '.') ?></td></tr>
        <?php endforeach; ?>
          <tr class="fw-bold border-top"><td>Total Pendapatan</td><td class="text-right amount-col"><?= number_format($revTotal, 0, ',', '.') ?></td></tr>
        </tbody>
      </table>
    </div>
    <div class="table-responsive mt-3">
      <table class="data-table">
        <thead><tr><th>BEBAN</th><th class="text-right">JUMLAH</th></tr></thead>
        <tbody>
        <?php $expTotal = 0; foreach ($expenseAccounts as $a): $expTotal += $a['balance']; ?>
          <tr><td><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></td><td class="text-right amount-col"><?= number_format($a['balance'], 0, ',', '.') ?></td></tr>
        <?php endforeach; ?>
          <tr class="fw-bold border-top"><td>Total Beban</td><td class="text-right amount-col"><?= number_format($expTotal, 0, ',', '.') ?></td></tr>
        </tbody>
      </table>
    </div>
    <div class="section mt-3">
      <div class="section-body text-center">
        <h3 class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?> fw-bold">
          Laba / (Rugi) Bersih: <?= number_format($profit, 0, ',', '.') ?>
        </h3>
      </div>
    </div>
  </div>
</div>

<?php elseif ($report === 'bs'): ?>
<!-- Hitung total untuk neraca -->
<?php
$assetTotal = 0;
$allAssets = array_merge($assetAccounts, $bankAccounts);
foreach ($allAssets as $a) { $assetTotal += $a['balance']; }
$libTotal = 0; foreach ($liabilityAccounts as $a) { $libTotal += $a['balance']; }
$eqTotal = 0; foreach ($equityAccounts as $a) { $eqTotal += $a['balance']; }
?>
<div class="section">
  <div class="section-header"><i class="bi bi-bank"></i> Neraca (<?= date('d M Y', strtotime($to)) ?>)</div>
  <div class="section-body">
    <div class="row">
      <div class="col-md-6">
        <h6>Aset</h6>
        <div class="table-responsive">
          <table class="data-table">
            <tbody>
            <?php foreach ($allAssets as $a): ?>
                <tr><td><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></td><td class="text-right amount-col"><?= number_format($a['balance'], 0, ',', '.') ?></td></tr>
              <?php endforeach; ?>
              <tr class="fw-bold border-top"><td>Total Aset</td><td class="text-right amount-col"><?= number_format($assetTotal, 0, ',', '.') ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-md-6">
        <h6>Kewajiban &amp; Ekuitas</h6>
        <div class="table-responsive">
          <table class="data-table">
            <tbody>
            <?php foreach ($liabilityAccounts as $a): ?>
              <tr><td><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></td><td class="text-right amount-col"><?= number_format($a['balance'], 0, ',', '.') ?></td></tr>
            <?php endforeach; ?>
            <?php foreach ($equityAccounts as $a): ?>
              <tr><td><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></td><td class="text-right amount-col"><?= number_format($a['balance'], 0, ',', '.') ?></td></tr>
            <?php endforeach; ?>
              <tr class="fw-bold border-top"><td>Total Kewajiban &amp; Ekuitas</td><td class="text-right amount-col"><?= number_format($libTotal + $eqTotal, 0, ',', '.') ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="section mt-3">
      <div class="section-body text-center">
        <h5 class="fw-bold">Selisih: <?= number_format($assetTotal - ($libTotal + $eqTotal), 0, ',', '.') ?></h5>
        <p class="text-muted small">Ideally 0</p>
      </div>
    </div>
  </div>
</div>

<?php elseif ($report === 'tb'): ?>
<div class="section">
  <div class="section-header"><i class="bi bi-journal-text"></i> Neraca Saldo (<?= date('d M Y', strtotime($from)) ?> s/d <?= date('d M Y', strtotime($to)) ?>)</div>
  <div class="section-body">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr><th>KODE</th><th>AKUN</th><th class="text-right" style="width:16%">DEBIT</th><th class="text-right" style="width:16%">KREDIT</th></tr>
        </thead>
        <tbody>
        <?php
        $grandD = 0; $grandK = 0;
        $allTypes = ['asset','bank','liability','equity','revenue','expense'];
        foreach ($allTypes as $type) {
          $stmt = $pdo->prepare("
            SELECT a.code, a.name,
                   COALESCE(SUM(CASE WHEN t.transaction_date BETWEEN ? AND ? THEN l.debit ELSE 0 END),0) AS debit,
                   COALESCE(SUM(CASE WHEN t.transaction_date BETWEEN ? AND ? THEN l.credit ELSE 0 END),0) AS kredit
            FROM accounts a
            LEFT JOIN transaction_lines l ON l.account_id = a.id
            LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
            WHERE a.business_id = ? AND a.type = ? AND a.is_active = 1
            GROUP BY a.id ORDER BY a.code ASC
          ");
          $stmt->execute([$from, $to, $from, $to, $businessId, $businessId, $type]);
          while ($a = $stmt->fetch()) {
            if ($a['debit'] == 0 && $a['kredit'] == 0) continue;
            $grandD += $a['debit']; $grandK += $a['kredit'];
        ?>
          <tr>
            <td><?= htmlspecialchars($a['code']) ?></td>
            <td><?= htmlspecialchars($a['name']) ?></td>
            <td class="text-right amount-col"><?= number_format($a['debit'], 0, ',', '.') ?></td>
            <td class="text-right amount-col"><?= number_format($a['kredit'], 0, ',', '.') ?></td>
          </tr>
        <?php } ?>
        <?php } ?>
          <tr class="fw-bold border-top">
            <td colspan="2">Total</td>
            <td class="text-right amount-col"><?= number_format($grandD, 0, ',', '.') ?></td>
            <td class="text-right amount-col"><?= number_format($grandK, 0, ',', '.') ?></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="alert <?= $grandD == $grandK ? 'alert-success' : 'alert-danger' ?> mt-3">
      Selisih debit dan kredit: <?= number_format(abs($grandD - $grandK), 0, ',', '.') ?> (<?= $grandD == $grandK ? 'Seimbang' : 'Tidak seimbang' ?>)
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>