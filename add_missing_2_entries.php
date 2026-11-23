<?php
require_once __DIR__ . '/config/database.php';

// Add 2 missing entries from the user's table (no JE-RE reference number)
// Entry A: 14/11/2026, PENDAPETAN Maelen Smith, Cr 100.000 to account 360
// Entry B: 24/10/2026, Biaya BRC FEE BRC, Dr 50.000 to account 360

$entries = [
    ['ref' => 'JE-RE-MAelen', 'date' => '2026-11-14', 'acc360' => 63, 'dr360' => 0, 'cr360' => 100000,
     'desc360' => 'PENDAPETAN Maelen Smith INV-20261113-00426 Australia',
     'offset_code' => '400', 'offset_name' => 'Pendapatan', 'dr_offset' => 100000, 'cr_offset' => 0,
     'desc_offset' => 'PENDAPETAN Maelen Smith INV-20261113-00426 Australia'],
    ['ref' => 'JE-RE-FEE24', 'date' => '2026-10-24', 'acc360' => 63, 'dr360' => 50000, 'cr360' => 0,
     'desc360' => 'FEE BRC',
     'offset_code' => '631', 'offset_name' => 'Biaya BRC', 'dr_offset' => 0, 'cr_offset' => 50000,
     'desc_offset' => 'FEE BRC'],
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
    echo "\nMissing entries committed.\n";

    $stmt = $pdo->query("SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal_dr_cr, COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) AS bal_cr_dr FROM transaction_lines WHERE account_id = 63");
    $r = $stmt->fetch();
    echo "Account 360: " . number_format($r['bal_dr_cr'], 2, ',', '.') . " Dr | " . number_format($r['bal_cr_dr'], 2, ',', '.') . " Cr\n";
    echo "Account 360 lines: ";
    $stmt = $pdo->query("SELECT COUNT(*) FROM transaction_lines WHERE account_id = 63");
    echo $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
