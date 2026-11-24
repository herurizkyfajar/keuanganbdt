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

// Helper format Rupiah (sesuai referensi: "Rp 19.592.202,00")
function rp($n): string {
    return 'Rp ' . number_format((float)$n, 2, ',', '.');
}

// Helper untuk render daftar item dalam kartu (saldo bisa diklik ke detail transaksi per akun)
function renderItems(array $items): string {
    $html = '';
    if (empty($items)) {
        $html .= '<div class="dash-row" style="color:#94a3b8;">(kosong)</div>';
    } else {
        foreach ($items as $it) {
            $accountId = (int)($it['id'] ?? 0);
            $hasBalance = $it['balance'] != 0;
            $linkClass = $hasBalance ? 'clickable-balance' : '';
            $html .= '
            <div class="dash-row">
              <span>' . htmlspecialchars(($it['code'] ?? '') ? $it['code'] . ' - ' : '') . htmlspecialchars($it['name']) . '</span>
              <a href="detail-transaksi.php?account_id=' . $accountId . '" class="' . $linkClass . '">
                ' . rp($it['balance']) . '
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
  <div style="display:flex; align-items:center; gap:0.75rem;">
    <div style="display:flex; align-items:center; gap:0.55rem; background:#fff; border:1px solid #e5e7eb; border-radius:0.65rem; padding:0.55rem 0.95rem; font-size:0.85rem; color:#334155; font-weight:600; box-shadow:0 1px 2px rgba(15,23,42,0.04);">
      <i class="bi bi-calendar2-event" style="font-size:0.95rem; color:#64748b;"></i>
      <span>Periode: November 2026</span>
      <i class="bi bi-chevron-down" style="font-size:0.7rem; color:#94a3b8;"></i>
    </div>
    <a href="sales-invoices.php" class="btn btn-primary" style="background:#2563eb; border-color:#2563eb; border-radius:0.65rem; padding:0.6rem 1.15rem;"><i class="bi bi-plus-lg"></i> Faktur Baru</a>
  </div>
`;
</script>

<style>
/* Konten putih seperti referensi */
.main { background: #ffffff; }

/* Judul halaman lebih besar seperti referensi */
.page-title-box h1 { font-size: 1.9rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
.page-title-box p { font-size: 0.9rem; color: #64748b; }

.dash-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

/* Panel putih dengan judul ikon (Balance Sheet / Profit and Loss) */
.dash-panel {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(15,23,42,0.05);
}
.dash-panel-title {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 1.05rem 1.25rem;
  font-size: 1.1rem; font-weight: 800; color: #0f172a;
  letter-spacing: -0.01em;
}
.dash-panel-title i { color: #2563eb; font-size: 1.15rem; }

/* Group header berwarna (Assets biru, Liabilities hijau, dst.) */
.dash-group {
  display: flex; align-items: center; gap: 0.65rem;
  margin: 0.5rem 0.75rem;
  padding: 0.6rem 0.9rem;
  border-radius: 0.6rem;
  font-weight: 700; font-size: 0.92rem;
}
.dash-group .grp-icon {
  width: 27px; height: 27px; border-radius: 0.45rem;
  background: #ffffff; display: inline-flex; align-items: center; justify-content: center;
  font-size: 0.95rem; flex-shrink: 0;
  box-shadow: 0 1px 2px rgba(15,23,42,0.08);
}
.dash-group .grp-name { font-weight: 700; }
.dash-group .grp-total {
  margin-left: auto; font-weight: 800; font-variant-numeric: tabular-nums;
  color: inherit; text-decoration: none;
}
.dash-group .grp-total:hover { text-decoration: underline; text-underline-offset: 2px; }
.grp-blue { background: #e8f1fe; color: #2563eb; }
.grp-green { background: #e7f8ea; color: #16a34a; }

/* Baris akun */
.dash-rows { padding: 0.15rem 0.35rem 0.6rem; }
.dash-row {
  display: flex; justify-content: space-between; align-items: center; gap: 1rem;
  padding: 0.5rem 0.9rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.86rem; color: #334155;
}
.dash-row:last-child { border-bottom: none; }
.dash-row a {
  color: #2563eb; font-variant-numeric: tabular-nums; white-space: nowrap;
  text-decoration: underline; text-decoration-color: rgba(37,99,235,0.35);
  text-underline-offset: 2px;
}
.dash-row a:hover { text-decoration-color: currentColor; }
.clickable-balance:hover { text-decoration-color: currentColor; opacity: 0.9; }
.text-pos { color: #16a34a; }
.text-neg { color: #ef4444; }

@media (max-width: 1200px) {
  .stat-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .stat-grid { grid-template-columns: 1fr; }
  .page-title-box h1 { font-size: 1.5rem; }
}
</style>

<!-- Stat Cards (sesuai referensi) -->
<div class="stat-grid">
  <div class="stat-card">
    <span class="stat-icon bg-info"><i class="bi bi-layers"></i></span>
    <div class="stat-main">
      <div class="stat-label">Total Aset</div>
      <div class="stat-value"><?= rp($assetTotal) ?></div>
    </div>
    <div class="stat-meta">
      <span class="stat-change pos">↑ +2,4%</span>
      <span class="stat-vs">vs bulan lalu</span>
    </div>
  </div>
  <div class="stat-card">
    <span class="stat-icon bg-danger"><i class="bi bi-scales"></i></span>
    <div class="stat-main">
      <div class="stat-label">Total Liabilitas</div>
      <div class="stat-value"><?= rp($liabilityTotal) ?></div>
    </div>
    <div class="stat-meta">
      <span class="stat-change neutral">0,0%</span>
      <span class="stat-vs">vs bulan lalu</span>
    </div>
  </div>
  <div class="stat-card">
    <span class="stat-icon bg-success"><i class="bi bi-bar-chart"></i></span>
    <div class="stat-main">
      <div class="stat-label">Total Equity</div>
      <div class="stat-value"><?= rp($equityTotal) ?></div>
    </div>
    <div class="stat-meta">
      <span class="stat-change pos">↑ +2,4%</span>
      <span class="stat-vs">vs bulan lalu</span>
    </div>
  </div>
  <div class="stat-card">
    <span class="stat-icon bg-primary"><i class="bi bi-coin"></i></span>
    <div class="stat-main">
      <div class="stat-label">Laba (Rugi) Bersih</div>
      <div class="stat-value"><?= rp($netProfit) ?></div>
    </div>
    <div class="stat-meta">
      <span class="stat-change pos">↑ +12,6%</span>
      <span class="stat-vs">vs bulan lalu</span>
    </div>
  </div>
</div>

<div class="dash-grid">
  <!-- KIRI: Balance Sheet -->
  <div class="dash-panel">
    <div class="dash-panel-title"><i class="bi bi-layers"></i> Balance Sheet</div>

    <div class="dash-group grp-blue">
      <span class="grp-icon"><i class="bi bi-box-seam"></i></span>
      <span class="grp-name">Assets</span>
      <a class="grp-total" href="detail-rekening.php?type=asset"><?= rp($assetTotal) ?></a>
    </div>
    <div class="dash-rows">
      <?= renderItems(array_merge($groups['asset'], $groups['bank'])) ?>
    </div>

    <div class="dash-group grp-green">
      <span class="grp-icon"><i class="bi bi-wallet2"></i></span>
      <span class="grp-name">Liabilities</span>
      <a class="grp-total" href="detail-rekening.php?type=liability"><?= rp($liabilityTotal) ?></a>
    </div>
    <div class="dash-rows">
      <?= renderItems($groups['liability']) ?>
    </div>

    <div class="dash-group grp-blue">
      <span class="grp-icon"><i class="bi bi-arrow-repeat"></i></span>
      <span class="grp-name">Equity</span>
      <a class="grp-total" href="detail-rekening.php?type=equity"><?= rp($equityTotal) ?></a>
    </div>
    <div class="dash-rows">
      <?= renderItems($groups['equity']) ?>
    </div>
  </div>

  <!-- KANAN: Profit and Loss Statement -->
  <div class="dash-panel">
    <div class="dash-panel-title"><i class="bi bi-bar-chart"></i> Profit and Loss Statement</div>

    <div class="dash-group grp-green">
      <span class="grp-icon"><i class="bi bi-wallet2"></i></span>
      <span class="grp-name">Pemasukkan</span>
      <a class="grp-total" href="detail-rekening.php?type=revenue"><?= rp($revenueTotal) ?></a>
    </div>
    <div class="dash-rows">
      <?= renderItems($groups['revenue']) ?>
    </div>

    <div class="dash-group grp-blue">
      <span class="grp-icon"><i class="bi bi-wallet2"></i></span>
      <span class="grp-name">Less Beban</span>
      <a class="grp-total" href="detail-rekening.php?type=expense"><?= rp($expenseTotal) ?></a>
    </div>
    <div class="dash-rows">
      <?= renderItems($groups['expense']) ?>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>