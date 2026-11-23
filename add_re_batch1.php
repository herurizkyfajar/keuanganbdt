<?php
require_once __DIR__ . '/config/database.php';

// Batch 1 of Retained earnings (account 360) entries
// Each entry: one line to account 360, one offset line to the account named in "Account" column
$entries = [
    ['ref' => 'JE-RE-144', 'date' => '2026-11-17', 'acc360' => 63, 'acc360_name' => 'Retained earnings', 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia'],
    ['ref' => 'JE-RE-140', 'date' => '2026-11-16', 'acc360' => 63, 'acc360_name' => 'Retained earnings', 'dr360' => 0, 'cr360' => 500000, 'desc360' => 'DP PENDAPETAN Azizah INV-20261116-00427 Indonesia', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 500000, 'cr_offset' => 0, 'desc_offset' => 'DP PENDAPETAN Azizah INV-20261116-00427 Indonesia'],
    ['ref' => 'JE-RE-136', 'date' => '2026-11-14', 'acc360' => 63, 'acc360_name' => 'Retained earnings', 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan'],
    ['ref' => 'JE-RE-137', 'date' => '2026-11-14', 'acc360' => 63, 'acc360_name' => 'Retained earnings', 'dr360' => 2000000, 'cr360' => 0, 'desc360' => 'SEWA DRIVER DAN MOBIL', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 2000000, 'desc_offset' => 'SEWA DRIVER DAN MOBIL'],
    ['ref' => 'JE-RE-138', 'date' => '2026-11-14', 'acc360' => 63, 'acc360_name' => 'Retained earnings', 'dr360' => 0, 'cr360' => 200000, 'desc360' => 'PENDAPETAN Kiran Kale INV-20261103-00412 India', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 200000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Kiran Kale INV-20261103-00412 India'],
];

$businessId = 1;
$pdo->beginTransaction();

try {
    // Build account code -> id map
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

        // Line to account 360
        $stmt = $pdo->prepare("
            INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$txId, $e['acc360'], $e['desc360'], $e['dr360'], $e['cr360']]);

        // Offset line
        $stmt->execute([$txId, $offsetId, $e['desc_offset'], $e['dr_offset'], $e['cr_offset']]);

        echo "Created tx #{$txId} ({$e['ref']}): 360 DR=" . number_format($e['dr360'],0) . " CR=" . number_format($e['cr360'],0) . " | {$e['offset_code']} DR=" . number_format($e['dr_offset'],0) . " CR=" . number_format($e['cr_offset'],0) . "\n";
    }

    $pdo->commit();
    echo "\nBatch 1 committed.\n";

    // Verify account 360 balance
    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance: " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
