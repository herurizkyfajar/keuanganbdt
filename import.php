<?php
// import.php
require_once 'includes/header.php';
$businessId = $_SESSION['business_id'];

// Jenis impor yang tersedia
$importTypes = [
    'accounts'   => 'Chart of Accounts',
    'customers'  => 'Pelanggan',
    'suppliers'  => 'Pemasok',
    'items'      => 'Barang & Jasa',
    'bank'       => 'Rekening Bank',
    'transactions' => 'Jurnal Umum',
];

$type = $_GET['type'] ?? 'accounts';
if (!array_key_exists($type, $importTypes)) {
    $type = 'accounts';
}

$message = '';
$messageType = '';
$preview = null;
$columns = [];
$rows = [];

// Proses preview file CSV (belum disimpan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'preview') {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-danger">Silakan pilih file CSV terlebih dahulu.</div>';
    } else {
        $file = $_FILES['csv_file'];
        $tmp = $file['tmp_name'];
        $handle = fopen($tmp, 'r');
        if ($handle === false) {
            $message = '<div class="alert alert-danger">Gagal membaca file CSV.</div>';
        } else {
            $header = fgetcsv($handle, 0, ',');
            if ($header === false) {
                $message = '<div class="alert alert-danger">File CSV kosong atau tidak valid.</div>';
            } else {
                // Bersihkan header
                $columns = array_map(function($h){ return trim($h); }, $header);
                $count = 0;
                while (($row = fgetcsv($handle, 0, ',')) !== false && $count < 20) {
                    $rows[] = array_combine($columns, array_map(function($v){ return trim($v); }, $row));
                    $count++;
                }
                fclose($handle);
                $preview = true;
                $message = '<div class="alert alert-info">Preview berhasil. Periksa data di bawah sebelum mengimpor.</div>';
            }
        }
    }
}

// Proses impor sebenarnya
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'do_import') {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-danger">Silakan pilih file CSV terlebih dahulu.</div>';
    } else {
        $file = $_FILES['csv_file'];
        $tmp = $file['tmp_name'];
        $handle = fopen($tmp, 'r');
        if ($handle === false) {
            $message = '<div class="alert alert-danger">Gagal membaca file CSV.</div>';
        } else {
            $header = fgetcsv($handle, 0, ',');
            if ($header === false) {
                $message = '<div class="alert alert-danger">File CSV kosong atau tidak valid.</div>';
            } else {
                $columns = array_map(function($h){ return trim(strtolower($h)); }, $header);
                $imported = 0;
                $errors = 0;
                $errorDetails = [];

                try {
                    $pdo->beginTransaction();

                    while (($row = fgetcsv($handle, 0, ',')) !== false) {
                        $data = array_combine($columns, array_map(function($v){ return trim($v); }, $row));

                        try {
                            switch ($type) {
                                case 'accounts':
                                    importAccount($pdo, $businessId, $data);
                                    break;
                                case 'customers':
                                    importCustomer($pdo, $businessId, $data);
                                    break;
                                case 'suppliers':
                                    importSupplier($pdo, $businessId, $data);
                                    break;
                                case 'items':
                                    importItem($pdo, $businessId, $data);
                                    break;
                                case 'bank':
                                    importBank($pdo, $businessId, $data);
                                    break;
                                case 'transactions':
                                    importTransaction($pdo, $businessId, $data);
                                    break;
                            }
                            $imported++;
                        } catch (Exception $e) {
                            $errors++;
                            $errorDetails[] = $e->getMessage();
                        }
                    }

                    $pdo->commit();
                    $message = '<div class="alert alert-success">Impor selesai: <strong>' . $imported . '</strong> data berhasil, <strong>' . $errors . '</strong> gagal.</div>';
                    if (!empty($errorDetails)) {
                        $message .= '<div class="alert alert-danger" style="font-size:0.8rem;">' . implode('<br>', array_slice($errorDetails, 0, 10)) . '</div>';
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = '<div class="alert alert-danger">Gagal mengimpor: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            fclose($handle);
        }
    }
}

// Fungsi-fungsi impor spesifik
function importAccount($pdo, $businessId, $data) {
    $code    = $data['code'] ?? '';
    $name    = $data['name'] ?? '';
    $type    = $data['type'] ?? 'asset';
    $parent  = $data['parent'] ?? null;
    $desc    = $data['description'] ?? '';
    if ($code === '' || $name === '') throw new Exception('Kode atau nama akun kosong');
    // Normalisasi tipe
    $typeMap = [
        'aset' => 'asset', 'asset' => 'asset',
        'bank' => 'bank',
        'kewajiban' => 'liability', 'liability' => 'liability',
        'ekuitas' => 'equity', 'equity' => 'equity',
        'pendapatan' => 'revenue', 'revenue' => 'revenue',
        'beban' => 'expense', 'expense' => 'expense',
    ];
    $type = strtolower($type);
    $type = $typeMap[$type] ?? 'asset';
    $stmt = $pdo->prepare("INSERT INTO accounts (business_id, code, name, type, parent_id, description) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$businessId, $code, $name, $type, $parent, $desc]);
}

function importCustomer($pdo, $businessId, $data) {
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $tax = $data['tax_number'] ?? $data['npwp'] ?? '';
    if ($name === '') throw new Exception('Nama pelanggan kosong');
    $stmt = $pdo->prepare("INSERT INTO customers (business_id, name, email, phone, address, tax_number) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$businessId, $name, $email, $phone, $address, $tax]);
}

function importSupplier($pdo, $businessId, $data) {
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $tax = $data['tax_number'] ?? $data['npwp'] ?? '';
    if ($name === '') throw new Exception('Nama pemasok kosong');
    $stmt = $pdo->prepare("INSERT INTO suppliers (business_id, name, email, phone, address, tax_number) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$businessId, $name, $email, $phone, $address, $tax]);
}

function importItem($pdo, $businessId, $data) {
    $code = $data['code'] ?? '';
    $name = $data['name'] ?? '';
    $desc = $data['description'] ?? '';
    $type = strtolower($data['type'] ?? 'service');
    $type = ($type === 'product' || $type === 'produk') ? 'product' : 'service';
    $salesPrice = (float) ($data['sales_price'] ?? $data['harga_jual'] ?? 0);
    $purchasePrice = (float) ($data['purchase_price'] ?? $data['harga_beli'] ?? 0);
    if ($name === '') throw new Exception('Nama item kosong');
    $stmt = $pdo->prepare("INSERT INTO items (business_id, code, name, description, type, sales_price, purchase_price) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$businessId, $code, $name, $desc, $type, $salesPrice, $purchasePrice]);
}

function importBank($pdo, $businessId, $data) {
    $name = $data['account_name'] ?? $data['nama_rekening'] ?? '';
    $bank = $data['bank_name'] ?? $data['bank'] ?? '';
    $acc = $data['account_number'] ?? $data['no_rekening'] ?? '';
    $curr = $data['currency'] ?? 'IDR';
    $open = (float) ($data['opening_balance'] ?? $data['saldo_pembuka'] ?? 0);
    $date = $data['opening_date'] ?? $data['tanggal_pembuka'] ?? date('Y-m-d');
    if ($name === '') throw new Exception('Nama rekening kosong');
    $stmt = $pdo->prepare("INSERT INTO bank_accounts (business_id, account_name, bank_name, account_number, currency, opening_balance, opening_date) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$businessId, $name, $bank, $acc, $curr, $open, $date]);
}

function importTransaction($pdo, $businessId, $data) {
    $ref   = $data['reference'] ?? $data['nomor'] ?? '';
    $desc  = $data['description'] ?? $data['keterangan'] ?? '';
    $tgl   = $data['transaction_date'] ?? $data['tanggal'] ?? date('Y-m-d');
    $type  = strtolower($data['type'] ?? 'journal');
    $type = in_array($type, ['journal','receipt','payment']) ? $type : 'journal';
    $debit  = (float) ($data['debit'] ?? 0);
    $credit = (float) ($data['credit'] ?? 0);
    $accCode = $data['account_code'] ?? $data['kode_akun'] ?? '';
    $accName = $data['account_name'] ?? $data['nama_akun'] ?? '';
    if ($ref === '' && $desc === '') throw new Exception('Referensi dan keterangan kosong');

    $stmt = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (?,?,?,?,?)");
    $stmt->execute([$businessId, $ref, $desc, $tgl, $type]);
    $txId = $pdo->lastInsertId();

    // Cari atau buat akun
    $accId = null;
    if ($accCode !== '') {
        $s = $pdo->prepare("SELECT id FROM accounts WHERE business_id=? AND code=? LIMIT 1");
        $s->execute([$businessId, $accCode]);
        $r = $s->fetch();
        if ($r) $accId = $r['id'];
    }
    if ($accId === null && $accName !== '') {
        $s = $pdo->prepare("SELECT id FROM accounts WHERE business_id=? AND name=? LIMIT 1");
        $s->execute([$businessId, $accName]);
        $r = $s->fetch();
        if ($r) $accId = $r['id'];
    }
    if ($accId === null) {
        // Buat akun baru otomatis sebagai aset
        $s = $pdo->prepare("INSERT INTO accounts (business_id, code, name, type) VALUES (?,?,?,?)");
        $s->execute([$businessId, $accCode ?: 'IMP-' . uniqid(), $accName ?: 'Akun Impor', 'asset']);
        $accId = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?,?,?,?,?)");
    $stmt->execute([$txId, $accId, $desc, $debit, $credit]);
}
?>
<script>
document.getElementById('pageTitle').textContent = 'Import Data';
document.getElementById('pageSubtitle').textContent = 'Impor data dari file CSV ke aplikasi';
document.getElementById('pageActions').innerHTML = '';
</script>

<?php echo $message; ?>

<div class="section">
  <div class="section-header"><i class="bi bi-upload"></i> Impor Data dari CSV</div>
  <div class="section-body">
    <div class="row">
      <div class="col-md-6">
        <form method="POST" action="" enctype="multipart/form-data">
          <input type="hidden" name="action" value="preview">
          <input type="hidden" name="type" value="<?= $type ?>">
          <div class="mb-3">
            <label class="form-label">Jenis Data</label>
            <select name="type" class="form-select" onchange="this.form.submit()">
              <?php foreach ($importTypes as $key => $label): ?>
                <option value="<?= $key ?>" <?= $key == $type ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Pilih File CSV</label>
            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
            <div class="form-text">File harus berformat CSV dengan header di baris pertama.</div>
          </div>
          <?php if ($type === 'transactions'): ?>
            <div class="alert alert-warning">
              <strong>Catatan untuk Jurnal Umum:</strong> Pastikan CSV memiliki kolom: reference, description, transaction_date, type, debit, credit, account_code (atau account_name).
            </div>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">Preview Data</button>
        </form>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Format CSV yang Direkomendasikan</div>
          <div class="card-body">
            <h6><?= htmlspecialchars($importTypes[$type]) ?></h6>
            <pre style="font-size:0.8rem; background:#f8f9fa; padding:0.5rem; border-radius:0.4rem; overflow-x:auto;">
<?php
$examples = [
    'accounts'   => "code,name,type,parent_id,description\n110,Kas,asset,,Uang tunai di kantor\n120,Bank BCA,asset,,Rekening bank BCA\n410,Penjualan,revenue,,Penjualan barang",
    'customers'  => "name,email,phone,address,tax_number\nPT Abadi,abadi@email.com,08123456789,Jl. Abadi,01.234.567.8",
    'suppliers'  => "name,email,phone,address,tax_number\nCV Sejahtera,sejahtera@email.com,081299998888,Jl. Sejahtera,02.345.678.9",
    'items'      => "code,name,type,description,sales_price,purchase_price\nITEM-001,Laptop,product,Laptop untuk karyawan,12000000,10000000",
    'bank'       => "account_name,bank_name,account_number,currency,opening_balance,opening_date\nBCA Utama,BCA,1234567890,IDR,10000000,2026-01-01",
    'transactions' => "reference,description,transaction_date,type,debit,credit,account_code\nINV-0001,Penjualan tunai,2026-01-15,journal,5000000,0,110",
];
echo $examples[$type] ?? '';
?>
            </pre>
            <a href="https://www.google.com/search?q=csv+format+example" target="_blank" class="btn btn-outline btn-sm">Lihat contoh CSV</a>
          </div>
        </div>
      </div>
    </div>

    <?php if ($preview): ?>
    <div class="mt-4">
      <form method="POST" action="">
        <input type="hidden" name="action" value="do_import">
        <input type="hidden" name="type" value="<?= $type ?>">
        <h5>Preview Data (maks 20 baris pertama)</h5>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <?php foreach ($columns as $col): ?>
                  <th><?= htmlspecialchars($col) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
              <tr>
                <?php foreach ($columns as $col): ?>
                  <td><?= htmlspecialchars($row[$col] ?? '') ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">Yakin Impor Semua Data</button>
          <a href="import.php?type=<?= $type ?>" class="btn btn-outline">Batal</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div class="mt-4">
      <div class="alert alert-info">
        <strong>Penting:</strong>
        <ul>
          <li>Pastikan file CSV menggunakan encoding UTF-8.</li>
          <li>Pemisah kolom harus koma (,).</li>
          <li>Impor data akan ditambahkan ke data yang sudah ada, tidak akan menimpa.</li>
          <li>Sebelum mengimpor transaksi, pastikan Chart of Accounts sudah tersedia.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>