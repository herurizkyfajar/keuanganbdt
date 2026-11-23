<?php
require_once __DIR__ . '/config/database.php';

// Fix account 134 (id=56): flip 8 entries from Debit to Credit
$fixes = [
    3364 => ['ref' => 'JE-3010', 'dr' => 0, 'cr' => 600000],
    3365 => ['ref' => 'JE-75', 'dr' => 0, 'cr' => 600000],
    3367 => ['ref' => 'JE-57', 'dr' => 0, 'cr' => 200000],
    3368 => ['ref' => 'JE-51', 'dr' => 0, 'cr' => 250000],
    3371 => ['ref' => 'JE-46', 'dr' => 0, 'cr' => 250000],
    3377 => ['ref' => 'JE-14', 'dr' => 0, 'cr' => 200000],
    3378 => ['ref' => 'JE-13', 'dr' => 0, 'cr' => 200000],
    3374 => ['ref' => 'JE-26', 'dr' => 0, 'cr' => 50000],
];

$accId = 56;
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        UPDATE transaction_lines
        SET debit = ?, credit = ?
        WHERE id = ? AND account_id = ?
    ");

    foreach ($fixes as $lineId => $f) {
        $stmt->execute([$f['dr'], $f['cr'], $lineId, $accId]);
        echo "Line #{$lineId} ({$f['ref']}): DR=0 CR=" . number_format($f['cr'],0) . "\n";
    }

    $pdo->commit();
    echo "\nFixed 8 entries.\n";

    // Verify
    $stmt = $pdo->query("
        SELECT COUNT(*) AS n,
               COALESCE(SUM(debit),0) AS total_dr,
               COALESCE(SUM(credit),0) AS total_cr,
               COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS balance
        FROM transaction_lines WHERE account_id = $accId
    ");
    $r = $stmt->fetch();
    echo "Lines: {$r['n']} | Total DR: " . number_format($r['total_dr'],0) . " | Total CR: " . number_format($r['total_cr'],0) . " | Balance: " . number_format($r['balance'], 2, ',', '.') . " Dr\n";
    echo "Expected balance: 1.200.000 Dr\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
