<?php
// export.php
// Fitur ekspor seluruh data bisnis ke file .sql (MySQL dump) menggunakan PDO,
// tanpa perlu perintah mysqldump di server.

// Mulai sesi dan koneksi database SEBELUM ada output HTML apa pun,
// agar header() untuk unduhan file dapat dikirim dengan benar.
session_start();
require_once __DIR__ . '/config/database.php';
$businessId = $_SESSION['business_id'] ?? null;

if (empty($businessId)) {
    http_response_code(400);
    die('Bisnis tidak aktif dalam sesi ini.');
}

// Daftar tabel yang akan diekspor, dalam urutan dependensi (induk sebelum anak).
// 'where' berisi klausa WHERE yang difilter sesuai bisnis saat ini agar hasil
// ekspor hanya berisi data milik perusahaan yang sedang aktif (self-contained).
$tables = [
    'businesses'             => ['label' => 'Perusahaan',                'where' => 'WHERE id = ?'],
    'accounts'               => ['label' => 'Chart of Accounts',         'where' => 'WHERE business_id = ?'],
    'bank_accounts'          => ['label' => 'Rekening Bank',             'where' => 'WHERE business_id = ?'],
    'customers'              => ['label' => 'Pelanggan',                 'where' => 'WHERE business_id = ?'],
    'suppliers'              => ['label' => 'Pemasok',                   'where' => 'WHERE business_id = ?'],
    'items'                  => ['label' => 'Barang & Jasa',             'where' => 'WHERE business_id = ?'],
    'business_ai_settings'   => ['label' => 'Pengaturan AI',             'where' => 'WHERE business_id = ?'],
    'transactions'           => ['label' => 'Jurnal Umum',               'where' => 'WHERE business_id = ?'],
    'transaction_lines'      => ['label' => 'Baris Jurnal',              'where' => 'WHERE transaction_id IN (SELECT id FROM transactions WHERE business_id = ?)'],
    'sales_invoices'         => ['label' => 'Faktur Penjualan',          'where' => 'WHERE business_id = ?'],
    'sales_invoice_lines'    => ['label' => 'Baris Faktur Penjualan',    'where' => 'WHERE sales_invoice_id IN (SELECT id FROM sales_invoices WHERE business_id = ?)'],
    'purchase_invoices'      => ['label' => 'Faktur Pembelian',          'where' => 'WHERE business_id = ?'],
    'purchase_invoice_lines' => ['label' => 'Baris Faktur Pembelian',    'where' => 'WHERE purchase_invoice_id IN (SELECT id FROM purchase_invoices WHERE business_id = ?)'],
    'expense_claims'         => ['label' => 'Klaim Biaya',               'where' => 'WHERE business_id = ?'],
    'expense_claim_lines'    => ['label' => 'Baris Klaim Biaya',         'where' => 'WHERE expense_claim_id IN (SELECT id FROM expense_claims WHERE business_id = ?)'],
    'ai_chat_messages'       => ['label' => 'Pesan Asisten AI',          'where' => 'WHERE business_id = ?'],
];

$message = '';
$messageType = '';
$includeStructure = false;
$selected = array_keys($tables); // default: semua tabel dipilih

// Proses ekspor (harus dijalankan sebelum header.php mengeluarkan output HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'do_export') {
    $includeStructure = !empty($_POST['include_structure']);
    $posted = $_POST['tables'] ?? [];
    // Validasi whitelist tabel agar tidak ada injeksi melalui nama tabel
    $selected = array_intersect((array)$posted, array_keys($tables));

    if (empty($selected)) {
        $message = '<div class="alert alert-danger">Pilih paling sedikit satu tabel untuk diekspor.</div>';
        $messageType = 'danger';
    } else {
        try {
            $sql = "-- ========================================\n";
            $sql .= "-- Export Data Manager.io (managerio2)\n";
            $sql .= "-- Tanggal : " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Bisnis  : ID #" . (int)$businessId . "\n";
            $sql .= "-- Dibuat  : Aplikasi Manajer Akuntansi\n";
            $sql .= "-- ========================================\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
            $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

            foreach ($selected as $table) {
                // Struktur tabel (CREATE TABLE)
                if ($includeStructure) {
                    try {
                        $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                        $createRow = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($createRow && isset($createRow['Create Table'])) {
                            $sql .= "-- Struktur tabel: {$table}\n";
                            $sql .= $createRow['Create Table'] . ";\n\n";
                        }
                    } catch (PDOException $e) {
                        $sql .= "-- Gagal ambil struktur tabel {$table}: " . $e->getMessage() . "\n\n";
                    }
                }

                // Data
                $where = $tables[$table]['where'];
                $stmt = $pdo->prepare("SELECT * FROM `{$table}` {$where}");
                $stmt->execute([$businessId]);
                $rows = $stmt->fetchAll();

                if (empty($rows)) {
                    $sql .= "-- Data: {$table} (0 baris, kosong)\n\n";
                    continue;
                }

                $columns = array_keys($rows[0]);
                $colList = implode(',', array_map(function ($c) { return "`{$c}`"; }, $columns));

                $sql .= "-- Data: {$table} (" . count($rows) . " baris)\n";
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($columns as $col) {
                        $v = $row[$col];
                        $values[] = $v === null ? 'NULL' : $pdo->quote($v);
                    }
                    $sql .= "INSERT INTO `{$table}` ({$colList}) VALUES (" . implode(',', $values) . ");\n";
                }
                $sql .= "\n";
            }

            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            $sql .= "-- Selesai diekspor\n";

            $filename = 'managerio2_export_biz' . (int)$businessId . '_' . date('Ymd_His') . '.sql';

            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($sql));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');
            echo $sql;
            exit;
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Gagal mengekspor data: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $messageType = 'danger';
        }
    }
}

// Tidak ada ekspor: tampilkan halaman form (memuat header.php yang mencetak HTML)
require_once 'includes/header.php';
?>
<script>
document.getElementById('pageTitle').textContent = 'Ekspor Data SQL';
document.getElementById('pageSubtitle').textContent = 'Unduh seluruh data perusahaan sebagai file .sql (MySQL dump)';
</script>

<?php echo $message; ?>

<div class="section">
  <div class="section-header"><i class="bi bi-download"></i> Ekspor Seluruh Data ke SQL</div>
  <div class="section-body">
    <?php if ($messageType !== 'danger'): ?>
    <div class="alert alert-info">
      <strong>Tentang fitur ini:</strong> Membuat file <code>.sql</code> berisi struktur dan/atau data
      semua tabel milik bisnis yang sedang aktif (ID #<?= (int)$businessId ?>). File hasil ekspor dapat
      diimpor kembali ke database Manager.io dengan perintah <code>mysql -u root managerio2 &lt; file.sql</code>
      atau melalui phpMyAdmin &rarr; Import.
    </div>
    <?php endif; ?>

    <form method="POST" action="" onsubmit="return confirmExport()">
      <input type="hidden" name="action" value="do_export">

      <div class="mb-3">
        <label class="form-label">Pilih tabel yang akan diekspor</label>
        <div class="row">
          <?php foreach ($tables as $table => $info): ?>
          <div class="col-md-6 mb-2">
            <div class="border rounded p-2" style="background:#f8f9fa;">
              <input type="checkbox" name="tables[]" value="<?= $table ?>" id="tbl_<?= $table ?>" checked>
              <label for="tbl_<?= $table ?>" style="margin-left:0.4rem; font-weight:600;"><?= htmlspecialchars($info['label']) ?></label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mb-3 form-row">
        <div class="form-col">
          <div class="form-check" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="include_structure" value="1" id="include_structure" checked>
            <label for="include_structure" class="form-label" style="margin-bottom:0;">Sertakan struktur tabel (CREATE TABLE)</label>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="bi bi-download"></i> Ekspor ke .sql</button>
        <a href="settings.php" class="btn btn-outline">Batal</a>
      </div>
    </form>

    <div class="mt-4">
      <div class="card">
        <div class="card-header">Petunjuk Penggunaan</div>
        <div class="card-body">
          <ul>
            <li>Ekspor hanya mencakup data milik bisnis yang aktif di sesi ini, sehingga file .sql siap diimpor ke database lain tanpa bawa data perusahaan lain.</li>
            <li>Tabel diurutkan berdasarkan ketergantungan (tabel induk dulu) agar impor kembali tidak gagal karena foreign key.</li>
            <li><code>SET FOREIGN_KEY_CHECKS</code> dimatikan saat impor dan diaktifkan kembali setelahnya.</li>
            <li>Encoding file adalah UTF-8 (utf8mb4), cocok dengan database managerio2.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function confirmExport() {
  return confirm('Yakin ingin mengekspor data terpilih ke file .sql?');
}
</script>

<?php require_once 'includes/footer.php'; ?>
