<?php
// detail-rekening.php
// Halaman detail daftar akun per jenis, diakses dari kartu dashboard.
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

// Validasi dan normalisasi tipe yang dipilih
$allowedTypes = ['asset', 'bank', 'liability', 'equity', 'revenue', 'expense'];
$type = $_GET['type'] ?? '';
if (!in_array($type, $allowedTypes, true)) {
    $type = 'asset';
}

// Label tampilan per tipe
$typeLabels = [
    'asset'    => 'Aset',
    'bank'     => 'Bank',
    'liability'=> 'Kewajiban',
    'equity'   => 'Ekuitas',
    'revenue'  => 'Pemasukan',
    'expense'  => 'Beban',
];
$typeLabel = $typeLabels[$type] ?? $type;

// Untuk kartu "Aset" di dashboard gabungkan asset + bank
$queryTypes = ($type === 'asset') ? ['asset', 'bank'] : [$type];
$placeholders = implode(',', array_fill(0, count($queryTypes), '?'));
$typeParams = array_values($queryTypes);

$stmt = $pdo->prepare("
  SELECT a.id, a.code, a.name, a.type,
         COALESCE(SUM(
           CASE 
             WHEN a.type IN ('asset','bank','expense') THEN l.debit - l.credit
             WHEN a.type IN ('liability','equity','revenue') THEN l.credit - l.debit
             ELSE 0
           END
         ), 0) AS balance,
         COUNT(l.id) AS line_count
  FROM accounts a
  LEFT JOIN transaction_lines l ON l.account_id = a.id
  LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
  WHERE a.business_id = ? AND a.is_active = 1 AND a.type IN ($placeholders)
  GROUP BY a.id
  ORDER BY a.code ASC
");
$stmt->execute(array_merge([$businessId, $businessId], $typeParams));
$accounts = $stmt->fetchAll();

// Total saldo halaman ini
$pageTotal = array_reduce($accounts, fn($c, $a) => $c + $a['balance'], 0);
?>

<script>
document.getElementById('pageTitle').textContent = 'Detail <?= htmlspecialchars($typeLabel) ?>';
document.getElementById('pageSubtitle').textContent = 'Daftar rekening berjenis <?= htmlspecialchars($typeLabel) ?>';
document.getElementById('pageActions').innerHTML = `
  <a href="dashboard.php" class="btn btn-outline"><i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard</a>
`;
</script>

<?php if (empty($accounts)): ?>
  <div class="section">
    <div class="section-body">
      <div class="alert alert-info">Belum ada rekening aktif berjenis <?= htmlspecialchars($typeLabel) ?>.</div>
    </div>
  </div>
<?php else: ?>
  <div class="section">
    <div class="section-header"><i class="bi bi-journal-text"></i> <?= htmlspecialchars($typeLabel) ?> <span style="font-weight:400; font-size:0.85rem; color:var(--muted); margin-left:0.5rem;">Total: <?= number_format($pageTotal, 0, ',', '.') ?></span></div>
    <div class="section-body p-0">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:12%">KODE</th>
              <th>NAMA AKUN</th>
              <th style="width:16%">TIPE</th>
              <th class="text-right" style="width:18%">SALDO</th>
              <th class="text-right" style="width:14%">TRANSAKSI</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($accounts as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['code']) ?></td>
              <td>
                <strong><?= htmlspecialchars($a['name']) ?></strong>
              </td>
              <td>
                <span class="badge" style="background:<?= match($a['type']) {
                    'asset' => '#cfe2ff;color:#084298',
                    'liability' => '#ffe5d0;color:#994700',
                    'equity' => '#e2e3e5;color:#383d41',
                    'revenue' => '#d1e7dd;color:#0f5132',
                    'expense' => '#f8d7da;color:#842029',
                    'bank' => '#cff4fc;color:#055160',
                    default => '#f1f3f5;color:#495057'
                } ?>"><?= ucfirst($a['type']) ?></span>
              </td>
              <td class="text-right amount-col">
                <?php if ($a['type'] === 'asset' || $a['type'] === 'bank' || $a['type'] === 'expense'): ?>
                  <span class="text-debit"><?= number_format($a['balance'], 0, ',', '.') ?></span>
                <?php else: ?>
                  <span class="text-credit"><?= number_format($a['balance'], 0, ',', '.') ?></span>
                <?php endif; ?>
              </td>
              <td class="text-right">
                <?php if ((int)$a['line_count'] > 0): ?>
                  <a class="btn btn-outline btn-sm" href="detail-transaksi.php?account_id=<?= (int)$a['id'] ?>"><i class="bi bi-list"></i> <?= (int)$a['line_count'] ?></a>
                <?php else: ?>
                  <span class="text-muted" style="font-size:0.8rem;">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
