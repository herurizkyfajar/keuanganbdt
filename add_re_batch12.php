<?php
require_once __DIR__ . '/config/database.php';

// Batch 12 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-65', 'date' => '2026-10-23', 'acc360' => 63, 'dr360' => 0, 'cr360' => 700000, 'desc360' => 'PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 700000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA'],
    ['ref' => 'JE-RE-67', 'date' => '2026-10-23', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary'],
    ['ref' => 'JE-RE-64', 'date' => '2026-10-23', 'acc360' => 63, 'dr360' => 100000, 'cr360' => 0, 'desc360' => 'SHARE BRC', 'offset_code' => '631', 'offset_name' => 'Biaya BRC', 'dr_offset' => 0, 'cr_offset' => 100000, 'desc_offset' => 'SHARE BRC'],
    ['ref' => 'JE-RE-66', 'date' => '2026-10-23', 'acc360' => 63, 'dr360' => 2100, 'cr360' => 0, 'desc360' => 'ADMIN QR', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 2100, 'desc_offset' => 'ADMIN QR'],
    ['ref' => 'JE-RE-63', 'date' => '2026-10-23', 'acc360' => 63, 'dr360' => 550000, 'cr360' => 0, 'desc360' => 'SEWA DRIVER DAN MOBIL', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 550000, 'desc_offset' => 'SEWA DRIVER DAN MOBIL'],
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
    echo "\nBatch 12 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
