<?php
// settings.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$stmt = $pdo->prepare("SELECT * FROM businesses WHERE id = ?");
$stmt->execute([$businessId]);
$biz = $stmt->fetch();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_business') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $tax = $_POST['tax_number'] ?? '';
    $currency = $_POST['currency'] ?? 'IDR';
    $fyStart = $_POST['financial_year_start'] ?? null;
    $stmt = $pdo->prepare("UPDATE businesses SET name=?, address=?, phone=?, email=?, tax_number=?, currency=?, financial_year_start=? WHERE id=?");
    $stmt->execute([$name, $address, $phone, $email, $tax, $currency, $fyStart, $businessId]);
    $_SESSION['business_name'] = $name;
    $_SESSION['business_currency'] = $currency;
    header('Location: settings.php?saved=1'); exit;
}
$saved = isset($_GET['saved']);
?>
<script>
document.getElementById('pageTitle').textContent = 'Pengaturan Perusahaan';
document.getElementById('pageSubtitle').textContent = 'Ubah profil dan pengaturan bisnis';
</script>

<?php echo $message; ?>
<?php if ($saved): ?><div class="alert alert-success">Pengaturan berhasil disimpan.</div><?php endif; ?>

<div class="section">
  <div class="section-header"><i class="bi bi-gear"></i> Profil Perusahaan</div>
  <div class="section-body">
    <form method="POST" action="">
      <input type="hidden" name="action" value="update_business">
      <div class="form-grid">
        <div class="form-col-2">
          <label class="form-label">Nama Perusahaan</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($biz['name'] ?? '') ?>" required>
        </div>
        <div>
          <label class="form-label">Mata Uang</label>
          <select name="currency" class="form-select">
            <option value="IDR" <?= ($biz['currency'] ?? 'IDR') == 'IDR' ? 'selected' : '' ?>>IDR</option>
            <option value="USD" <?= ($biz['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD</option>
            <option value="EUR" <?= ($biz['currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>EUR</option>
          </select>
        </div>
        <div class="form-col-2">
          <label class="form-label">Alamat</label>
          <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($biz['address'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="form-label">Telepon</label>
          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($biz['phone'] ?? '') ?>">
        </div>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($biz['email'] ?? '') ?>">
        </div>
        <div>
          <label class="form-label">NPWP</label>
          <input type="text" name="tax_number" class="form-control" value="<?= htmlspecialchars($biz['tax_number'] ?? '') ?>">
        </div>
        <div>
          <label class="form-label">Awal Tahun Buku</label>
          <input type="date" name="financial_year_start" class="form-control" value="<?= htmlspecialchars($biz['financial_year_start'] ?? '') ?>">
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-primary">Simpan Pengaturan</button>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>