<?php
require_once __DIR__ . '/config/database.php';

// Batch 7 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-101', 'date' => '2026-11-02', 'acc360' => 63, 'dr360' => 0, 'cr360' => 800000, 'desc360' => 'PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 800000, 'cr_offset' => 0, 'desc_offset' => 'PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA'],
    ['ref' => 'JE-RE-102', 'date' => '2026-11-02', 'acc360' => 63, 'dr360' => 600000, 'cr360' => 0, 'desc360' => 'DENI NUR CAHYA SEWA DRIVER DAN MOBIL', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 600000, 'desc_offset' => 'DENI NUR CAHYA SEWA DRIVER DAN MOBIL'],
    ['ref' => 'JE-RE-97', 'date' => '2026-11-01', 'acc360' => 63, 'dr360' => 115500, 'cr360' => 0, 'desc360' => 'KONSUMSI MEET/RAPAT', 'offset_code' => '629', 'offset_name' => 'Biaya Rapat', 'dr_offset' => 0, 'cr_offset' => 115500, 'desc_offset' => 'KONSUMSI MEET/RAPAT'],
    ['ref' => 'JE-RE-95', 'date' => '2026-11-01', 'acc360' => 63, 'dr360' => 1000000, 'cr360' => 0, 'desc360' => 'GAJI HERU', 'offset_code' => '628', 'offset_name' => 'Biaya Gaji', 'dr_offset' => 0, 'cr_offset' => 1000000, 'desc_offset' => 'GAJI HERU'],
    ['ref' => 'JE-RE-93', 'date' => '2026-11-01', 'acc360' => 63, 'dr360' => 1000000, 'cr360' => 0, 'desc360' => 'GAJI ALDI', 'offset_code' => '628', 'offset_name' => 'Biaya Gaji', 'dr_offset' => 0, 'cr_offset' => 1000000, 'desc_offset' => 'GAJI ALDI'],
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
    echo "\nBatch 7 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
