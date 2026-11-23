<?php
require_once __DIR__ . '/config/database.php';

// Batch 8 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-94', 'date' => '2026-11-01', 'acc360' => 63, 'dr360' => 1000000, 'cr360' => 0, 'desc360' => 'GAJI ANDRE', 'offset_code' => '628', 'offset_name' => 'Biaya Gaji', 'dr_offset' => 0, 'cr_offset' => 1000000, 'desc_offset' => 'GAJI ANDRE'],
    ['ref' => 'JE-RE-96', 'date' => '2026-11-01', 'acc360' => 63, 'dr360' => 13000, 'cr360' => 0, 'desc360' => 'ADMIN BANK', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 13000, 'desc_offset' => 'ADMIN BANK'],
    ['ref' => 'JE-RE-91', 'date' => '2026-10-30', 'acc360' => 63, 'dr360' => 1400000, 'cr360' => 0, 'desc360' => 'BAYAR SEWA DRIVER DAN MOBIL', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 1400000, 'desc_offset' => 'BAYAR SEWA DRIVER DAN MOBIL'],
    ['ref' => 'JE-RE-92', 'date' => '2026-10-30', 'acc360' => 63, 'dr360' => 200000, 'cr360' => 0, 'desc360' => 'FEE BRC', 'offset_code' => '631', 'offset_name' => 'Biaya BRC', 'dr_offset' => 0, 'cr_offset' => 200000, 'desc_offset' => 'FEE BRC'],
    ['ref' => 'JE-RE-90', 'date' => '2026-10-30', 'acc360' => 63, 'dr360' => 4800, 'cr360' => 0, 'desc360' => 'ADMIN QR', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 4800, 'desc_offset' => 'ADMIN QR'],
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
    echo "\nBatch 8 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
