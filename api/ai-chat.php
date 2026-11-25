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
 * Kumpulkan rangkuman data bisnis sebagai konteks AI.
 */
function buildDataContext($pdo, $businessId): string {
    $lines = [];

    $stmt = $pdo->prepare("SELECT name, currency, tax_number FROM businesses WHERE id = ?");
    $stmt->execute([$businessId]);
    $biz = $stmt->fetch();
    $lines[] = "- Perusahaan: " . htmlspecialchars($biz['name'] ?? '-') . " | Mata uang: " . ($biz['currency'] ?? 'IDR');

    // Saldo per tipe akun
    $stmt = $pdo->prepare("
      SELECT a.type, COUNT(*) as cnt,
             COALESCE(SUM(CASE WHEN a.type IN ('asset','bank','expense') THEN l.debit - l.credit ELSE l.credit - l.debit END),0) as balance
      FROM accounts a
      LEFT JOIN transaction_lines l ON l.account_id = a.id
      WHERE a.business_id = ? AND a.is_active = 1
      GROUP BY a.type
    ");
    $stmt->execute([$businessId]);
    while ($row = $stmt->fetch()) {
        $lines[] = "- Akun (" . $row['type'] . "): " . $row['cnt'] . " akun, saldo: " . number_format($row['balance'], 0, ',', '.');
    }

    // Transaksi 30 hari terakhir
    $stmt = $pdo->prepare("
      SELECT COUNT(*) as cnt
      FROM transactions t
      WHERE t.business_id = ? AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$businessId]);
    $tx = $stmt->fetch();
    $lines[] = "- Transaksi 30 hari terakhir: " . $tx['cnt'] . " transaksi";

    // Faktur penjualan per status
    $stmt = $pdo->prepare("
      SELECT status, COUNT(*) as cnt, COALESCE(SUM(total),0) as total
      FROM sales_invoices WHERE business_id = ? GROUP BY status
    ");
    $stmt->execute([$businessId]);
    $invParts = [];
    while ($row = $stmt->fetch()) {
        $invParts[] = $row['status'] . ": " . $row['cnt'] . " (" . number_format($row['total'], 0, ',', '.') . ")";
    }
    $lines[] = "- Faktur penjualan: " . (empty($invParts) ? '-' : implode('; ', $invParts));

    // Faktur pembelian per status
    $stmt = $pdo->prepare("
      SELECT status, COUNT(*) as cnt, COALESCE(SUM(total),0) as total
      FROM purchase_invoices WHERE business_id = ? GROUP BY status
    ");
    $stmt->execute([$businessId]);
    $purParts = [];
    while ($row = $stmt->fetch()) {
        $purParts[] = $row['status'] . ": " . $row['cnt'] . " (" . number_format($row['total'], 0, ',', '.') . ")";
    }
    $lines[] = "- Faktur pembelian: " . (empty($purParts) ? '-' : implode('; ', $purParts));

    // Jumlah data master
    foreach (['customers', 'suppliers', 'items', 'bank_accounts'] as $tbl) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM $tbl WHERE business_id = ?");
        $stmt->execute([$businessId]);
        $c = $stmt->fetch();
        $lines[] = "- $tbl: " . $c['cnt'] . " data";
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
