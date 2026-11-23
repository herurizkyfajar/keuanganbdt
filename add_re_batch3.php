<?php
require_once __DIR__ . '/config/database.php';

// Batch 3 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-130', 'date' => '2026-11-11', 'acc360' => 63, 'dr360' => 15000, 'cr360' => 0, 'desc360' => 'PHOTOCOPY DAN SCAN SURAT KESEPATAN', 'offset_code' => '613', 'offset_name' => 'Biaya Percepatan dan Alat Tulis Kantor', 'dr_offset' => 0, 'cr_offset' => 15000, 'desc_offset' => 'PHOTOCOPY DAN SCAN SURAT KESEPATAN'],
    ['ref' => 'JE-RE-127', 'date' => '2026-11-10', 'acc360' => 63, 'dr360' => 120619, 'cr360' => 0, 'desc360' => 'PEMBELIAN FOLLOWERS IG', 'offset_code' => '621', 'offset_name' => 'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB', 'dr_offset' => 0, 'cr_offset' => 120619, 'desc_offset' => 'PEMBELIAN FOLLOWERS IG'],
    ['ref' => 'JE-RE-128', 'date' => '2026-11-10', 'acc360' => 63, 'dr360' => 120000, 'cr360' => 0, 'desc360' => 'BIAYA MEET', 'offset_code' => '629', 'offset_name' => 'Biaya Rapat', 'dr_offset' => 0, 'cr_offset' => 120000, 'desc_offset' => 'BIAYA MEET'],
    ['ref' => 'JE-RE-126', 'date' => '2026-11-10', 'acc360' => 63, 'dr360' => 2772180, 'cr360' => 0, 'desc360' => 'PEMBELIAN ASET TABLET', 'offset_code' => '625', 'offset_name' => 'Biaya Pembelian Aset', 'dr_offset' => 0, 'cr_offset' => 2772180, 'desc_offset' => 'PEMBELIAN ASET TABLET'],
    ['ref' => 'JE-RE-124', 'date' => '2026-11-10', 'acc360' => 63, 'dr360' => 100000, 'cr360' => 0, 'desc360' => 'FEE BRC', 'offset_code' => '631', 'offset_name' => 'Biaya BRC', 'dr_offset' => 0, 'cr_offset' => 100000, 'desc_offset' => 'FEE BRC'],
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
    echo "\nBatch 3 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
