<?php
// ai-settings.php — Pengaturan API Asisten AI
ob_start();
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_ai') {
    $provider = $_POST['provider'] ?? 'custom';
    $apiKey = trim($_POST['api_key'] ?? '');
    $baseUrl = trim($_POST['base_url'] ?? '');
    $model = trim($_POST['model'] ?? 'gpt-3.5-turbo');
    $temperature = $_POST['temperature'] ?? 0.7;
    $maxTokens = $_POST['max_tokens'] ?? 1000;
    $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO business_ai_settings
            (business_id, provider, api_key, base_url, model, temperature, max_tokens, is_enabled)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            provider=VALUES(provider), api_key=VALUES(api_key), base_url=VALUES(base_url),
            model=VALUES(model), temperature=VALUES(temperature), max_tokens=VALUES(max_tokens),
            is_enabled=VALUES(is_enabled), updated_at=CURRENT_TIMESTAMP");
        $stmt->execute([$businessId, $provider, $apiKey, $baseUrl, $model, $temperature, $maxTokens, $isEnabled]);
        header('Location: ai-settings.php?saved=1'); exit;
    } catch (PDOException $e) {
        $message = 'Gagal menyimpan pengaturan AI: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

$stmt = $pdo->prepare("SELECT * FROM business_ai_settings WHERE business_id = ?");
$stmt->execute([$businessId]);
$ai = $stmt->fetch();
$saved = isset($_GET['saved']);
?>

<script>
document.getElementById('pageTitle').textContent = 'Pengaturan AI';
document.getElementById('pageSubtitle').textContent = 'Konfigurasi API asisten AI untuk menjawab data aplikasi';
</script>

<?php if ($saved): ?><div class="alert alert-success">Pengaturan AI berhasil disimpan.</div><?php endif; ?>
<?php if ($message): ?><div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="section">
  <div class="section-header"><i class="bi bi-robot"></i> Konfigurasi API AI</div>
  <div class="section-body">
    <p class="text-muted" style="font-size:0.82rem;">Isi kredensial API AI agar asisten chat dapat menjawab pertanyaan seputar data keuangan di aplikasi ini. Dukungan untuk penyedia yang kompatibel OpenAI (OpenAI, dan endpoint chat/completions lain).</p>

    <form method="POST" action="">
      <input type="hidden" name="action" value="save_ai">

      <div class="form-grid">
        <div>
          <label class="form-label">Penyedia API</label>
          <select name="provider" class="form-select">
            <option value="custom" <?= (!isset($ai['provider']) || $ai['provider'] === 'custom') ? 'selected' : '' ?>>Custom / Lainnya</option>
            <option value="openai" <?= ($ai['provider'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
            <option value="ollama" <?= ($ai['provider'] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama (lokal)</option>
          </select>
          <div class="form-text" style="font-size:0.75rem; color:var(--muted); margin-top:0.25rem;">Pilih endpoint yang digunakan.</div>
        </div>
        <div>
          <label class="form-label">Model AI</label>
          <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($ai['model'] ?? 'gpt-3.5-turbo') ?>" placeholder="gpt-3.5-turbo">
        </div>
        <div class="form-col-2">
          <label class="form-label">Base URL API</label>
          <input type="url" name="base_url" class="form-control" value="<?= htmlspecialchars($ai['base_url'] ?? '') ?>" placeholder="https://api.openai.com/v1">
          <div class="form-text" style="font-size:0.75rem; color:var(--muted); margin-top:0.25rem;">Kosongkan untuk menggunakan https://api.openai.com/v1</div>
        </div>
      </div>

      <div class="form-grid mt-3">
        <div>
          <label class="form-label">API Key</label>
          <input type="password" name="api_key" class="form-control" value="<?= htmlspecialchars($ai['api_key'] ?? '') ?>" placeholder="sk-...">
        </div>
        <div>
          <label class="form-label">Temperature</label>
          <input type="number" name="temperature" step="0.1" min="0" max="2" class="form-control" value="<?= htmlspecialchars($ai['temperature'] ?? 0.7) ?>">
        </div>
        <div>
          <label class="form-label">Max Tokens</label>
          <input type="number" name="max_tokens" min="1" class="form-control" value="<?= htmlspecialchars($ai['max_tokens'] ?? 1000) ?>">
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label" style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
          <input type="checkbox" name="is_enabled" value="1" <?= !isset($ai['is_enabled']) || $ai['is_enabled'] ? 'checked' : '' ?> style="margin:0;">
          Aktifkan asisten AI
        </label>
      </div>

      <div class="mt-3">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Pengaturan AI</button>
        <a href="dashboard.php" class="btn btn-outline"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
