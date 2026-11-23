<?php
require_once __DIR__ . '/config/database.php';

// Batch 2 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-135', 'date' => '2026-11-13', 'acc360' => 63, 'dr360' => 0, 'cr360' => 75000, 'desc360' => 'PENDAPETAN Christina INV-20261113-00425 South Korea', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 75000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Christina INV-20261113-00425 South Korea'],
    ['ref' => 'JE-RE-134', 'date' => '2026-11-12', 'acc360' => 63, 'dr360' => 0, 'cr360' => 300000, 'desc360' => 'PENDAPETAN Nasser INV-20261110-00418 Pakistan', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 300000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Nasser INV-20261110-00418 Pakistan'],
    ['ref' => 'JE-RE-131', 'date' => '2026-11-12', 'acc360' => 63, 'dr360' => 160825, 'cr360' => 0, 'desc360' => 'SUNTIK FOLLOWERS IG', 'offset_code' => '621', 'offset_name' => 'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB', 'dr_offset' => 0, 'cr_offset' => 160825, 'desc_offset' => 'SUNTIK FOLLOWERS IG'],
    ['ref' => 'JE-RE-133', 'date' => '2026-11-12', 'acc360' => 63, 'dr360' => 0, 'cr360' => 75000, 'desc360' => 'PENDAPETAN Dali Schmölzer INV-20261110-00420 Netherlands', 'offset_code' => '623', 'offset_name' => 'Pendapetan Pengelolaan Web', 'dr_offset' => 75000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Dali Schmölzer INV-20261110-00420 Netherlands'],
    ['ref' => 'JE-RE-129', 'date' => '2026-11-11', 'acc360' => 63, 'dr360' => 120619, 'cr360' => 0, 'desc360' => 'SUNTIK FOLLOWERS', 'offset_code' => '621', 'offset_name' => 'Biaya Iklan, Promosi, ADS, AI, BACKUP WEB', 'dr_offset' => 0, 'cr_offset' => 120619, 'desc_offset' => 'SUNTIK FOLLOWERS'],
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
    echo "\nBatch 2 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
