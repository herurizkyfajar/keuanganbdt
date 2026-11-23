<?php
require_once __DIR__ . '/config/database.php';

// Batch 10 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-84', 'date' => '2026-10-29', 'acc360' => 63, 'dr360' => 3800000, 'cr360' => 0, 'desc360' => 'SEWA DRIVER DAN MOBIL', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 3800000, 'desc_offset' => 'SEWA DRIVER DAN MOBIL'],
    ['ref' => 'JE-RE-82', 'date' => '2026-10-29', 'acc360' => 63, 'dr360' => 0, 'cr360' => 3800000, 'desc360' => 'PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 3800000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia'],
    ['ref' => 'JE-RE-81', 'date' => '2026-10-28', 'acc360' => 63, 'dr360' => 0, 'cr360' => 200000, 'desc360' => 'DP. PEMASUKAN BPN Rachmat Wilianto INV-20261028-00405 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 200000, 'cr_offset' => 0, 'desc_offset' => 'DP. PEMASUKAN BPN Rachmat Wilianto INV-20261028-00405 INDONESIA'],
    ['ref' => 'JE-RE-79', 'date' => '2026-10-27', 'acc360' => 63, 'dr360' => 0, 'cr360' => 300000, 'desc360' => 'PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 300000, 'cr_offset' => 0, 'desc_offset' => 'PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA'],
    ['ref' => 'JE-RE-78', 'date' => '2026-10-27', 'acc360' => 63, 'dr360' => 2500000, 'cr360' => 0, 'desc360' => 'SEWA DRIVER & MOBIL HIACE Vendy Andreas INV-20261026-00403 INDONESIA', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 2500000, 'desc_offset' => 'SEWA DRIVER & MOBIL HIACE Vendy Andreas INV-20261026-00403 INDONESIA'],
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
    echo "\nBatch 10 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
