<?php
require_once __DIR__ . '/config/database.php';

// Re-add JE-RE-5 ("kas saat ini") that was accidentally removed from account 360
// Credit 9,277,111 to account 360, Debit 9,277,111 to account 400 (Pendapatan)
// NOT touching Bank BCA (account 120)

$ref = 'JE-RE-5';
$date = '2026-10-04';
$desc = 'kas saat ini';
$amount = 9277111;
$acc360 = 63;
$accOffset = 12; // Pendapatan

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        INSERT INTO transactions (business_id, reference, description, transaction_date, type)
        VALUES (?, ?, ?, ?, 'journal')
    ");
    $stmt->execute([1, $ref, $desc, $date]);
    $txId = (int)$pdo->lastInsertId();

    // Credit to account 360
    $stmt = $pdo->prepare("
        INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$txId, $acc360, $desc, 0, $amount]);

    // Debit to offset account (Pendapatan)
    $stmt->execute([$txId, $accOffset, $desc, $amount, 0]);

    $pdo->commit();
    echo "Created tx #{$txId} ({$ref}): CR 360 {$amount} | DR 400 {$amount}\n";

    // Verify
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal_360_dr_minus_cr,
               COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) AS bal_360_cr_minus_dr
        FROM transaction_lines WHERE account_id = 63
    ");
    $r = $stmt->fetch();
    echo "Account 360 balance (credit-debit): " . number_format($r['bal_360_cr_minus_dr'], 2, ',', '.') . " Cr\n";

    $stmt = $pdo->query("
        SELECT COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal_120
        FROM transaction_lines WHERE account_id = 3
    ");
    $r = $stmt->fetch();
    echo "Account 120 (Bank BCA) balance: " . number_format($r['bal_120'], 2, ',', '.') . " (should be 1.509.618,00)\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
