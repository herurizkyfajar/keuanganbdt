<?php
require 'config/database.php';
$pdo->beginTransaction();
try {
    $pendId = 12;   // code 400 Pendapatan
    $bankId = 3;    // code 120 Bank BCA

    $entries = [
        ['2026-11-17','JE-144','PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia',100000],
        ['2026-11-16','JE-140','DP PENDAPETAN Azizah INV-20261116-00427 Indonesia',500000],
        ['2026-11-14','JE-136','PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan',100000],
        ['2026-11-14','JE-138','PENDAPETAN Kiran Kale INV-20261103-00412 India',200000],
        ['2026-11-14','JE-PENDAPETAN-MAELEN','PENDAPETAN Maelen Smith INV-20261113-00426 Australia',100000],
        ['2026-11-13','JE-135','PENDAPETAN Christina INV-20261113-00425 South Korea',75000],
        ['2026-11-12','JE-134','PENDAPETAN Nasser INV-20261110-00418 Pakistan',300000],
        ['2026-11-09','JE-119','',2400000],
        ['2026-11-09','JE-122','PENDAPETAN Aswen INV-20261108-00416 Malaysia',150000],
        ['2026-11-08','JE-117','DP Yunita widarani INV-20261104-00414 INDONESIA',1000000],
        ['2026-11-06','JE-111','PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium',100000],
        ['2026-11-06','JE-112','PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA',100000],
        ['2026-11-04','JE-106','PEMASUKAN ANDRE',100000],
        ['2026-11-02','JE-101','PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA',800000],
        ['2026-10-30','JE-89','PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN',1600000],
        ['2026-10-29','JE-85','PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore',100000],
        ['2026-10-29','JE-86','PEMASUKAN Muller Cecilia INV-20261028-00406 France',200000],
        ['2026-10-29','JE-87','PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI',300000],
        ['2026-10-29','JE-82','PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia',3800000],
        ['2026-10-28','JE-81','DP. PEMASUKAN BPN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN',200000],
        ['2026-10-27','JE-79','PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA',300000],
        ['2026-10-27','JE-80','PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico',100000],
        ['2026-10-26','JE-76','PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA',2500000],
        ['2026-10-24','JE-68','PEMASUKAN fei INV-20261019-00394 China',100000],
        ['2026-10-24','JE-69','ANDRE "IKUT TRANSFER"',1100000],
        ['2026-10-23','JE-65','PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA',700000],
        ['2026-10-23','JE-67','PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary',100000],
        ['2026-10-22','JE-58','PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA "KOTOR"',1500000],
        ['2026-10-22','JE-59','PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh',50000],
        ['2026-10-22','JE-62','PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh',50000],
        ['2026-10-20','JE-55','PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2',100000],
        ['2026-10-19','JE-52','PEMASUKAN Daniel Martak INV-20261017-00393 France',100000],
        ['2026-10-19','JE-54','PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1',100000],
        ['2026-10-18','JE-49','PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA',50000],
        ['2026-10-17','JE-45','GANTI OPRASIONAL FERDI',200000],
        ['2026-10-16','JE-36','PEMASUKAN Vanessa Krobath INV-20261014-00389 Austria',100000],
        ['2026-10-16','JE-44','PEMASUKAN Ganason Saminathan INV-20261015-00392 Malaysia',100000],
        ['2026-10-16','JE-39','PEMASUKAN Cahyani Fajar Sukoco INV-20261007-00379 INDONESIA Rp1.000.000',1000000],
        ['2026-10-16','JE-37','PEMASUKAN Kelly Ritschel INV-20261015-00390 Germany',650000],
        ['2026-10-15','JE-42','PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany',200000],
        ['2026-10-14','JE-32','PEMASUKAN Olivier vis INV-20261012-00387 Netherlands',100000],
        ['2026-10-14','JE-31','PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia',400000],
        ['2026-10-08','JE-17','pemasukan Tanti INV-20261007-00381 INDONESIA',75000],
        ['2026-10-08','JE-16','pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia',100000],
        ['2026-10-07','JE-12','pemasukan Farah Dhiya Amin INV-20261001-00374 Malaysia',100000],
        ['2026-10-06','JE-11','pemasukan Johannes Westrik INV-20261004-00378 Netherlands',150000],
        ['2026-10-04','JE-4','pemasukan jasa agensi',1903671],
        ['2026-10-04','JE-5','kas saat ini',9277111],
    ];

    $insTx = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (1, ?, ?, ?, 'journal')");
    $insLine = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?, ?, ?, ?, ?)");

    $totalPend = 0;
    $totalBank = 0;
    foreach ($entries as [$tgl, $ref, $desc, $amt]) {
        $insTx->execute([$ref, $desc, $tgl]);
        $txId = $pdo->lastInsertId();
        $insLine->execute([$txId, $pendId, $desc, 0, $amt]);
        $insLine->execute([$txId, $bankId, $desc, $amt, 0]);
        $totalPend += $amt;
        $totalBank += $amt;
    }

    $pdo->commit();
    echo "Total Pendapatan credits: " . number_format($totalPend,0,',','.') . "\n";
    echo "Total Bank BCA debits: " . number_format($totalBank,0,',','.') . "\n";

    $s = $pdo->query("SELECT SUM(credit) - SUM(debit) as bal FROM transaction_lines WHERE account_id = 12");
    $row = $s->fetch();
    echo "Pendapatan balance: " . number_format((float)($row['bal'] ?? 0),0,',','.') . " Cr\n";

    $s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
    $row = $s->fetch();
    echo "Bank BCA balance: " . number_format((float)($row['bal'] ?? 0),0,',','.') . " Dr\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
