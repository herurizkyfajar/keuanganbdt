<?php
require 'config/database.php';

echo "=== REVENUE ACCOUNTS (Pemasukkan) — semua harus 0 ===\n";
$revIds = [12, 13, 14, 31, 43];
$placeholders = implode(',', array_fill(0, count($revIds), '?'));
$s = $pdo->prepare("SELECT a.code, a.name, COALESCE(SUM(
    CASE WHEN a.type='revenue' THEN tl.credit - tl.debit ELSE 0 END
),0) as bal FROM accounts a LEFT JOIN transaction_lines tl ON tl.account_id=a.id WHERE a.id IN ($placeholders) GROUP BY a.id");
$s->execute($revIds);
$allZero = true;
while ($r = $s->fetch()) {
    $bal = (float)$r['bal'];
    $status = $bal == 0 ? 'OK' : 'ERROR';
    if ($bal != 0) $allZero = false;
    printf("%-5s %-35s bal=%s [%s]\n", $r['code'], $r['name'], number_format($bal,0,',','.'), $status);
}
echo "Semua revenue = 0: " . ($allZero ? 'YA' : 'TIDAK') . "\n";

echo "\n=== BANK BCA — target 1.509.618 Dr ===\n";
$s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
$row = $s->fetch();
$bca = (float)($row['bal'] ?? 0);
echo "Saldo: " . number_format($bca,0,',','.') . " Dr | Target: 1.509.618 Dr | " . ($bca == 1509618 ? 'MATCH' : 'MISMATCH') . "\n";

echo "\n=== BIAYA BRC — target 3 entri Dr ===\n";
$s = $pdo->query("SELECT tl.debit, tl.credit, t.transaction_date, tl.description FROM transaction_lines tl JOIN transactions t ON t.id=tl.transaction_id WHERE tl.account_id=51 ORDER BY t.transaction_date");
$cnt = 0; $bal=0;
while ($r = $s->fetch()) {
    $cnt++;
    $bal += (float)$r['debit'] - (float)$r['credit'];
    printf("%s | %s | Dr %-12s Cr %-12s\n", $r['transaction_date'], $r['description'], number_format($r['debit'],0,',','.'), number_format($r['credit'],0,',','.'));
}
echo "Jumlah entri: $cnt | Saldo: " . number_format($bal,0,',','.') . " Dr\n";
