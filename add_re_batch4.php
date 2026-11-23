<?php
require_once __DIR__ . '/config/database.php';

// Batch 4 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-119', 'date' => '2026-11-09', 'acc360' => 63, 'dr360' => 0, 'cr360' => 2400000, 'desc360' => '', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 2400000, 'cr_offset' => 0, 'desc_offset' => ''],
    ['ref' => 'JE-RE-122', 'date' => '2026-11-09', 'acc360' => 63, 'dr360' => 0, 'cr360' => 150000, 'desc360' => 'PENDAPETAN Aswen INV-20261108-00416 Malaysia', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 150000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPETAN Aswen INV-20261108-00416 Malaysia'],
    ['ref' => 'JE-RE-120', 'date' => '2026-11-09', 'acc360' => 63, 'dr360' => 7200, 'cr360' => 0, 'desc360' => 'ADMIN QR', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 7200, 'desc_offset' => 'ADMIN QR'],
    ['ref' => 'JE-RE-121', 'date' => '2026-11-09', 'acc360' => 63, 'dr360' => 1300000, 'cr360' => 0, 'desc360' => 'SEWA DRIVER DAN MOBIL', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 1300000, 'desc_offset' => 'SEWA DRIVER DAN MOBIL'],
    ['ref' => 'JE-RE-118', 'date' => '2026-11-08', 'acc360' => 63, 'dr360' => 3000, 'cr360' => 0, 'desc360' => 'ADMIN QR', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 3000, 'desc_offset' => 'ADMIN QR'],
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
    echo "\nBatch 4 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
