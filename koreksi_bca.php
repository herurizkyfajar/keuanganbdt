<?php
// koreksi_bca.php
// Menyesuaikan saldo Bank BCA (id=3) agar menjadi 1.509.618,00 sesuai target.
require_once __DIR__ . '/config/database.php';

$businessId = 1;
$accBankBca = 3;   // 120 Bank BCA
$accAdjust  = 16;  // 510 Beban Listrik (akun penyesuaian)

// Hitung saldo saat ini Bank BCA
$stmt = $pdo->prepare("
  SELECT COALESCE(SUM(l.debit - l.credit), 0) AS saldo
  FROM transaction_lines l
  JOIN transactions t ON t.id = l.transaction_id
  WHERE l.account_id = ? AND t.business_id = ?
");
$stmt->execute([$accBankBca, $businessId]);
$saldoSaatIni = (float)$stmt->fetch()['saldo'];

$target = 1509618.00;
$selisih = $saldoSaatIni - $target; // jumlah yang harus dikurangi (kredit ke BCA)

echo "Saldo saat ini Bank BCA: " . number_format($saldoSaatIni, 2, ',', '.') . "\n";
echo "Target:                  " . number_format($target, 2, ',', '.') . "\n";
echo "Selisih koreksi:         " . number_format($selisih, 2, ',', '.') . "\n";

if ($selisih > 0) {
    $tgl = '2026-11-22';
    $desc = 'Koreksi saldo Bank BCA';
    $insertTx = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (?, ?, ?, ?, 'journal')");
    $insertLine = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?, ?, ?, ?, ?)");

    $insertTx->execute([$businessId, 'JE-KOREKSI-BCA', $desc, $tgl]);
    $txId = $pdo->lastInsertId();

    // Debit ke akun penyesuaian, Kredit ke Bank BCA (mengurangi saldo BCA)
    $insertLine->execute([$txId, $accAdjust, $desc, $selisih, 0.00]);
    $insertLine->execute([$txId, $accBankBca, $desc, 0.00, $selisih]);

    echo "Entri koreksi ditambahkan: Debit " . number_format($selisih, 2, ',', '.') . " ke akun id=$accAdjust, Kredit ke Bank BCA.\n";

    // Verifikasi saldo baru
    $stmt->execute([$accBankBca, $businessId]);
    $saldoBaru = (float)$stmt->fetch()['saldo'];
    echo "Saldo baru Bank BCA:     " . number_format($saldoBaru, 2, ',', '.') . "\n";
    echo "Selisih dengan target:   " . number_format($saldoBaru - $target, 2, ',', '.') . "\n";
} else {
    echo "Saldo sudah sesuai atau di bawah target, tidak perlu koreksi.\n";
}
