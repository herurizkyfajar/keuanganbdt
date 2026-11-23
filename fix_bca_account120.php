<?php
// fix_bca_account120.php
// Mengatur ulang account 120 (Bank BCA, id=3) agar cocok persis dengan daftar user.
// Tidak mengubah akun lain.
require_once __DIR__ . '/config/database.php';

$pdo->beginTransaction();
try {
    $accBankBca = 3; // 120 Bank BCA

    // 1. Hapus semua baris lama untuk account 120
    $del = $pdo->prepare("DELETE FROM transaction_lines WHERE account_id = ?");
    $del->execute([$accBankBca]);
    echo "Deleted old lines for account_id=3\n";

    // Data entri dari daftar user (urutan konsisten: tanggal, reference, description, amount, is_debit)
    // is_debit = true  => amount di kolom Debit (uang masuk ke bank, menambah saldo Dr)
    // is_debit = false => amount di kolom Credit (uang keluar dari bank, mengurangi saldo Dr)
    $entries = [
        // November 2026 (chronological order within each date)
        ['2026-11-01','JE-97','KONSUMSI MEET/RAPAT',115500,false],
        ['2026-11-01','JE-98','ANDRE NYICIL PIUTANG',900000,true],
        ['2026-11-01','JE-99','GANTI OPRASIONAL',600000,false],
        ['2026-11-02','JE-100','ANDRE OPRASIONAL JKT',500000,false],
        ['2026-11-02','JE-102','DENI NUR CAHYA SEWA DRIVER DAN MOBIL',600000,false],
        ['2026-11-02','JE-103','ANDRE GANTI OPRASIONAL',500000,false],
        ['2026-11-02','JE-104','ANDRE NYICIL PIUTANG',600000,true],
        ['2026-11-04','JE-105','ANDRE PINJEM',500000,false],
        ['2026-11-04','JE-106','PEMASUKAN ANDRE',100000,true],
        ['2026-11-04','JE-107','ADMIN BANK',14000,false],
        ['2026-11-06','JE-108','ANDRE BAYAR PIUTANG',500000,true],
        ['2026-11-06','JE-109','PERPANJANG N8N',100000,false],
        ['2026-11-06','JE-110','PERPANJANG AI',150000,false],
        ['2026-11-06','JE-111','PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium',100000,true],
        ['2026-11-06','JE-112','PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA',100000,true],
        ['2026-11-07','JE-113','RULLY',1200000,true],
        ['2026-11-07','JE-114','ARDI',500000,true],
        ['2026-11-07','JE-115','ADMIN TF GOPAY ARDI',1000,false],
        ['2026-11-08','JE-116','ANDRE BAYAR PIUTANG',200000,true],
        ['2026-11-08','JE-117','DP Yunita widarani INV-20261104-00414 INDONESIA',1000000,true],
        ['2026-11-08','JE-118','ADMIN QR',3000,false],
        ['2026-11-09','JE-119','',2400000,true],
        ['2026-11-09','JE-120','ADMIN QR',7200,false],
        ['2026-11-09','JE-121','SEWA DRIVER DAN MOBIL',1300000,false],
        ['2026-11-09','JE-122','PENDAPETAN Aswen INV-20261108-00416 Malaysia',150000,true],
        ['2026-11-09','JE-123','ANDRE BAYAR PIUTANG',500000,true],
        ['2026-11-10','JE-124','FEE BRC',100000,false],
        ['2026-11-10','JE-125','ANDRE BAYAR PIUTANG',700000,true],
        ['2026-11-10','JE-126','PEMBELIAN ASET TABLET',2772180,false],
        ['2026-11-10','JE-127','PEMBELIAN FOLLOWERS IG',120619,false],
        ['2026-11-10','JE-128','BIAYA MEET',120000,false],
        ['2026-11-11','JE-129','SUNTIK FOLLOWERS',120619,false],
        ['2026-11-11','JE-130','PHOTOCOPY DAN SCAN SURAT KESEPATAN',15000,false],
        ['2026-11-12','JE-131','SUNTIK FOLLOWERS IG',160825,false],
        ['2026-11-12','JE-133','PENDAPETAN Dali Schmölzer INV-20261110-00420 Netherlands',75000,true],
        ['2026-11-12','JE-134','PENDAPETAN Nasser INV-20261110-00418 Pakistan',300000,true],
        ['2026-11-13','JE-135','PENDAPETAN Christina INV-20261113-00425 South Korea',75000,true],
        ['2026-11-14','JE-136','PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan',100000,true],
        ['2026-11-14','JE-137','SEWA DRIVER DAN MOBIL',2000000,false],
        ['2026-11-14','JE-138','PENDAPETAN Kiran Kale INV-20261103-00412 India',200000,true],
        ['2026-11-14',NULL,'PENDAPETAN Maelen Smith INV-20261113-00426 Australia',100000,true],
        ['2026-11-16','JE-139','ANDRE OPRASIONAL',600000,false],
        ['2026-11-16','JE-140','DP PENDAPETAN Azizah INV-20261116-00427 Indonesia',500000,true],
        ['2026-11-16','JE-141','ANDRE PINJEM',700000,false],
        ['2026-11-17','JE-142','ANDRE BAYAR FEE',500000,false],
        ['2026-11-17','JE-143','PINJEM',500000,false],
        ['2026-11-17','JE-144','PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia',100000,true],
        ['2026-11-17','JE-145','GANTI FEE HUSEN',500000,true],
        ['2026-11-17','JE-146','GANTI SALDO',500000,true],
        ['2026-11-18','JE-147','PINJEM SALDO BUAT ETOLL',300000,false],
        ['2026-11-19','JE-148','GANTI SLADO ETOLL',300000,true],
        // Oktober 2026
        ['2026-10-01','JE-6','piutang andre',4100000,false],
        ['2026-10-04','JE-5','kas saat ini',9277111,true],
        ['2026-10-04','JE-7','admin bank bca',14000,false],
        ['2026-10-04','JE-71','TRANSFER BALIK KE ANDRE',1100000,false],
        ['2026-10-05','JE-8','OPRASIONAL TAMU Farah Dhiya Amin TANGGAL 04/10/2026',200000,false],
        ['2026-10-05','JE-9','OPRASIONAL TAMU Farah Dhiya Amin TANGGAL 05/10/2026',200000,false],
        ['2026-10-06','JE-10','PIUTANG ANDRE',300000,true],
        ['2026-10-06','JE-11','pemasukan Johannes Westrik INV-20261004-00378 Netherlands',150000,true],
        ['2026-10-07','JE-12','pemasukan Farah Dhiya Amin INV-20261001-00374 Malaysia',100000,true],
        ['2026-10-07','JE-13','ganti oprasional',200000,false],
        ['2026-10-08','JE-14','GANTI OPRASIONA 05-10-2026',200000,true],
        ['2026-10-08','JE-15','pemasukan Dennis Riedeman INV-20261007-00382 Netherlands',50000,false],
        ['2026-10-08','JE-16','pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia',100000,true],
        ['2026-10-08','JE-17','pemasukan Tanti INV-20261007-00381 INDONESIA',75000,true],
        ['2026-10-09','JE-21','OPRASIONAL JKT',600000,false],
        ['2026-10-09','JE-22','GANTI PIUTANG 06/10/2026',300000,true],
        ['2026-10-10','JE-23','ANDRE ETOLL',50000,false],
        ['2026-10-10','JE-24','ADMIN GOPAY ANDRE ETOLL',1000,false],
        ['2026-10-11','JE-25','BAYAR ADS',500000,false],
        ['2026-10-11','JE-26','ANDRE GANTI ETOLL',50000,true],
        ['2026-10-12','JE-27','ANDRE OPRASIONAL JKT',600000,false],
        ['2026-10-13','JE-28','PINJEM',500000,false],
        ['2026-10-13','JE-29','HERU PERPANJANG N8N',100000,false],
        ['2026-10-13','JE-30','BAYAR AI',150000,false],
        ['2026-10-13',NULL,'ANDRE OPRASIONAL 14/10/2026',250000,false],
        ['2026-10-14','JE-31','PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia',400000,true],
        ['2026-10-14','JE-32','PEMASUKAN Olivier vis INV-20261012-00387 Netherlands',100000,true],
        ['2026-10-15','JE-33','OPRASIONAL FERDI',200000,false],
        ['2026-10-15','JE-34','BAYAR MOBIL DAN SUPRI',450000,false],
        ['2026-10-15','JE-35','ADMIN GOPAY',1000,false],
        ['2026-10-16','JE-36','PEMASUKAN Vanessa Krobath INV-20261014-00389 Austria',100000,true],
        ['2026-10-16','JE-37','PEMASUKAN Kelly Ritschel INV-20261015-00390 Germany',650000,true],
        ['2026-10-16','JE-38','SEWA DRIVER & MOBIL ANDRE',700000,false],
        ['2026-10-16','JE-39','PEMASUKAN Cahyani Fajar Sukoco INV-20261007-00379 INDONESIA Rp1.000.000',1000000,true],
        ['2026-10-16','JE-40','SEWA DRIVER & MOBIL',450000,false],
        ['2026-10-16','JE-41','ADMIN QR',4950,false],
        ['2026-10-16','JE-44','PEMASUKAN Ganason Saminathan INV-20261015-00392 Malaysia',100000,true],
        ['2026-10-17','JE-45','GANTI OPRASIONAL FERDI',200000,true],
        ['2026-10-17','JE-46','GANTI OPRASIONAL',250000,true],
        ['2026-10-17','JE-47','ANDRE OPRASIONAL 18-10-2026',250000,true],
        ['2026-10-17','JE-48','PEMBAYARAN BACK UP DATA WEBSITE',1250000,false],
        ['2026-10-18','JE-49','PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA',50000,true],
        ['2026-10-19','JE-50','OPRASIONAL ANDRE',200000,false],
        ['2026-10-19','JE-51','BAYAR OPRASIONAL 17/10/2026',250000,false],
        ['2026-10-19','JE-52','PEMASUKAN Daniel Martak INV-20261017-00393 France',100000,true],
        ['2026-10-19','JE-54','PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1',100000,true],
        ['2026-10-20','JE-55','PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2',100000,true],
        ['2026-10-21','JE-57','GANTI OPRASIONAL 19/10/2026',200000,false],
        ['2026-10-22','JE-59','PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh',50000,true],
        ['2026-10-22','JE-62','PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh',50000,true],
        ['2026-10-23','JE-63','SEWA DRIVER DAN MOBIL',550000,false],
        ['2026-10-23','JE-64','SHARE BRC',100000,false],
        ['2026-10-23','JE-65','PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA',700000,true],
        ['2026-10-23','JE-66','ADMIN QR',2100,false],
        ['2026-10-23','JE-67','PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary',100000,true],
        ['2026-10-24','JE-68','PEMASUKAN fei INV-20261019-00394 China',100000,true],
        ['2026-10-24',NULL,'FEE BRC',50000,false],
        ['2026-10-24','JE-69','ANDRE "IKUT TRANSFER"',1100000,true],
        ['2026-10-24','JE-70','ADMIN QR',3300,false],
        ['2026-10-25','JE-72','ANDRE OPRASIONAL JAKARTA',600000,false],
        ['2026-10-25','JE-73','ANDRE BAYAR PIUTANG',500000,false],
        ['2026-10-25','JE-74','ANDRE GANTI UANG KACA MOBIL ""PIUTANG""',600000,false],
        ['2026-10-26','JE-75','ANDRE GANTI OPRASIONAL',600000,false],
        ['2026-10-27','JE-78','SEWA DRIVER & MOBIL HIACE Vendy Andreas INV-20261026-00403 INDONESIA',2500000,false],
        ['2026-10-27','JE-80','PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico',100000,true],
        ['2026-10-28','JE-81','DP. PEMASUKAN BPN  Rachmat Wilianto INV-20261028-00405 INDONESIA',200000,true],
        ['2026-10-29','JE-82','PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia',3800000,true],
        ['2026-10-29','JE-83','ADMIN QR',11400,false],
        ['2026-10-29','JE-84','SEWA DRIVER DAN MOBIL',3800000,false],
        ['2026-10-29','JE-85','PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore',100000,true],
        ['2026-10-29','JE-86','PEMASUKAN Muller Cecilia INV-20261028-00406 France',200000,true],
        ['2026-10-29','JE-87','PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI',300000,true],
        ['2026-10-29','JE-88','ANDRE NYICIL PIUTANG',300000,true],
        ['2026-10-30','JE-89','PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN',1600000,true],
        ['2026-10-30',NULL,'ANDRE GANRI OPRASIONAL 09/10/2026',600000,false],
        ['2026-10-30','JE-90','ADMIN QR',4800,false],
        ['2026-10-30','JE-91','BAYAR SEWA DRIVER DAN MOBIL',1400000,false],
        ['2026-10-30','JE-92','FEE BRC',200000,false],
    ];

    $insertTx = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (1, ?, ?, ?, 'journal')");
    $insertLine = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?, 3, ?, ?, ?)");

    $count = 0;
    $notFound = [];
    foreach ($entries as $e) {
        [$tgl, $ref, $desc, $amt, $isDebit] = $e;

        // Cari transaction yang cocok berdasarkan reference + tanggal + description
        // Jika reference NULL, cari berdasarkan tanggal + description
        if ($ref !== null) {
            $stmt = $pdo->prepare("SELECT id FROM transactions WHERE reference = ? AND transaction_date = ? AND business_id = 1 ORDER BY id ASC LIMIT 1");
            $stmt->execute([$ref, $tgl]);
            $row = $stmt->fetch();
        } else {
            $stmt = $pdo->prepare("SELECT id FROM transactions WHERE transaction_date = ? AND description = ? AND business_id = 1 ORDER BY id ASC LIMIT 1");
            $stmt->execute([$tgl, $desc]);
            $row = $stmt->fetch();
        }

        if (!$row) {
            // Buat transaksi baru
            $refUse = $ref !== null ? $ref : 'JE-AUTO-' . date('Ymd', strtotime($tgl)) . '-' . uniqid();
            $insertTx->execute([$refUse, $desc, $tgl]);
            $txId = (int)$pdo->lastInsertId();
        } else {
            $txId = (int)$row['id'];
        }

        $debit = $isDebit ? $amt : 0.00;
        $credit = $isDebit ? 0.00 : $amt;
        $insertLine->execute([$txId, $desc, $debit, $credit]);
        $count++;
    }

    $pdo->commit();

    // Verifikasi
    $s = $pdo->query("SELECT SUM(debit) - SUM(credit) as bal FROM transaction_lines WHERE account_id = 3");
    $row = $s->fetch();
    $final = (float)($row['bal'] ?? 0);
    echo "\n=== HASIL ===\n";
    echo "Entri yang diproses: $count\n";
    echo "Saldo Bank BCA (id=3) akhir: " . number_format($final, 0, ',', '.') . " Dr\n";
    echo "Target: 1.509.618 Dr\n";
    echo ($final == 1509618 ? "MATCH ✓\n" : "MISMATCH ✗\n");

    if (!empty($notFound)) {
        echo "\nTidak ditemukan:\n";
        foreach ($notFound as $n) echo " - $n\n";
    }

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
