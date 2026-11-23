<?php
require 'config/database.php';

echo "=== REVENUE ACCOUNTS ===\n";
$s = $pdo->query("SELECT id, code, name FROM accounts WHERE type='revenue' ORDER BY code");
$revIds = [];
while ($r = $s->fetch()) {
    $revIds[] = $r['id'];
    printf("id=%-4s code=%-5s %s\n", $r['id'], $r['code'], $r['name']);
}

echo "\n=== LINES ON REVENUE ACCOUNTS ===\n";
$placeholders = implode(',', array_fill(0, count($revIds), '?'));
$s = $pdo->prepare("SELECT tl.id, tl.transaction_id, tl.account_id, a.code, a.name, tl.debit, tl.credit, t.transaction_date, t.reference, t.description 
FROM transaction_lines tl 
JOIN accounts a ON a.id=tl.account_id 
JOIN transactions t ON t.id=tl.transaction_id 
WHERE tl.account_id IN ($placeholders) 
ORDER BY t.transaction_date, t.id");
$s->execute($revIds);
$lines = $s->fetchAll();
echo "Total revenue lines: " . count($lines) . "\n";

$txIds = [];
foreach ($lines as $l) {
    $txIds[] = $l['transaction_id'];
    printf("tx=%-5s ref=%-12s date=%s acct=%-5s %-30s Dr %-12s Cr %-12s | %s\n",
        $l['transaction_id'], $l['reference'], $l['transaction_date'],
        $l['code'], $l['name'],
        number_format($l['debit'],0,',','.'), number_format($l['credit'],0,',','.'),
        $l['description']);
}
$txIds = array_unique($txIds);
echo "\nUnique transactions touching revenue: " . count($txIds) . "\n";

// Check current balances
echo "\n=== CURRENT REVENUE BALANCES ===\n";
foreach ($revIds as $rid) {
    $s = $pdo->prepare("SELECT code, name, SUM(credit) - SUM(debit) as bal FROM transaction_lines tl JOIN accounts a ON a.id=tl.account_id WHERE tl.account_id=? GROUP BY tl.account_id");
    $s->execute([$rid]);
    $r = $s->fetch();
    if ($r) {
        printf("%-5s %-35s bal=%s\n", $r['code'], $r['name'], number_format((float)$r['bal'],0,',','.'));
    }
}

echo "\n=== CURRENT BANK BCA BALANCE ===\n";
$s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
$row = $s->fetch();
echo "Bank BCA: " . number_format((float)($row['bal'] ?? 0),0,',','.') . " Dr\n";
