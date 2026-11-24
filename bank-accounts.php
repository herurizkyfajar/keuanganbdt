<?php
// bank-accounts.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$stmt = $pdo->prepare("
  SELECT b.*,
         COALESCE(SUM(CASE WHEN t.type='receipt' THEN l.debit ELSE 0 END),0) - 
         COALESCE(SUM(CASE WHEN t.type='payment' THEN l.credit ELSE 0 END),0) AS movement
  FROM bank_accounts b
  LEFT JOIN accounts a ON a.business_id = b.business_id AND a.name LIKE CONCAT('%', b.account_name, '%')
  LEFT JOIN transaction_lines l ON l.account_id = a.id
  LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = b.business_id
  WHERE b.business_id = ? AND b.is_active = 1
  GROUP BY b.id
  ORDER BY b.account_name ASC
");
$stmt->execute([$businessId]);
$banks = $stmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_bank') {
    $name = $_POST['account_name'] ?? '';
    $bank = $_POST['bank_name'] ?? '';
    $acc  = $_POST['account_number'] ?? '';
    $curr = $_POST['currency'] ?? 'IDR';
    $open = (float) ($_POST['opening_balance'] ?? 0);
    $date = $_POST['opening_date'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO bank_accounts (business_id, account_name, bank_name, account_number, currency, opening_balance, opening_date) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$businessId, $name, $bank, $acc, $curr, $open, $date]);
    header('Location: bank-accounts.php?saved=1'); exit;
}
$saved = isset($_GET['saved']);
?>
<script>
document.getElementById('pageTitle').textContent = 'Rekening Bank';
document.getElementById('pageSubtitle').textContent = 'Kelola rekening bank dan saldo pembuka';
document.getElementById('pageActions').innerHTML = `
  <button class="btn btn-primary" onclick="openModal('modalAddBank')"><i class="bi bi-plus-lg me-1"></i> Tambah Rekening</button>
`;
</script>

<?php echo $message; ?>
<?php if ($saved): ?><div class="alert alert-success">Rekening bank berhasil ditambahkan.</div><?php endif; ?>

<div class="stat-grid" style="margin-bottom:1rem;">
  <div class="stat-card">
    <div class="stat-icon bg-info"><i class="bi bi-building"></i></div>
    <div class="stat-info">
      <h3><?= count($banks) ?></h3>
      <p>Rekening Aktif</p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon bg-primary"><i class="bi bi-currency-dollar"></i></div>
    <div class="stat-info">
      <h3><?= number_format(array_sum(array_column($banks, 'opening_balance')), 0, ',', '.') ?></h3>
      <p>Total Saldo Pembuka</p>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-header d-flex justify-content-between align-items-center">
    <div><i class="bi bi-building"></i> Daftar Rekening Bank</div>
  </div>
  <div class="section-body p-0">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:22%">NAMA REKENING</th>
            <th style="width:18%">BANK</th>
            <th style="width:16%">NO. REKENING</th>
            <th style="width:10%">MATA UANG</th>
            <th class="text-right" style="width:16%">SALDO PEMBUKA</th>
            <th class="text-right" style="width:10%"></th>
          </tr>
        </thead>
        <tbody>
        <?php if ($banks): foreach ($banks as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['account_name']) ?></td>
            <td><?= htmlspecialchars($b['bank_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($b['account_number'] ?? '-') ?></td>
            <td><?= htmlspecialchars($b['currency']) ?></td>
            <td class="text-right amount-col"><?= number_format($b['opening_balance'], 0, ',', '.') ?></td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm"><i class="bi bi-pencil"></i></a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada rekening bank.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalAddBank">
  <div class="modal modal-modern" style="max-width:560px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_bank">
      <div class="mdlg-header">
        <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
          <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
          <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
          <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
        </svg>
        <div class="mdlg-header-icon"><i class="bi bi-building"></i></div>
        <div class="mdlg-header-text">
          <div class="mdlg-header-title">Tambah Rekening Bank</div>
          <div class="mdlg-header-sub">Input data rekening bank perusahaan</div>
        </div>
        <button type="button" class="mdlg-close" onclick="closeModal('modalAddBank')" aria-label="Tutup">&times;</button>
      </div>
      <div class="mdlg-body">
        <div class="mdlg-grid">
          <div class="mdlg-field">
            <label class="mdlg-label" for="bankName"><i class="bi bi-building"></i> Nama Rekening <span class="req">*</span></label>
            <input type="text" id="bankName" name="account_name" class="mdlg-input" placeholder="Contoh: BCA Utama" required>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="bankBank"><i class="bi bi-credit-card"></i> Bank</label>
            <input type="text" id="bankBank" name="bank_name" class="mdlg-input" placeholder="Contoh: BCA">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="bankNumber"><i class="bi bi-hash"></i> Nomor Rekening</label>
            <input type="text" id="bankNumber" name="account_number" class="mdlg-input" placeholder="Contoh: 1234567890">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="bankCurrency"><i class="bi bi-currency-exchange"></i> Mata Uang</label>
            <select id="bankCurrency" name="currency" class="mdlg-input">
              <option value="IDR">IDR</option>
              <option value="USD">USD</option>
            </select>
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="bankBalance"><i class="bi bi-calculator"></i> Saldo Pembuka</label>
            <input type="number" id="bankBalance" name="opening_balance" class="mdlg-input" step="0.01" min="0" value="0">
          </div>
          <div class="mdlg-field">
            <label class="mdlg-label" for="bankOpenDate"><i class="bi bi-calendar-event"></i> Tanggal Pembuka</label>
            <input type="date" id="bankOpenDate" name="opening_date" class="mdlg-input" value="<?= date('Y-m-d') ?>">
          </div>
        </div>
      </div>
      <div class="mdlg-footer">
        <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
          <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
        </svg>
        <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalAddBank')">Batal</button>
        <button type="submit" class="mdlg-btn-save"><i class="bi bi-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); }); });
</script>

<?php require_once 'includes/footer.php'; ?>