<?php
// api/ai-chat-history.php — Riwayat percakapan AI per user & per sesi (24 jam)
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

$requestedSessionId = $_GET['session_id'] ?? null;

// Ambil semua sesi user ini di bisnis ini, dengan aktivitas terakhir
$stmt = $pdo->prepare("
    SELECT session_id,
           MAX(created_at) as last_activity,
           COUNT(*) as message_count,
           MIN(created_at) as started_at
    FROM ai_chat_messages
    WHERE business_id = ? AND user_id = ? AND session_id IS NOT NULL
    GROUP BY session_id
    ORDER BY last_activity DESC
");
$stmt->execute([$businessId, $userId]);
$sessions = $stmt->fetchAll();

$now = new DateTime();
$activeSession = null;   // aktivitas dalam 24 jam terakhir
$expiredSession = null;  // sesi terbaru yang sudah > 24 jam

foreach ($sessions as $s) {
    $last = new DateTime($s['last_activity']);
    $diff = (int)$now->getTimestamp() - (int)$last->getTimestamp();
    if ($diff <= 86400 && !$activeSession) {
        $activeSession = $s;
    } elseif (!$expiredSession) {
        $expiredSession = $s;
    }
}

// Tentukan sesi yang akan dimuat
$loadSessionId = $requestedSessionId;
if (!$loadSessionId && $activeSession) {
    $loadSessionId = $activeSession['session_id'];
}

$messages = [];
$status = 'new'; // new | active | expired
if ($activeSession) {
    $status = 'active';
} elseif ($expiredSession) {
    $status = 'expired';
}

if ($loadSessionId) {
    // Validasi sesi milik user & bisnis ini
    $stmt = $pdo->prepare("SELECT 1 FROM ai_chat_messages WHERE business_id=? AND user_id=? AND session_id=? LIMIT 1");
    $stmt->execute([$businessId, $userId, $loadSessionId]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("
            SELECT id, role, content, created_at, session_id
            FROM ai_chat_messages
            WHERE business_id = ? AND user_id = ? AND session_id = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$businessId, $userId, $loadSessionId]);
        $messages = $stmt->fetchAll();
    } else {
        $loadSessionId = null;
    }
}

echo json_encode([
    'status' => $status,
    'current_session_id' => $loadSessionId,
    'active_session' => $activeSession,
    'expired_session' => $expiredSession,
    'messages' => $messages
]);
exit;
