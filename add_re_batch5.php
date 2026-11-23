<?php
require_once __DIR__ . '/config/database.php';

// Batch 5 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-117', 'date' => '2026-11-08', 'acc360' => 63, 'dr360' => 0, 'cr360' => 1000000, 'desc360' => 'DP Yunita widarani INV-20261104-00414 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 1000000, 'cr_offset' => 0, 'desc_offset' => 'DP Yunita widarani INV-20261104-00414 INDONESIA'],
    ['ref' => 'JE-RE-115', 'date' => '2026-11-07', 'acc360' => 63, 'dr360' => 1000, 'cr360' => 0, 'desc360' => 'ADMIN TF GOPAY ARDI', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 1000, 'desc_offset' => 'ADMIN TF GOPAY ARDI'],
    ['ref' => 'JE-RE-114', 'date' => '2026-11-07', 'acc360' => 63, 'dr360' => 500000, 'cr360' => 0, 'desc360' => 'ARDI', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 500000, 'desc_offset' => 'ARDI'],
    ['ref' => 'JE-RE-113', 'date' => '2026-11-07', 'acc360' => 63, 'dr360' => 1200000, 'cr360' => 0, 'desc360' => 'RULLY', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 1200000, 'desc_offset' => 'RULLY'],
    ['ref' => 'JE-RE-111', 'date' => '2026-11-06', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium'],
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
    echo "\nBatch 5 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
