<?php
require_once __DIR__ . '/config/database.php';

// Batch 9 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-89', 'date' => '2026-10-30', 'acc360' => 63, 'dr360' => 0, 'cr360' => 1600000, 'desc360' => 'PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 1600000, 'cr_offset' => 0, 'desc_offset' => 'PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN'],
    ['ref' => 'JE-RE-85', 'date' => '2026-10-29', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore'],
    ['ref' => 'JE-RE-86', 'date' => '2026-10-29', 'acc360' => 63, 'dr360' => 0, 'cr360' => 200000, 'desc360' => 'PEMASUKAN Muller Cecilia INV-20261028-00406 France', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 200000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Muller Cecilia INV-20261028-00406 France'],
    ['ref' => 'JE-RE-83', 'date' => '2026-10-29', 'acc360' => 63, 'dr360' => 11400, 'cr360' => 0, 'desc360' => 'ADMIN QR', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 11400, 'desc_offset' => 'ADMIN QR'],
    ['ref' => 'JE-RE-87', 'date' => '2026-10-29', 'acc360' => 63, 'dr360' => 0, 'cr360' => 300000, 'desc360' => 'PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 300000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI'],
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
    echo "\nBatch 9 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
