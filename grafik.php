<?php
// grafik.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$from = $_GET['from'] ?? date('Y-01-01');
$to   = $_GET['to'] ?? date('Y-m-d');

if (!$from) $from = date('Y-01-01');
if (!$to)   $to = date('Y-m-d');

// Ambil data bulanan (1-12) untuk tipe akun tertentu.
// revenue -> jumlah kredit, expense -> jumlah debit.
function getMonthlyData($pdo, $businessId, $type, $from, $to) {
    $data = [];
    for ($m = 1; $m <= 12; $m++) {
        $data[$m] = 0.0;
    }
    $stmt = $pdo->prepare("
      SELECT MONTH(t.transaction_date) AS bulan,
             COALESCE(SUM(
               CASE 
                 WHEN ? = 'revenue' THEN l.credit
                 WHEN ? = 'expense' THEN l.debit
                 ELSE 0
               END
             ), 0) AS total
      FROM accounts a
      LEFT JOIN transaction_lines l ON l.account_id = a.id
      LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
      WHERE a.business_id = ? AND a.type = ? AND a.is_active = 1
        AND t.transaction_date BETWEEN ? AND ?
      GROUP BY MONTH(t.transaction_date)
    ");
    $stmt->execute([$type, $type, $businessId, $businessId, $type, $from, $to]);
    while ($row = $stmt->fetch()) {
        $data[(int) $row['bulan']] = (float) $row['total'];
    }
    return array_values($data);
}

$revenueMonthly = getMonthlyData($pdo, $businessId, 'revenue', $from, $to);
$expenseMonthly = getMonthlyData($pdo, $businessId, 'expense', $from, $to);

$revenueTotal = array_sum($revenueMonthly);
$expenseTotal = array_sum($expenseMonthly);
$profit       = $revenueTotal - $expenseTotal;

// Ambil 10 akun pengeluaran terbanyak (berdasarkan total debit) dalam rentang tanggal.
function getTopExpenseAccounts($pdo, $businessId, $from, $to, $limit = 10) {
    $stmt = $pdo->prepare("
      SELECT a.code, a.name,
             COALESCE(SUM(
               CASE WHEN t.transaction_date BETWEEN ? AND ? THEN l.debit ELSE 0 END
             ), 0) AS total
      FROM accounts a
      LEFT JOIN transaction_lines l ON l.account_id = a.id
      LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
      WHERE a.business_id = ? AND a.type = 'expense' AND a.is_active = 1
      GROUP BY a.id
      ORDER BY total DESC
      LIMIT ?
    ");
    $stmt->execute([$from, $to, $businessId, $businessId, $limit]);
    return $stmt->fetchAll();
}

$topExpenseAccounts = getTopExpenseAccounts($pdo, $businessId, $from, $to);

$monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
document.getElementById('pageTitle').textContent = 'Grafik Keuangan';
document.getElementById('pageSubtitle').textContent = 'Pendapatan dan Pengeluaran per Bulan';
document.getElementById('pageActions').innerHTML = `
  <form method="GET" class="d-flex align-items-center gap-2" style="margin-bottom:0;">
    <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
    <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
    <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Tampilkan</button>
    <a href="grafik.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
  </form>
`;
</script>

<style>
.chart-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  padding: 1rem 1.25rem 1.25rem;
}
.chart-title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #333;
  margin: 0 0 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.chart-wrap {
  position: relative;
  height: 260px;
  width: 100%;
}
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.stat-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 0.75rem;
  padding: 1rem 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.stat-label {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6c757d;
  font-weight: 600;
}
.stat-value {
  font-size: 1.35rem;
  font-weight: 700;
  color: #333;
  font-variant-numeric: tabular-nums;
}
.stat-pos { color: #198754; }
.stat-neg { color: #dc3545; }
.stat-neutral { color: #0d6efd; }
@media (max-width: 768px) {
  .chart-wrap { height: 220px; }
}
</style>

<div class="section">
  <div class="section-body">
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-label">Total Pendapatan</div>
        <div class="stat-value stat-pos"><?= number_format($revenueTotal, 0, ',', '.') ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value stat-neg"><?= number_format($expenseTotal, 0, ',', '.') ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Laba / (Rugi)</div>
        <div class="stat-value <?= $profit >= 0 ? 'stat-pos' : 'stat-neg' ?>">
          <?= number_format($profit, 0, ',', '.') ?>
        </div>
      </div>
    </div>

    <div class="row g-5">
      <div class="col-md-8">
        <div class="chart-card mb-4">
          <h3 class="chart-title"><i class="bi bi-bar-chart"></i> Grafik Pendapatan dan Pengeluaran per Bulan</h3>
          <div class="chart-wrap">
            <canvas id="chartFinance"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="chart-card mb-4">
          <h3 class="chart-title"><i class="bi bi-cash-stack"></i> Akun Pengeluaran Terbanyak</h3>
          <div class="chart-wrap" style="height: auto; min-height: 300px;">
            <canvas id="chartTopExpense"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const monthLabels = <?= json_encode($monthNames) ?>;
const revenueData = <?= json_encode($revenueMonthly) ?>;
const expenseData = <?= json_encode($expenseMonthly) ?>;

Chart.register(ChartDataLabels);

const ctx = document.getElementById('chartFinance').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: monthLabels,
    datasets: [
      {
        label: 'Pendapatan',
        data: revenueData,
        backgroundColor: 'rgba(25, 135, 84, 0.75)',
        borderColor: 'rgba(25, 135, 84, 1)',
        borderWidth: 1,
        borderRadius: 4,
        barPercentage: 0.8,
        categoryPercentage: 0.9
      },
      {
        label: 'Pengeluaran',
        data: expenseData,
        backgroundColor: 'rgba(220, 53, 69, 0.75)',
        borderColor: 'rgba(220, 53, 69, 1)',
        borderWidth: 1,
        borderRadius: 4,
        barPercentage: 0.8,
        categoryPercentage: 0.9
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 800 },
    plugins: {
      legend: { position: 'top' },
      tooltip: {
        callbacks: {
          label: function(cctx) {
            const v = cctx.parsed.y || 0;
            return cctx.dataset.label + ': Rp ' + v.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
          }
        }
      },
      datalabels: {
        display: true,
        color: '#333',
        font: { weight: 'bold', size: 11 },
        anchor: 'end',
        align: 'top',
        formatter: function(v) {
          return v ? 'Rp ' + v.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '';
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(v) {
            return 'Rp ' + v.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
          }
        },
        grid: { color: '#f1f3f5' },
        title: { display: true, text: 'Jumlah (Rp)' }
      },
      x: {
        grid: { display: false },
        title: { display: true, text: 'Bulan' }
      }
    }
  }
});
const topExpenseLabels = <?= json_encode(array_map(fn($a) => (($a['code'] ?? '') ? $a['code'] . ' - ' : '') . $a['name'], $topExpenseAccounts)) ?>;
const topExpenseData   = <?= json_encode(array_map(fn($a) => (float) $a['total'], $topExpenseAccounts)) ?>;

const ctx2 = document.getElementById('chartTopExpense').getContext('2d');
new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: topExpenseLabels,
    datasets: [{
      label: 'Pengeluaran (Rp)',
      data: topExpenseData,
      backgroundColor: 'rgba(220, 53, 69, 0.75)',
      borderColor: 'rgba(220, 53, 69, 1)',
      borderWidth: 1,
      borderRadius: 4,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 800 },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function(cctx) {
            const v = cctx.parsed.x || 0;
            return 'Rp ' + v.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
          }
        }
      },
      datalabels: {
        display: true,
        color: '#333',
        font: { weight: 'bold', size: 11 },
        anchor: 'end',
        align: 'end',
        formatter: function(v) {
          return v ? 'Rp ' + v.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) : '';
        }
      }
    },
    scales: {
      x: {
        beginAtZero: true,
        ticks: {
          callback: function(v) {
            return 'Rp ' + v.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
          }
        },
        grid: { color: '#f1f3f5' },
        title: { display: true, text: 'Jumlah (Rp)' }
      },
      y: {
        grid: { display: false },
        ticks: { font: { size: 11 } }
      }
    }
  }
});
</script>

<?php require_once 'includes/footer.php'; ?>
