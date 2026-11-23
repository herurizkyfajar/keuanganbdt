<?php
require_once __DIR__ . '/config/database.php';

// Batch 6 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-112', 'date' => '2026-11-06', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA'],
    ['ref' => 'JE-RE-110', 'date' => '2026-11-06', 'acc360' => 63, 'dr360' => 150000, 'cr360' => 0, 'desc360' => 'PERPANJANG AI', 'offset_code' => '621', 'offset_name' => 'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB', 'dr_offset' => 0, 'cr_offset' => 150000, 'desc_offset' => 'PERPANJANG AI'],
    ['ref' => 'JE-RE-109', 'date' => '2026-11-06', 'acc360' => 63, 'dr360' => 100000, 'cr360' => 0, 'desc360' => 'PERPANJANG N8N', 'offset_code' => '624', 'offset_name' => 'Biaya Perpanjangan n8n', 'dr_offset' => 0, 'cr_offset' => 100000, 'desc_offset' => 'PERPANJANG N8N'],
    ['ref' => 'JE-RE-106', 'date' => '2026-11-04', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN ANDRE', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN ANDRE'],
    ['ref' => 'JE-RE-107', 'date' => '2026-11-04', 'acc360' => 63, 'dr360' => 14000, 'cr360' => 0, 'desc360' => 'ADMIN BANK', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 14000, 'desc_offset' => 'ADMIN BANK'],
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
    echo "\nBatch 6 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
