<?php
// _rev_zero.php — Hapus semua transaksi yang menyentuh akun revenue agar saldo revenue = 0, lalu restore Bank BCA.
require 'config/database.php';
$pdo->beginTransaction();
try {
    // Revenue account IDs
    $revIds = [12, 13, 14, 31, 43]; // 400 Pendapatan, 410 Penjualan, 420 Lain-lain Pendapatan, 611 Bunga diterima, 623 Pendapetan Pengelolaan Web

    $placeholders = implode(',', array_fill(0, count($revIds), '?'));
    $s = $pdo->prepare("SELECT DISTINCT transaction_id FROM transaction_lines WHERE account_id IN ($placeholders)");
    $s->execute($revIds);
    $txIds = $s->fetchAll(PDO::FETCH_COLUMN);
    echo "Transactions touching revenue: " . count($txIds) . "\n";

    // Delete all lines from those transactions, then the transactions
    $delLines = $pdo->prepare("DELETE FROM transaction_lines WHERE transaction_id = ?");
    $delTx = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
    foreach ($txIds as $txId) {
        $delLines->execute([$txId]);
        $delTx->execute([$txId]);
    }
    echo "Deleted " . count($txIds) . " transactions and their lines.\n";

    // Verify revenue balances are now 0
    echo "\n=== REVENUE BALANCES AFTER DELETE ===\n";
    foreach ($revIds as $rid) {
        $s = $pdo->prepare("SELECT code, name, COALESCE(SUM(credit) - SUM(debit),0) as bal FROM transaction_lines tl JOIN accounts a ON a.id=tl.account_id WHERE tl.account_id=? GROUP BY tl.account_id");
        $s->execute([$rid]);
        $r = $s->fetch();
        if ($r) {
            printf("%-5s %-35s bal=%s\n", $r['code'], $r['name'], number_format((float)$r['bal'],0,',','.'));
        } else {
            $s2 = $pdo->query("SELECT code, name FROM accounts WHERE id=$rid");
            $r2 = $s2->fetch();
            printf("%-5s %-35s bal=0 (no lines)\n", $r2['code'] ?? '?', $r2['name'] ?? '?');
        }
    }

    // Check Bank BCA balance now
    $s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
    $row = $s->fetch();
    $currentBca = (float)($row['bal'] ?? 0);
    echo "\n=== BANK BCA AFTER DELETE ===\n";
    echo "Current: " . number_format($currentBca,0,',','.') . " Dr\n";
    echo "Target: 1.509.618 Dr\n";

    // Calculate adjustment needed
    $diff = 1509618 - $currentBca;
    echo "Difference: " . number_format($diff,0,',','.') . "\n";

    if ($diff != 0) {
        // Add/adjust koreksi line on Bank BCA
        // Find existing koreksi transaction
        $s = $pdo->prepare("SELECT tl.id, tl.debit, tl.credit FROM transaction_lines tl JOIN transactions t ON t.id=tl.transaction_id WHERE tl.account_id=3 AND t.description='Koreksi saldo Bank BCA'");
        $s->execute();
        $koreksi = $s->fetch();

        if ($koreksi) {
            // Update existing: add diff to debit (if diff>0) or credit (if diff<0)
            $newDebit = (float)$koreksi['debit'] + max(0, $diff);
            $newCredit = (float)$koreksi['credit'] + max(0, -$diff);
            $upd = $pdo->prepare("UPDATE transaction_lines SET debit=?, credit=? WHERE id=?");
            $upd->execute([$newDebit, $newCredit, $koreksi['id']]);
            echo "Updated koreksi: Dr=" . number_format($newDebit,0,',','.') . " Cr=" . number_format($newCredit,0,',','.') . "\n";
        } else {
            // Create new koreksi transaction
            $insTx = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (1, 'JE-KOREKSI-BCA', 'Koreksi saldo Bank BCA', '2026-11-23', 'journal')");
            $insTx->execute();
            $txId = $pdo->lastInsertId();
            $insLine = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?, ?, ?, ?, ?)");
            if ($diff > 0) {
                $insLine->execute([$txId, 3, 'Koreksi saldo Bank BCA', $diff, 0]);
                $insLine->execute([$txId, 30, 'Koreksi saldo Bank BCA', 0, $diff]); // Cr Biaya Administrasi Bank
            } else {
                $insLine->execute([$txId, 3, 'Koreksi saldo Bank BCA', 0, -$diff]);
                $insLine->execute([$txId, 30, 'Koreksi saldo Bank BCA', -$diff, 0]); // Dr Biaya Administrasi Bank
            }
            echo "Created new koreksi: " . number_format(abs($diff),0,',','.') . "\n";
        }
    }

    $pdo->commit();

    // Final verify
    echo "\n=== FINAL VERIFY ===\n";
    $s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
    $row = $s->fetch();
    echo "Bank BCA final: " . number_format((float)($row['bal'] ?? 0),0,',','.') . " Dr\n";

    echo "\nRevenue lines remaining:\n";
    $s = $pdo->query("SELECT COUNT(*) FROM transaction_lines tl WHERE tl.account_id IN ($placeholders)");
    // re-prepare
    $s = $pdo->prepare("SELECT COUNT(*) FROM transaction_lines WHERE account_id IN ($placeholders)");
    $s->execute($revIds);
    echo "Count: " . $s->fetchColumn() . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
