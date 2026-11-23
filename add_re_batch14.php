<?php
require_once __DIR__ . '/config/database.php';

// Batch 14 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-55', 'date' => '2026-10-20', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2'],
    ['ref' => 'JE-RE-52', 'date' => '2026-10-19', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN Daniel Martak INV-20261017-00393 France', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Daniel Martak INV-20261017-00393 France'],
    ['ref' => 'JE-RE-54', 'date' => '2026-10-19', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1'],
    ['ref' => 'JE-RE-49', 'date' => '2026-10-18', 'acc360' => 63, 'dr360' => 0, 'cr360' => 50000, 'desc360' => 'PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 50000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA'],
    ['ref' => 'JE-RE-48', 'date' => '2026-10-17', 'acc360' => 63, 'dr360' => 1250000, 'cr360' => 0, 'desc360' => 'PEMBAYARAN BACK UP DATA WEBSITE', 'offset_code' => '621', 'offset_name' => 'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB', 'dr_offset' => 0, 'cr_offset' => 1250000, 'desc_offset' => 'PEMBAYARAN BACK UP DATA WEBSITE'],
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
    echo "\nBatch 14 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
