<?php
require_once __DIR__ . '/config/database.php';

// Batch 13 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-58', 'date' => '2026-10-22', 'acc360' => 63, 'dr360' => 0, 'cr360' => 1500000, 'desc360' => 'PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA ""KOTOR""', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 1500000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA ""KOTOR""'],
    ['ref' => 'JE-RE-59', 'date' => '2026-10-22', 'acc360' => 63, 'dr360' => 0, 'cr360' => 50000, 'desc360' => 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 50000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh'],
    ['ref' => 'JE-RE-62', 'date' => '2026-10-22', 'acc360' => 63, 'dr360' => 0, 'cr360' => 50000, 'desc360' => 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 50000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh'],
    ['ref' => 'JE-RE-60', 'date' => '2026-10-20', 'acc360' => 63, 'dr360' => 0, 'cr360' => 6141, 'desc360' => 'INTERST ON ACCOUNT', 'offset_code' => '611', 'offset_name' => 'Bunga diterima', 'dr_offset' => 6141, 'cr_offset' => 0, 'desc_offset' => 'INTERST ON ACCOUNT'],
    ['ref' => 'JE-RE-61', 'date' => '2026-10-20', 'acc360' => 63, 'dr360' => 1228, 'cr360' => 0, 'desc360' => 'PENGELUARAN TAX', 'offset_code' => '630', 'offset_name' => 'Tax', 'dr_offset' => 0, 'cr_offset' => 1228, 'desc_offset' => 'PENGELUARAN TAX'],
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
    echo "\nBatch 13 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
