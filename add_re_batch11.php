<?php
require_once __DIR__ . '/config/database.php';

// Batch 11 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-80', 'date' => '2026-10-27', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico'],
    ['ref' => 'JE-RE-76', 'date' => '2026-10-26', 'acc360' => 63, 'dr360' => 0, 'cr360' => 2500000, 'desc360' => 'PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 2500000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA'],
    ['ref' => 'JE-RE-70', 'date' => '2026-10-24', 'acc360' => 63, 'dr360' => 3300, 'cr360' => 0, 'desc360' => 'ADMIN QR', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 3300, 'desc_offset' => 'ADMIN QR'],
    ['ref' => 'JE-RE-68', 'date' => '2026-10-24', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN fei INV-20261019-00394 China', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN fei INV-20261019-00394 China'],
    ['ref' => 'JE-RE-69', 'date' => '2026-10-24', 'acc360' => 63, 'dr360' => 0, 'cr360' => 1100000, 'desc360' => 'ANDRE ""IKUT TRANSFER""', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 1100000, 'cr_offset' => 0, 'desc_offset' => 'ANDRE ""IKUT TRANSFER""'],
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
    echo "\nBatch 11 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
