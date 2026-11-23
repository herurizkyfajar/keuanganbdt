<?php
require_once __DIR__ . '/config/database.php';

// Batch 17 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-32', 'date' => '2026-10-14', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN Olivier vis INV-20261012-00387 Netherlands', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Olivier vis INV-20261012-00387 Netherlands'],
    ['ref' => 'JE-RE-31', 'date' => '2026-10-14', 'acc360' => 63, 'dr360' => 0, 'cr360' => 400000, 'desc360' => 'PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 400000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia'],
    ['ref' => 'JE-RE-30', 'date' => '2026-10-13', 'acc360' => 63, 'dr360' => 150000, 'cr360' => 0, 'desc360' => 'BAYAR AI', 'offset_code' => '621', 'offset_name' => 'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB', 'dr_offset' => 0, 'cr_offset' => 150000, 'desc_offset' => 'BAYAR AI'],
    ['ref' => 'JE-RE-29', 'date' => '2026-10-13', 'acc360' => 63, 'dr360' => 100000, 'cr360' => 0, 'desc360' => 'HERU PERPANJANG N8N', 'offset_code' => '624', 'offset_name' => 'Biaya Perpanjangan n8n', 'dr_offset' => 0, 'cr_offset' => 100000, 'desc_offset' => 'HERU PERPANJANG N8N'],
    ['ref' => 'JE-RE-25', 'date' => '2026-10-11', 'acc360' => 63, 'dr360' => 500000, 'cr360' => 0, 'desc360' => 'BAYAR ADS', 'offset_code' => '621', 'offset_name' => 'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB', 'dr_offset' => 0, 'cr_offset' => 500000, 'desc_offset' => 'BAYAR ADS'],
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
    echo "\nBatch 17 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
