<?php
require 'config/database.php';

echo "=== PENDAPATAN (code 400, id 12) CURRENT ===\n";
$s = $pdo->query("SELECT tl.id, t.transaction_date, tl.description, tl.debit, tl.credit 
FROM transaction_lines tl JOIN transactions t ON t.id=tl.transaction_id 
WHERE tl.account_id=12 ORDER BY t.transaction_date, tl.id");
$cnt = 0; $bal = 0;
while ($r = $s->fetch()) {
    $cnt++;
    $bal += (float)$r['credit'] - (float)$r['debit'];
    printf("%s | %s | Dr %-12s Cr %-12s | bal=%s\n",
        $r['transaction_date'], $r['description'],
        number_format($r['debit'],0,',','.'), number_format($r['credit'],0,',','.'),
        number_format($bal,0,',','.'));
}
echo "Entries: $cnt | Balance: " . number_format($bal,0,',','.') . " Cr\n";

echo "\n=== BANK BCA (id 3) CURRENT ===\n";
$s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
$row = $s->fetch();
echo "Balance: " . number_format((float)($row['bal'] ?? 0),0,',','.') . " Dr\n";
