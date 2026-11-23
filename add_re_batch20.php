<?php
require_once __DIR__ . '/config/database.php';

// Final batch of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-7', 'date' => '2026-10-04', 'acc360' => 63, 'dr360' => 14000, 'cr360' => 0, 'desc360' => 'admin bank bca', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 14000, 'desc_offset' => 'admin bank bca'],
    ['ref' => 'JE-RE-4', 'date' => '2026-10-04', 'acc360' => 63, 'dr360' => 0, 'cr360' => 1903671, 'desc360' => 'pemasukan jasa agensi', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 1903671, 'cr_offset' => 0, 'desc_offset' => 'pemasukan jasa agensi'],
    ['ref' => 'JE-RE-5', 'date' => '2026-10-04', 'acc360' => 63, 'dr360' => 0, 'cr360' => 9277111, 'desc360' => 'kas saat ini', 'offset_code' => '120', 'offset_name' => 'Bank BCA', 'dr_offset' => 9277111, 'cr_offset' => 0, 'desc_offset' => 'kas saat ini'],
];

$businessId = 1;
$pdo->beginTransaction();

try {
    $accMap = [];
    $stmt = $pdo->query("SELECT id, code FROM accounts WHERE is_active = 1");
    foreach ($stmt->fetchAll() as $r) {
        $accMap[$r['code']] = (int)$r['id'];
    }

    foreach ($entries as $e) {
        $offsetId = $accMap[$e['offset_code']] ?? null;
        if (!$offsetId) {
            throw new Exception("Offset account {$e['offset_code']} not found");
        }

        $stmt = $pdo->prepare("
            INSERT INTO transactions (business_id, reference, description, transaction_date, type)
            VALUES (?, ?, ?, ?, 'journal')
        ");
        $stmt->execute([$businessId, $e['ref'], $e['desc360'], $e['date']]);
        $txId = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$txId, $e['acc360'], $e['desc360'], $e['dr360'], $e['cr360']]);
        $stmt->execute([$txId, $offsetId, $e['desc_offset'], $e['dr_offset'], $e['cr_offset']]);

        echo "Created tx #{$txId} ({$e['ref']}): 360 DR=" . number_format($e['dr360'],0) . " CR=" . number_format($e['cr360'],0) . " | {$e['offset_code']} DR=" . number_format($e['dr_offset'],0) . " CR=" . number_format($e['cr_offset'],0) . "\n";
    }

    $pdo->commit();
    echo "\nFinal batch committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
    echo "Account 360 balance (credit-debit): " . number_format(-$bal, 2, ',', '.') . " Cr\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
