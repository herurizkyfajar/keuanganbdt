<?php
require 'config/database.php';
$pdo->beginTransaction();
try {
    $bankId = 3;   // Bank BCA
    $adjId = 30;   // Biaya Administrasi Bank (counterpart)

    $s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
    $row = $s->fetch();
    $currentBca = (float)($row['bal'] ?? 0);
    echo "Bank BCA current: " . number_format($currentBca,0,',','.') . " Dr\n";

    $targetBca = 1509618;
    $diff = $currentBca - $targetBca;
    echo "Need to credit Bank BCA: " . number_format($diff,0,',','.') . "\n";

    $s = $pdo->prepare("SELECT tl.id, tl.debit, tl.credit FROM transaction_lines tl JOIN transactions t ON t.id=tl.transaction_id WHERE tl.account_id=? AND t.description='Koreksi saldo Bank BCA'");
    $s->execute([$bankId]);
    $koreksi = $s->fetch();

    if ($koreksi) {
        $newCredit = (float)$koreksi['credit'] + $diff;
        $upd = $pdo->prepare("UPDATE transaction_lines SET credit=? WHERE id=?");
        $upd->execute([$newCredit, $koreksi['id']]);
        echo "Updated koreksi line id={$koreksi['id']}: credit now " . number_format($newCredit,0,',','.') . "\n";
    } else {
        $insTx = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (1, 'JE-KOREKSI-BCA', 'Koreksi saldo Bank BCA', '2026-11-23', 'journal')");
        $insTx->execute();
        $txId = $pdo->lastInsertId();
        $insLine = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?, ?, ?, ?, ?)");
        $insLine->execute([$txId, $bankId, 'Koreksi saldo Bank BCA', 0, $diff]);
        $insLine->execute([$txId, $adjId, 'Koreksi saldo Bank BCA', $diff, 0]);
        echo "Created koreksi: Cr Bank BCA " . number_format($diff,0,',','.') . "\n";
    }

    $pdo->commit();

    $s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
    $row = $s->fetch();
    echo "Bank BCA final: " . number_format((float)($row['bal'] ?? 0),0,',','.') . " Dr (target: 1.509.618) | " . (((float)($row['bal'] ?? 0) == 1509618) ? 'MATCH' : 'MISMATCH') . "\n";

    $s = $pdo->query("SELECT SUM(credit) - SUM(debit) as bal FROM transaction_lines WHERE account_id = 12");
    $row = $s->fetch();
    echo "Pendapatan final: " . number_format((float)($row['bal'] ?? 0),0,',','.') . " Cr (target: 33.430.782) | " . (((float)($row['bal'] ?? 0) == 33430782) ? 'MATCH' : 'MISMATCH') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
