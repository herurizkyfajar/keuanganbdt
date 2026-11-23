<?php
require_once __DIR__ . '/config/database.php';

// Add JE-46 line to account 360: Debit 250.000
// Transaction 441 already exists with JE-46 reference
// Add account 360 Debit 250.000, and balance with Credit 250.000 to account 56 (Piutang Operasional)

$txId = 441;
$desc = 'GANTI OPRASIONAL';
$amount = 250000;
$acc360 = 63;
$accOffset = 56; // Piutang Operasional

$pdo->beginTransaction();

try {
    // Add line to account 360: Debit 250.000
    $stmt = $pdo->prepare("
        INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
        VALUES (?, ?, ?, ?, 0)
    ");
    $stmt->execute([$txId, $acc360, $desc, $amount]);

    // Balance: Credit 250.000 to account 56
    $stmt = $pdo->prepare("
        INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
        VALUES (?, ?, ?, 0, ?)
    ");
    $stmt->execute([$txId, $accOffset, $desc, $amount]);

    $pdo->commit();
    echo "Added to tx #{$txId} (JE-46): 360 DR {$amount} | 56 CR {$amount}\n";

    // Verify
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal_dr_cr,
               COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) AS bal_cr_dr
        FROM transaction_lines WHERE account_id = 63
    ");
    $r = $stmt->fetch();
    echo "Account 360: " . number_format($r['bal_cr_dr'], 2, ',', '.') . " Cr\n";
    echo "Expected: 4.592.202,00 Cr\n";
    echo "Difference: " . number_format(4592202 - $r['bal_cr_dr'], 2, ',', '.') . "\n";

    // Verify Bank BCA untouched
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal_120
        FROM transaction_lines WHERE account_id = 3
    ");
    $r = $stmt->fetch();
    echo "Account 120 (Bank BCA): " . number_format($r['bal_120'], 2, ',', '.') . " (should be 1.509.618,00)\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
