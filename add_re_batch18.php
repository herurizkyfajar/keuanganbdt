<?php
require_once __DIR__ . '/config/database.php';

// Batch 18 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-24', 'date' => '2026-10-10', 'acc360' => 63, 'dr360' => 1000, 'cr360' => 0, 'desc360' => 'ADMIN GOPAY ANDRE ETOLL', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 1000, 'desc_offset' => 'ADMIN GOPAY ANDRE ETOLL'],
    ['ref' => 'JE-RE-17', 'date' => '2026-10-08', 'acc360' => 63, 'dr360' => 0, 'cr360' => 75000, 'desc360' => 'pemasukan Tanti INV-20261007-00381 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 75000, 'cr_offset' => 0, 'desc_offset' => 'pemasukan Tanti INV-20261007-00381 INDONESIA'],
    ['ref' => 'JE-RE-19', 'date' => '2026-10-08', 'acc360' => 63, 'dr360' => 1000000, 'cr360' => 0, 'desc360' => 'gaji andre', 'offset_code' => '628', 'offset_name' => 'Biaya Gaji', 'dr_offset' => 0, 'cr_offset' => 1000000, 'desc_offset' => 'gaji andre'],
    ['ref' => 'JE-RE-20', 'date' => '2026-10-08', 'acc360' => 63, 'dr360' => 1000000, 'cr360' => 0, 'desc360' => 'gaji heru', 'offset_code' => '628', 'offset_name' => 'Biaya Gaji', 'dr_offset' => 0, 'cr_offset' => 1000000, 'desc_offset' => 'gaji heru'],
    ['ref' => 'JE-RE-16', 'date' => '2026-10-08', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000, 'desc360' => 'pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0, 'desc_offset' => 'pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia'],
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
    echo "\nBatch 18 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
