<?php
// api/ai-chat.php — Endpoint asisten AI untuk menjawab seputar data aplikasi
require_once __DIR__ . '/../config/database.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Tidak terautentikasi']);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$businessId = (int)($_SESSION['business_id'] ?? 0);
if (!$businessId) {
    http_response_code(400);
    echo json_encode(['error' => 'Bisnis belum dipilih']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');
$inputSessionId = trim($input['session_id'] ?? '');
if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Pesan kosong']);
    exit;
}

// ---- Tentukan / buat sesi percakapan (berlaku 24 jam) ----
$sessionId = null;
if ($inputSessionId !== '') {
    // Gunakan sesi yang diminta jika milik user & bisnis ini
    $stmt = $pdo->prepare("SELECT 1 FROM ai_chat_messages WHERE business_id=? AND user_id=? AND session_id=? LIMIT 1");
    $stmt->execute([$businessId, $userId, $inputSessionId]);
    if ($stmt->fetch()) {
        $sessionId = $inputSessionId;
    }
}
if (!$sessionId) {
    // Cari sesi aktif (aktivitas dalam 24 jam terakhir)
    $stmt = $pdo->prepare("
        SELECT session_id FROM ai_chat_messages
        WHERE business_id=? AND user_id=? AND session_id IS NOT NULL
          AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$businessId, $userId]);
    $row = $stmt->fetch();
    if ($row) {
        $sessionId = $row['session_id'];
    }
}
if (!$sessionId) {
    $sessionId = bin2hex(random_bytes(16)); // sesi baru
}

// Simpan pesan pengguna
$stmt = $pdo->prepare("INSERT INTO ai_chat_messages (business_id, user_id, session_id, role, content) VALUES (?, ?, ?, 'user', ?)");
$stmt->execute([$businessId, $userId, $sessionId, $userMessage]);

// Ambil pengaturan AI
$stmt = $pdo->prepare("SELECT * FROM business_ai_settings WHERE business_id = ?");
$stmt->execute([$businessId]);
$aiSettings = $stmt->fetch();

$enabled = !empty($aiSettings['is_enabled']);
$apiKey = $aiSettings['api_key'] ?? '';
$baseUrl = rtrim($aiSettings['base_url'] ?? '', '/');
$model = $aiSettings['model'] ?? 'gpt-3.5-turbo';
$temperature = (float)($aiSettings['temperature'] ?? 0.7);
$maxTokens = (int)($aiSettings['max_tokens'] ?? 1000);

// Bangun konteks data bisnis untuk AI
$context = buildDataContext($pdo, $businessId);

$systemPrompt = "Anda adalah asisten AI untuk aplikasi akuntansi Managerio2. Jawablah pertanyaan pengguna berdasarkan data bisnis di bawah ini (dalam bahasa Indonesia). Berikan jawaban yang akurat, singkat, dan bermutu. Jika data tidak mencukupi, sampaikan bahwa data tidak tersedia. Jangan mengarang angka.\n\nDATA BISNIS:\n" . $context;

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user', 'content' => $userMessage],
];

$reply = '';
if ($enabled && $apiKey) {
    $reply = callAiApi($baseUrl, $apiKey, $model, $temperature, $maxTokens, $messages);
} else {
    $reply = "Asisten AI belum dikonfigurasi. Silakan atur API AI di menu Pengaturan > Asisten AI terlebih dahulu.";
}

// Simpan balasan asisten
$stmt = $pdo->prepare("INSERT INTO ai_chat_messages (business_id, user_id, session_id, role, content) VALUES (?, ?, ?, 'assistant', ?)");
$stmt->execute([$businessId, $userId, $sessionId, $reply]);

echo json_encode(['reply' => $reply, 'session_id' => $sessionId]);
exit;

/**
 * Kumpulkan SEMUA data bisnis sebagai konteks AI.
 * Mengambil seluruh data dari semua tabel agar AI dapat menjawab
 * pertanyaan apa pun tentang data aplikasi.
 */
function buildDataContext($pdo, $businessId): string {
    $lines = [];

    // ============================================================
    // 1. PERUSAHAAN
    // ============================================================
    $stmt = $pdo->prepare("SELECT name, address, phone, email, tax_number, currency, financial_year_start FROM businesses WHERE id = ?");
    $stmt->execute([$businessId]);
    $biz = $stmt->fetch();
    $lines[] = "PERUSAHAAN:";
    $lines[] = "  - Nama: " . htmlspecialchars($biz['name'] ?? '-');
    $lines[] = "  - Alamat: " . htmlspecialchars($biz['address'] ?? '-');
    $lines[] = "  - Telepon: " . htmlspecialchars($biz['phone'] ?? '-');
    $lines[] = "  - Email: " . htmlspecialchars($biz['email'] ?? '-');
    $lines[] = "  - NPWP: " . htmlspecialchars($biz['tax_number'] ?? '-');
    $lines[] = "  - Mata uang: " . htmlspecialchars($biz['currency'] ?? 'IDR');
    $lines[] = "  - Awal tahun buku: " . htmlspecialchars($biz['financial_year_start'] ?? '-');

    // ============================================================
    // 2. CHART OF ACCOUNTS (semua akun dengan saldo)
    // ============================================================
    $lines[] = "\nDAFTAR AKUN (Chart of Accounts):";
    $stmt = $pdo->prepare("
      SELECT a.code, a.name, a.type, a.description, a.is_active,
             COALESCE(SUM(CASE WHEN a.type IN ('asset','bank','expense') THEN l.debit - l.credit ELSE l.credit - l.debit END),0) as balance
      FROM accounts a
      LEFT JOIN transaction_lines l ON l.account_id = a.id
      WHERE a.business_id = ?
      GROUP BY a.id, a.code, a.name, a.type, a.description, a.is_active
      ORDER BY a.code ASC
    ");
    $stmt->execute([$businessId]);
    $acctCount = 0;
    while ($row = $stmt->fetch()) {
        $status = $row['is_active'] ? 'Aktif' : 'Tidak Aktif';
        $lines[] = sprintf(
            "  - %s | %s | %s | %s | saldo: %s",
            htmlspecialchars($row['code'] ?? '-'),
            htmlspecialchars($row['name'] ?? '-'),
            htmlspecialchars($row['type'] ?? '-'),
            $status,
            number_format($row['balance'], 0, ',', '.')
        );
        $acctCount++;
    }
    $lines[] = "  Total akun: " . $acctCount;

    // ============================================================
    // 3. REKENING BANK
    // ============================================================
    $lines[] = "\nREKENING BANK:";
    $stmt = $pdo->prepare("
      SELECT account_name, bank_name, account_number, currency, opening_balance, opening_date, is_active
      FROM bank_accounts WHERE business_id = ?
    ");
    $stmt->execute([$businessId]);
    $bankCount = 0;
    while ($row = $stmt->fetch()) {
        $status = $row['is_active'] ? 'Aktif' : 'Tidak Aktif';
        $lines[] = sprintf(
            "  - %s | %s | %s | %s | saldo pembuka: %s | %s",
            htmlspecialchars($row['account_name'] ?? '-'),
            htmlspecialchars($row['bank_name'] ?? '-'),
            htmlspecialchars($row['account_number'] ?? '-'),
            htmlspecialchars($row['currency'] ?? 'IDR'),
            number_format($row['opening_balance'], 0, ',', '.'),
            $status
        );
        $bankCount++;
    }
    if ($bankCount === 0) {
        $lines[] = "  (tidak ada rekening bank)";
    } else {
        $lines[] = "  Total rekening bank: " . $bankCount;
    }

    // ============================================================
    // 4. PELANGGAN
    // ============================================================
    $lines[] = "\nPELANGGAN:";
    $stmt = $pdo->prepare("
      SELECT name, email, phone, address, tax_number, credit_limit, balance, is_active
      FROM customers WHERE business_id = ?
    ");
    $stmt->execute([$businessId]);
    $custCount = 0;
    while ($row = $stmt->fetch()) {
        $status = $row['is_active'] ? 'Aktif' : 'Tidak Aktif';
        $lines[] = sprintf(
            "  - %s | email: %s | telp: %s | NPWP: %s | saldo: %s | limite kredit: %s | %s",
            htmlspecialchars($row['name'] ?? '-'),
            htmlspecialchars($row['email'] ?? '-'),
            htmlspecialchars($row['phone'] ?? '-'),
            htmlspecialchars($row['tax_number'] ?? '-'),
            number_format($row['balance'], 0, ',', '.'),
            number_format($row['credit_limit'], 0, ',', '.'),
            $status
        );
        $custCount++;
    }
    if ($custCount === 0) {
        $lines[] = "  (tidak ada pelanggan)";
    } else {
        $lines[] = "  Total pelanggan: " . $custCount;
    }

    // ============================================================
    // 5. PEMASOK
    // ============================================================
    $lines[] = "\nPEMASOK:";
    $stmt = $pdo->prepare("
      SELECT name, email, phone, address, tax_number, balance, is_active
      FROM suppliers WHERE business_id = ?
    ");
    $stmt->execute([$businessId]);
    $suppCount = 0;
    while ($row = $stmt->fetch()) {
        $status = $row['is_active'] ? 'Aktif' : 'Tidak Aktif';
        $lines[] = sprintf(
            "  - %s | email: %s | telp: %s | NPWP: %s | saldo: %s | %s",
            htmlspecialchars($row['name'] ?? '-'),
            htmlspecialchars($row['email'] ?? '-'),
            htmlspecialchars($row['phone'] ?? '-'),
            htmlspecialchars($row['tax_number'] ?? '-'),
            number_format($row['balance'], 0, ',', '.'),
            $status
        );
        $suppCount++;
    }
    if ($suppCount === 0) {
        $lines[] = "  (tidak ada pemasok)";
    } else {
        $lines[] = "  Total pemasok: " . $suppCount;
    }

    // ============================================================
    // 6. BARANG & JASA (Items)
    // ============================================================
    $lines[] = "\nBARANG & JASA:";
    $stmt = $pdo->prepare("
      SELECT code, name, description, type, sales_price, purchase_price, is_active
      FROM items WHERE business_id = ?
    ");
    $stmt->execute([$businessId]);
    $itemCount = 0;
    while ($row = $stmt->fetch()) {
        $status = $row['is_active'] ? 'Aktif' : 'Tidak Aktif';
        $lines[] = sprintf(
            "  - %s | %s | %s | harga jual: %s | harga beli: %s | %s",
            htmlspecialchars($row['code'] ?? '-'),
            htmlspecialchars($row['name'] ?? '-'),
            htmlspecialchars($row['type'] ?? '-'),
            number_format($row['sales_price'], 0, ',', '.'),
            number_format($row['purchase_price'], 0, ',', '.'),
            $status
        );
        $itemCount++;
    }
    if ($itemCount === 0) {
        $lines[] = "  (tidak ada barang/jasa)";
    } else {
        $lines[] = "  Total barang/jasa: " . $itemCount;
    }

    // ============================================================
    // 7. TRANSAKSI (semua)
    // ============================================================
    $lines[] = "\nTRANSAKSI (semua):";
    $stmt = $pdo->prepare("
      SELECT t.id, t.reference, t.description, t.transaction_date, t.type, t.created_at
      FROM transactions t
      WHERE t.business_id = ?
      ORDER BY t.transaction_date DESC, t.id DESC
    ");
    $stmt->execute([$businessId]);
    $txCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - %s | %s | %s | %s | %s | id:%d",
            htmlspecialchars($row['transaction_date'] ?? '-'),
            htmlspecialchars($row['reference'] ?? '-'),
            htmlspecialchars($row['description'] ?? '-'),
            htmlspecialchars($row['type'] ?? '-'),
            htmlspecialchars($row['created_at'] ?? '-'),
            (int)$row['id']
        );
        $txCount++;
    }
    if ($txCount === 0) {
        $lines[] = "  (tidak ada transaksi)";
    } else {
        $lines[] = "  Total transaksi: " . $txCount;
    }

    // ============================================================
    // 8. TRANSACTION LINES (semua baris jurnal)
    // ============================================================
    $lines[] = "\nBARIS JURNAL (semua):";
    $stmt = $pdo->prepare("
      SELECT l.id, l.transaction_id, l.account_id, a.code as account_code, a.name as account_name,
             l.description, l.debit, l.credit
      FROM transaction_lines l
      LEFT JOIN accounts a ON l.account_id = a.id
      WHERE l.transaction_id IN (SELECT id FROM transactions WHERE business_id = ?)
      ORDER BY l.id ASC
    ");
    $stmt->execute([$businessId]);
    $lineCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - id:%d | transaksi:%d | akun:%s (%s) | %s | debit: %s | kredit: %s",
            (int)$row['id'],
            (int)$row['transaction_id'],
            htmlspecialchars($row['account_code'] ?? '-'),
            htmlspecialchars($row['account_name'] ?? '-'),
            htmlspecialchars($row['description'] ?? '-'),
            number_format($row['debit'], 0, ',', '.'),
            number_format($row['credit'], 0, ',', '.')
        );
        $lineCount++;
    }
    if ($lineCount === 0) {
        $lines[] = "  (tidak ada baris jurnal)";
    } else {
        $lines[] = "  Total baris jurnal: " . $lineCount;
    }

    // ============================================================
    // 9. FAKTUR PENJUALAN (semua)
    // ============================================================
    $lines[] = "\nFAKTUR PENJUALAN (semua):";
    $stmt = $pdo->prepare("
      SELECT si.id, si.invoice_number, si.customer_id, c.name as customer_name,
             si.invoice_date, si.due_date, si.status, si.subtotal, si.tax_amount, si.total, si.notes
      FROM sales_invoices si
      LEFT JOIN customers c ON si.customer_id = c.id
      WHERE si.business_id = ?
      ORDER BY si.invoice_date DESC, si.id DESC
    ");
    $stmt->execute([$businessId]);
    $invCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - %s | %s | %s | %s | %s | subtotal: %s | pajak: %s | total: %s | catatan: %s",
            htmlspecialchars($row['invoice_number'] ?? '-'),
            htmlspecialchars($row['customer_name'] ?? '-'),
            htmlspecialchars($row['invoice_date'] ?? '-'),
            htmlspecialchars($row['due_date'] ?? '-'),
            htmlspecialchars($row['status'] ?? '-'),
            number_format($row['subtotal'], 0, ',', '.'),
            number_format($row['tax_amount'], 0, ',', '.'),
            number_format($row['total'], 0, ',', '.'),
            htmlspecialchars($row['notes'] ?? '-')
        );
        $invCount++;
    }
    if ($invCount === 0) {
        $lines[] = "  (tidak ada faktur penjualan)";
    } else {
        $lines[] = "  Total faktur penjualan: " . $invCount;
    }

    // ============================================================
    // 10. SALES INVOICE LINES (semua)
    // ============================================================
    $lines[] = "\nBARIS FAKTUR PENJUALAN (semua):";
    $stmt = $pdo->prepare("
      SELECT sil.id, sil.sales_invoice_id, sil.item_id, sil.description,
             sil.quantity, sil.unit_price, sil.amount, sil.account_id
      FROM sales_invoice_lines sil
      WHERE sil.sales_invoice_id IN (SELECT id FROM sales_invoices WHERE business_id = ?)
    ");
    $stmt->execute([$businessId]);
    $silCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - id:%d | faktur:%d | item:%s | %s | qty: %s | harga satuan: %s | jumlah: %s | akun:%s",
            (int)$row['id'],
            (int)$row['sales_invoice_id'],
            htmlspecialchars($row['item_id'] ?? '-'),
            htmlspecialchars($row['description'] ?? '-'),
            number_format($row['quantity'], 0, ',', '.'),
            number_format($row['unit_price'], 0, ',', '.'),
            number_format($row['amount'], 0, ',', '.'),
            htmlspecialchars($row['account_id'] ?? '-')
        );
        $silCount++;
    }
    if ($silCount === 0) {
        $lines[] = "  (tidak ada baris faktur penjualan)";
    } else {
        $lines[] = "  Total baris faktur penjualan: " . $silCount;
    }

    // ============================================================
    // 11. FAKTUR PEMBELIAN (semua)
    // ============================================================
    $lines[] = "\nFAKTUR PEMBELIAN (semua):";
    $stmt = $pdo->prepare("
      SELECT pi.id, pi.invoice_number, pi.supplier_id, s.name as supplier_name,
             pi.invoice_date, pi.due_date, pi.status, pi.subtotal, pi.tax_amount, pi.total, pi.notes
      FROM purchase_invoices pi
      LEFT JOIN suppliers s ON pi.supplier_id = s.id
      WHERE pi.business_id = ?
      ORDER BY pi.invoice_date DESC, pi.id DESC
    ");
    $stmt->execute([$businessId]);
    $purCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - %s | %s | %s | %s | %s | subtotal: %s | pajak: %s | total: %s | catatan: %s",
            htmlspecialchars($row['invoice_number'] ?? '-'),
            htmlspecialchars($row['supplier_name'] ?? '-'),
            htmlspecialchars($row['invoice_date'] ?? '-'),
            htmlspecialchars($row['due_date'] ?? '-'),
            htmlspecialchars($row['status'] ?? '-'),
            number_format($row['subtotal'], 0, ',', '.'),
            number_format($row['tax_amount'], 0, ',', '.'),
            number_format($row['total'], 0, ',', '.'),
            htmlspecialchars($row['notes'] ?? '-')
        );
        $purCount++;
    }
    if ($purCount === 0) {
        $lines[] = "  (tidak ada faktur pembelian)";
    } else {
        $lines[] = "  Total faktur pembelian: " . $purCount;
    }

    // ============================================================
    // 12. PURCHASE INVOICE LINES (semua)
    // ============================================================
    $lines[] = "\nBARIS FAKTUR PEMBELIAN (semua):";
    $stmt = $pdo->prepare("
      SELECT pil.id, pil.purchase_invoice_id, pil.item_id, pil.description,
             pil.quantity, pil.unit_price, pil.amount, pil.account_id
      FROM purchase_invoice_lines pil
      WHERE pil.purchase_invoice_id IN (SELECT id FROM purchase_invoices WHERE business_id = ?)
    ");
    $stmt->execute([$businessId]);
    $pilCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - id:%d | faktur:%d | item:%s | %s | qty: %s | harga satuan: %s | jumlah: %s | akun:%s",
            (int)$row['id'],
            (int)$row['purchase_invoice_id'],
            htmlspecialchars($row['item_id'] ?? '-'),
            htmlspecialchars($row['description'] ?? '-'),
            number_format($row['quantity'], 0, ',', '.'),
            number_format($row['unit_price'], 0, ',', '.'),
            number_format($row['amount'], 0, ',', '.'),
            htmlspecialchars($row['account_id'] ?? '-')
        );
        $pilCount++;
    }
    if ($pilCount === 0) {
        $lines[] = "  (tidak ada baris faktur pembelian)";
    } else {
        $lines[] = "  Total baris faktur pembelian: " . $pilCount;
    }

    // ============================================================
    // 13. EXPENSE CLAIMS (semua klaim biaya)
    // ============================================================
    $lines[] = "\nKLAIM BIAYA (semua):";
    $stmt = $pdo->prepare("
      SELECT ec.id, ec.employee_name, ec.claim_date, ec.status, ec.total, ec.notes
      FROM expense_claims ec
      WHERE ec.business_id = ?
      ORDER BY ec.claim_date DESC, ec.id DESC
    ");
    $stmt->execute([$businessId]);
    $claimCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - id:%d | %s | %s | %s | total: %s | catatan: %s",
            (int)$row['id'],
            htmlspecialchars($row['employee_name'] ?? '-'),
            htmlspecialchars($row['claim_date'] ?? '-'),
            htmlspecialchars($row['status'] ?? '-'),
            number_format($row['total'], 0, ',', '.'),
            htmlspecialchars($row['notes'] ?? '-')
        );
        $claimCount++;
    }
    if ($claimCount === 0) {
        $lines[] = "  (tidak ada klaim biaya)";
    } else {
        $lines[] = "  Total klaim biaya: " . $claimCount;
    }

    // ============================================================
    // 14. EXPENSE CLAIM LINES (semua)
    // ============================================================
    $lines[] = "\nBARIS KLAIM BIAYA (semua):";
    $stmt = $pdo->prepare("
      SELECT ecl.id, ecl.expense_claim_id, ecl.account_id, ecl.description, ecl.amount
      FROM expense_claim_lines ecl
      WHERE ecl.expense_claim_id IN (SELECT id FROM expense_claims WHERE business_id = ?)
    ");
    $stmt->execute([$businessId]);
    $eclCount = 0;
    while ($row = $stmt->fetch()) {
        $lines[] = sprintf(
            "  - id:%d | klaim:%d | akun:%s | %s | jumlah: %s",
            (int)$row['id'],
            (int)$row['expense_claim_id'],
            htmlspecialchars($row['account_id'] ?? '-'),
            htmlspecialchars($row['description'] ?? '-'),
            number_format($row['amount'], 0, ',', '.')
        );
        $eclCount++;
    }
    if ($eclCount === 0) {
        $lines[] = "  (tidak ada baris klaim biaya)";
    } else {
        $lines[] = "  Total baris klaim biaya: " . $eclCount;
    }

    return implode("\n", $lines);
}

/**
 * Panggil API AI yang kompatibel OpenAI (chat/completions).
 */
function callAiApi(string $baseUrl, string $apiKey, string $model, float $temperature, int $maxTokens, array $messages): string {
    $suffix = '/chat/completions';
    if ($baseUrl) {
        $baseUrl = rtrim($baseUrl, '/');
        $url = substr($baseUrl, -strlen($suffix)) === $suffix ? $baseUrl : $baseUrl . $suffix;
    } else {
        $url = 'https://api.openai.com/v1/chat/completions';
    }
    $payload = json_encode([
        'model' => $model,
        'messages' => $messages,
        'temperature' => $temperature,
        'max_tokens' => $maxTokens,
        'stream' => false,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return "Gagal menghubungi API AI.";
    }
    $data = json_decode($response, true);
    $choice = $data['choices'][0] ?? null;
    $content = $choice['message']['content'] ?? null;
    if ($content === null || $content === '') {
        $content = $choice['message']['reasoning_content'] ?? null;
    }
    if ($httpCode >= 400 || empty($content)) {
        $err = $data['error']['message'] ?? 'Respons API tidak valid';
        return "Maaf, AI menanggapi dengan kesalahan: " . htmlspecialchars($err);
    }
    return trim($content);
}
