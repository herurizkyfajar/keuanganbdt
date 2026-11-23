<?php
require_once __DIR__ . '/config/database.php';

// Batch 16 of Retained earnings (account 360) entries
$entries = [
    ['ref' => 'JE-RE-41', 'date' => '2026-10-16', 'acc360' => 63, 'dr360' => 4950, 'cr360' => 0, 'desc360' => 'ADMIN QR', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 4950, 'desc_offset' => 'ADMIN QR'],
    ['ref' => 'JE-RE-33', 'date' => '2026-10-15', 'acc360' => 63, 'dr360' => 200000, 'cr360' => 0, 'desc360' => 'OPRASIONAL FERDI', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 200000, 'desc_offset' => 'OPRASIONAL FERDI'],
    ['ref' => 'JE-RE-42', 'date' => '2026-10-15', 'acc360' => 63, 'dr360' => 0, 'cr360' => 200000, 'desc360' => 'PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany', 'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 200000, 'cr_offset' => 0, 'desc_offset' => 'PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany'],
    ['ref' => 'JE-RE-35', 'date' => '2026-10-15', 'acc360' => 63, 'dr360' => 1000, 'cr360' => 0, 'desc360' => 'ADMIN GOPAY', 'offset_code' => '610', 'offset_name' => 'Biaya Administrasi Bank', 'dr_offset' => 0, 'cr_offset' => 1000, 'desc_offset' => 'ADMIN GOPAY'],
    ['ref' => 'JE-RE-34', 'date' => '2026-10-15', 'acc360' => 63, 'dr360' => 450000, 'cr360' => 0, 'desc360' => 'BAYAR MOBIL DAN SUPIR', 'offset_code' => '617', 'offset_name' => 'Biaya Sewa Mobil dan Supir', 'dr_offset' => 0, 'cr_offset' => 450000, 'desc_offset' => 'BAYAR MOBIL DAN SUPIR'],
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
    echo "\nBatch 16 committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = 63");
    $bal = $stmt->fetchColumn();
    echo "Account 360 balance (debit-credit): " . number_format($bal, 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
