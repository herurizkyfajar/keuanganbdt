<?php
// dashboard.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

// Ambil semua akun aktif dengan saldo per tipo
$stmt = $pdo->prepare("
  SELECT a.id, a.code, a.name, a.type,
         COALESCE(SUM(
           CASE 
             WHEN a.type IN ('asset','bank','expense') THEN l.debit - l.credit
             WHEN a.type IN ('liability','equity','revenue') THEN l.credit - l.debit
             ELSE 0
           END
         ), 0) AS balance
  FROM accounts a
  LEFT JOIN transaction_lines l ON l.account_id = a.id
  LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
  WHERE a.business_id = ? AND a.is_active = 1
  GROUP BY a.id
  ORDER BY a.code ASC
");
$stmt->execute([$businessId, $businessId]);
$allAccounts = $stmt->fetchAll();

// Kelompokkan per tipe
$groups = [
    'asset'    => [],
    'bank'     => [],
    'liability'=> [],
    'equity'   => [],
    'revenue'  => [],
    'expense'  => [],
];
foreach ($allAccounts as $acc) {
    $t = $acc['type'];
    if (isset($groups[$t])) {
        $groups[$t][] = $acc;
    }
}

// Urutan kustom untuk kartu "Less Beban" (sesuai referensi yang diinginkan).
// Akun yang tidak ada dalam daftar ini akan diurutkan terakhir (fallback by code).
$expenseOrder = [
    'Beban Kendaraan',
    'Biaya Administrasi Bank',
    'Biaya BRC',
    'Biaya Gaji',
    'Biaya Hiburan',
    'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB',
    'Biaya Jasa Akuntansi',
    'Biaya Jasa Notaris',
    'Biaya Listrik',
    'Biaya Pembelian Aset',
    'Biaya Peralatan Komputer',
    'Biaya Perbaikan dan Pemeliharaan',
    'Biaya Percetakan dan Alat Tulis Kantor',
    'Biaya Perpanjangan Domain',
    'Biaya Perpanjangan Hosting',
    'Biaya Perpanjangan n8n',
    'Biaya Rapat',
    'Biaya Sewa Mobil dan Supir',
    'Donasi',
    'Tax',
];
$expensePos = array_flip($expenseOrder);
usort($groups['expense'], function ($a, $b) use ($expensePos) {
    $pa = $expensePos[$a['name']] ?? PHP_INT_MAX;
    $pb = $expensePos[$b['name']] ?? PHP_INT_MAX;
    if ($pa === $pb) {
        return ($a['code'] ?? 0) <=> ($b['code'] ?? 0);
    }
    return $pa <=> $pb;
});

// Hitung total per kategori
$assetTotal   = array_reduce($groups['asset'],   fn($c,$a)=>$c+$a['balance'], 0) +
                array_reduce($groups['bank'],    fn($c,$a)=>$c+$a['balance'], 0);
$liabilityTotal = array_reduce($groups['liability'], fn($c,$a)=>$c+$a['balance'], 0);
$equityTotal    = array_reduce($groups['equity'],   fn($c,$a)=>$c+$a['balance'], 0);
$revenueTotal   = array_reduce($groups['revenue'],  fn($c,$a)=>$c+$a['balance'], 0);
$expenseTotal   = array_reduce($groups['expense'],  fn($c,$a)=>$c+$a['balance'], 0);
$netProfit = $revenueTotal - $expenseTotal;

// Helper untuk render daftar item dalam kartu (saldo bisa diklik ke detail transaksi per akun)
function renderItems(array $items): string {
    $html = '';
    if (empty($items)) {
        $html .= '<div style="padding:0.75rem 1rem; color:#6c757d; font-size:0.85rem;">(kosong)</div>';
    } else {
        foreach ($items as $it) {
            $accountId = (int)($it['id'] ?? 0);
            $hasBalance = $it['balance'] != 0;
            $linkClass = $hasBalance ? 'clickable-balance' : '';
            $html .= '
            <div style="display:flex; justify-content:space-between; padding:0.5rem 1rem; border-bottom:1px solid #f1f3f5; font-size:0.85rem;">
              <span>' . htmlspecialchars(($it['code'] ?? '') ? $it['code'] . ' - ' : '') . htmlspecialchars($it['name']) . '</span>
              <a href="detail-transaksi.php?account_id=' . $accountId . '" class="' . $linkClass . '" style="color:#0d6efd; font-variant-numeric:tabular-nums; text-decoration:underline; text-decoration-color:rgba(13,110,253,0.35);">
                ' . number_format($it['balance'], 2, ',', '.') . '
              </a>
            </div>';
        }
    }
    return $html;
}
?>
<script>
document.getElementById('pageTitle').textContent = 'Dashboard';
document.getElementById('pageSubtitle').textContent = 'Ringkasan keuangan bisnis Anda';
document.getElementById('pageActions').innerHTML = `
  <a href="sales-invoices.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Faktur Baru</a>
`;
</script>

<style>
.dash-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.dash-section {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6c757d;
  text-align: center;
  margin-bottom: 0.5rem;
  font-weight: 600;
}
.dash-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
}
.dash-header {
  background: #fdf7e3;
  border-bottom: 1px solid #f1e3c3;
  padding: 0.85rem 1.1rem;
  display: flex;
  justify-content: space-between;
  align-items: baseline;
}
.dash-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #333;
  letter-spacing: -0.2px;
}
.dash-total {
  font-size: 1.05rem;
  font-weight: 700;
  color: #333;
  font-variant-numeric: tabular-nums;
}
.dash-body {
  padding: 0;
  flex: 1;
  min-height: 140px;
}
.dash-total-row {
  background: #fdf7e3;
  border-top: 1px solid #f1e3c3;
  padding: 0.6rem 1.1rem;
  font-weight: 700;
  font-size: 0.9rem;
  color: #333;
  font-variant-numeric: tabular-nums;
}
.text-pos { color: #0d6efd; }
.text-neg { color: #dc3545; }
.dash-total-link {
  color: inherit;
  text-decoration: underline;
  text-decoration-color: rgba(0,0,0,0.35);
  text-underline-offset: 2px;
  cursor: pointer;
  transition: text-decoration-color 0.2s;
}
.dash-total-link:hover {
  text-decoration-color: currentColor;
  opacity: 0.9;
}
.clickable-balance:hover {
  text-decoration-color: currentColor;
  opacity: 0.9;
}
</style>

<div class="dash-grid">
  <!-- KIRI: Balance Sheet column -->
  <div style="display:flex; flex-direction:column; gap:1rem;">
    <div class="dash-section">Balance Sheet</div>
    <div class="dash-card">
      <div class="dash-header">
        <h3>Assets</h3>
        <a class="dash-total-link" href="detail-rekening.php?type=asset"><?= number_format($assetTotal, 2, ',', '.') ?></a>
      </div>
      <div class="dash-body">
        <?= renderItems(array_merge($groups['asset'], $groups['bank'])) ?>
      </div>
    </div>
    <div class="dash-card">
      <div class="dash-header">
        <h3>Liabilities</h3>
        <a class="dash-total-link" href="detail-rekening.php?type=liability"><?= number_format($liabilityTotal, 2, ',', '.') ?></a>
      </div>
      <div class="dash-body">
        <?= renderItems($groups['liability']) ?>
      </div>
    </div>
    <div class="dash-card">
      <div class="dash-header">
        <h3>Equity</h3>
        <a class="dash-total-link" href="detail-rekening.php?type=equity"><?= number_format($equityTotal, 2, ',', '.') ?></a>
      </div>
      <div class="dash-body">
        <?= renderItems($groups['equity']) ?>
      </div>
    </div>
  </div>

  <!-- KANAN: Profit and Loss Statement column -->
  <div style="display:flex; flex-direction:column; gap:1rem;">
    <div class="dash-section">Profit and Loss Statement</div>
    <div class="dash-card">
      <div class="dash-header">
        <h3>Pemasukkan</h3>
        <a class="dash-total-link text-pos" href="detail-rekening.php?type=revenue"><?= number_format($revenueTotal, 2, ',', '.') ?></a>
      </div>
      <div class="dash-body">
        <?= renderItems($groups['revenue']) ?>
      </div>
    </div>
    <div class="dash-card">
      <div class="dash-header">
        <h3>Less Beban</h3>
        <a class="dash-total-link text-neg" href="detail-rekening.php?type=expense"><?= number_format($expenseTotal, 2, ',', '.') ?></a>
      </div>
      <div class="dash-body">
        <?= renderItems($groups['expense']) ?>
      </div>
    </div>
    <div class="dash-card">
      <div class="dash-header">
        <h3>Net profit (loss)</h3>
        <span class="dash-total <?= $netProfit >= 0 ? 'text-pos' : 'text-neg' ?>"><?= number_format($netProfit, 2, ',', '.') ?></span>
      </div>
      <div class="dash-body">
        <div style="padding:1rem; text-align:center; color:#6c757d; font-size:0.85rem;">(detail laba rugi)</div>
      </div>
      <div class="dash-total-row">
        Total: <span><?= number_format($netProfit, 2, ',', '.') ?></span>
      </div>
    </div>
    <div class="dash-card">
      <div class="dash-header">
        <h3>Uncategorized</h3>
        <span class="dash-total">0,00</span>
      </div>
      <div class="dash-body">
        <div style="padding:1rem; text-align:center; color:#6c757d; font-size:0.85rem;">(tidak dikategorikan)</div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>