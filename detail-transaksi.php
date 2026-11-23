<?php
// detail-transaksi.php
// Halaman detail baris transaksi untuk satu akun tertentu.
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$accountId = filter_input(INPUT_GET, 'account_id', FILTER_VALIDATE_INT);
if (!$accountId) {
    header('Location: dashboard.php');
    exit;
}

// Pastikan akun milik bisnis aktif
$stmt = $pdo->prepare("SELECT id, code, name, type FROM accounts WHERE id = ? AND business_id = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$accountId, $businessId]);
$account = $stmt->fetch();
if (!$account) {
    header('Location: dashboard.php');
    exit;
}

// Ambil baris transaksi akun ini (urutan kronologis untuk perhitungan running balance)
// Akun 120 - Bank BCA memakai urutan baris (line id) agar saldo berjalan persis
// sesuai data yang diimpor. Akun lain tetap memakai urutan id transaksi seperti sebelumnya.
$orderBy = ($account['code'] === '120')
    ? 't.transaction_date ASC, l.id ASC'
    : 't.transaction_date ASC, t.id ASC';
// Akun 120 juga memakai deskripsi baris (bukan deskripsi header transaksi)
// supaya keterangan yang tampil persis seperti data yang diimpor.
$descriptionExpr = ($account['code'] === '120')
    ? "COALESCE(NULLIF(l.description, ''), t.description)"
    : "t.description";
$stmt = $pdo->prepare("
  SELECT t.id, t.transaction_date, $descriptionExpr AS description, t.reference,
         l.debit, l.credit,
         (l.debit - l.credit) AS amount
  FROM transaction_lines l
  JOIN transactions t ON t.id = l.transaction_id
  WHERE l.account_id = ? AND t.business_id = ?
  ORDER BY $orderBy
");
$stmt->execute([$accountId, $businessId]);
$lines = $stmt->fetchAll();

// Hitung running balance per baris (asset: debit naik, credit turun)
$runningBalance = 0;
foreach ($lines as &$l) {
    $runningBalance += ($l['debit'] - $l['credit']);
    $l['running_balance'] = $runningBalance;
}
unset($l);

// Balik urutan agar transaksi terbaru ditampilkan di paling atas
$lines = array_reverse($lines);

$totalDebit = array_reduce($lines, fn($c, $l) => $c + $l['debit'], 0);
$totalCredit = array_reduce($lines, fn($c, $l) => $c + $l['credit'], 0);
?>

<script>
document.getElementById('pageTitle').textContent = 'Transaksi Akun';
document.getElementById('pageSubtitle').textContent = 'Detail transaksi: <?= htmlspecialchars($account['code'] . ' - ' . $account['name']) ?>';
document.getElementById('pageActions').innerHTML = `
  <a href="detail-rekening.php?type=<?= urlencode($account['type']) ?>" class="btn btn-outline"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
`;
</script>

<div class="section">
  <div class="section-header">
    <i class="bi bi-currency-exchange"></i> <?= htmlspecialchars($account['code']) ?> – <?= htmlspecialchars($account['name']) ?>
    <span style="font-weight:400; font-size:0.85rem; color:var(--muted); margin-left:0.75rem;">Tipe: <?= ucfirst($account['type']) ?></span>
  </div>
  <div class="section-body p-0">
    <?php if (empty($lines)): ?>
      <div class="alert alert-info">Belum ada transaksi pada akun ini.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:14%">TANGGAL</th>
              <th style="width:12%">NO. TRANSAKSI</th>
              <th>KETERANGAN</th>
              <th style="width:16%">REFERENSI</th>
              <th class="text-right" style="width:16%">DEBIT</th>
              <th class="text-right" style="width:16%">KREDIT</th>
              <th class="text-right" style="width:16%">BALANCE</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($lines as $l): ?>
            <tr>
              <td><?= htmlspecialchars($l['transaction_date']) ?></td>
              <td><a href="journal-entries.php#trx-<?= (int)$l['id'] ?>" class="text-decoration-none"><?= '#' . str_pad((int)$l['id'], 6, '0', STR_PAD_LEFT) ?></a></td>
              <td><?= htmlspecialchars($l['description'] ?? '-') ?></td>
              <td><?= htmlspecialchars($l['reference'] ?? '-') ?></td>
              <td class="text-right amount-col"><?= $l['debit'] > 0 ? number_format($l['debit'], 0, ',', '.') : '&ndash;' ?></td>
              <td class="text-right amount-col"><?= $l['credit'] > 0 ? number_format($l['credit'], 0, ',', '.') : '&ndash;' ?></td>
              <td class="text-right amount-col"><?= number_format($l['running_balance'], 2, ',', '.') . ($l['running_balance'] > 0 ? ' Dr' : ($l['running_balance'] < 0 ? ' Cr' : '')) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="3" class="text-end"><strong>Total</strong></td>
            <td class="text-right amount-col"><strong><?= number_format($totalDebit, 0, ',', '.') ?></strong></td>
            <td class="text-right amount-col"><strong><?= number_format($totalCredit, 0, ',', '.') ?></strong></td>
            <td class="text-right amount-col"><strong><?= number_format($totalDebit - $totalCredit, 2, ',', '.') . (($totalDebit - $totalCredit) > 0 ? ' Dr' : (($totalDebit - $totalCredit) < 0 ? ' Cr' : '')) ?></strong></td>
          </tr>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
