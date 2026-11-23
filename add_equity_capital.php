<?php
require_once __DIR__ . '/config/database.php';

// Add capital entries to accounts 330, 340, 350
// Each: Credit 5.000.000 on 2026-09-05, with corresponding debit to Bank BRI (121)

$entries = [
    ['account_id' => 60, 'code' => '330', 'name' => 'Modal Aldi Maulana', 'date' => '2026-09-05', 'ref' => 'JE-MODAL-1', 'desc' => 'Modal Aldi Maulana', 'amount' => 5000000],
    ['account_id' => 61, 'code' => '340', 'name' => 'Modal Andre F R', 'date' => '2026-09-05', 'ref' => 'JE-MODAL-2', 'desc' => 'Modal Andre F R', 'amount' => 5000000],
    ['account_id' => 62, 'code' => '350', 'name' => 'Modal Heru R F', 'date' => '2026-09-05', 'ref' => 'JE-MODAL-3', 'desc' => 'Modal Heru R F', 'amount' => 5000000],
];

$briAccountId = 28; // Bank BRI
$briCode = '121';
$businessId = 1;

$pdo->beginTransaction();

try {
    foreach ($entries as $e) {
        // Create transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions (business_id, reference, description, transaction_date, type)
            VALUES (?, ?, ?, ?, 'journal')
        ");
        $stmt->execute([$businessId, $e['ref'], $e['desc'], $e['date']]);
        $txId = (int)$pdo->lastInsertId();

        // Credit to equity account
        $stmt = $pdo->prepare("
            INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
            VALUES (?, ?, ?, 0, ?)
        ");
        $stmt->execute([$txId, $e['account_id'], $e['desc'], $e['amount']]);

        // Debit to Bank BRI
        $stmt = $pdo->prepare("
            INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
            VALUES (?, ?, ?, ?, 0)
        ");
        $stmt->execute([$txId, $briAccountId, $e['desc'], $e['amount']]);

        echo "Created tx #{$txId} ({$e['ref']}): CR {$e['code']} {$e['amount']} | DR {$briCode} {$e['amount']}\n";
    }

    $pdo->commit();
    echo "\nDone. Committed.\n";

    // Verify
    $stmt = $pdo->query("
        SELECT a.code, a.name, COALESCE(SUM(l.debit),0) - COALESCE(SUM(l.credit),0) AS balance
        FROM accounts a
        LEFT JOIN transaction_lines l ON l.account_id = a.id
        WHERE a.id IN (60,61,62,28)
        GROUP BY a.id
        ORDER BY a.code
    ");
    foreach ($stmt->fetchAll() as $r) {
        echo "{$r['code']} {$r['name']}: balance = " . number_format($r['balance'], 2, ',', '.') . "\n";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
