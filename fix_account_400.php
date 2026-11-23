<?php
require_once __DIR__ . '/config/database.php';

// Fix account 400 (Pendapatan, id=12) to match exactly 42 credit entries
// Delete all existing account 12 lines, then insert correct ones

$entries = [
    ['ref' => 'JE-144',  'date' => '2026-11-17', 'tx_id' => 706, 'desc' => 'PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia', 'cr' => 100000],
    ['ref' => 'JE-140',  'date' => '2026-11-16', 'tx_id' => 707, 'desc' => 'DP PENDAPETAN Azizah INV-20261116-00427 Indonesia', 'cr' => 500000],
    ['ref' => 'JE-136',  'date' => '2026-11-14', 'tx_id' => 708, 'desc' => 'PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan', 'cr' => 100000],
    ['ref' => 'JE-138',  'date' => '2026-11-14', 'tx_id' => 709, 'desc' => 'PENDAPETAN Kiran Kale INV-20261103-00412 India', 'cr' => 200000],
    ['ref' => 'JE-MAelen', 'date' => '2026-11-14', 'tx_id' => 710, 'desc' => 'PENDAPETAN Maelen Smith INV-20261113-00426 Australia', 'cr' => 100000],
    ['ref' => 'JE-135',  'date' => '2026-11-13', 'tx_id' => 711, 'desc' => 'PENDAPETAN Christina INV-20261113-00425 South Korea', 'cr' => 75000],
    ['ref' => 'JE-134',  'date' => '2026-11-12', 'tx_id' => 712, 'desc' => 'PENDAPETAN Nasser INV-20261110-00418 Pakistan', 'cr' => 300000],
    ['ref' => 'JE-119',  'date' => '2026-11-09', 'tx_id' => 713, 'desc' => '', 'cr' => 2400000],
    ['ref' => 'JE-122',  'date' => '2026-11-09', 'tx_id' => 714, 'desc' => 'PENDAPETAN Aswen INV-20261108-00416 Malaysia', 'cr' => 150000],
    ['ref' => 'JE-117',  'date' => '2026-11-08', 'tx_id' => 715, 'desc' => 'DP Yunita widarani INV-20261104-00414 INDONESIA', 'cr' => 1000000],
    ['ref' => 'JE-111',  'date' => '2026-11-06', 'tx_id' => 716, 'desc' => 'PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium', 'cr' => 100000],
    ['ref' => 'JE-112',  'date' => '2026-11-06', 'tx_id' => 717, 'desc' => 'PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA', 'cr' => 100000],
    ['ref' => 'JE-106',  'date' => '2026-11-04', 'tx_id' => 718, 'desc' => 'PEMASUKAN ANDRE', 'cr' => 100000],
    ['ref' => 'JE-101',  'date' => '2026-11-02', 'tx_id' => 719, 'desc' => 'PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA', 'cr' => 800000],
    ['ref' => 'JE-89',   'date' => '2026-10-30', 'tx_id' => 720, 'desc' => 'PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', 'cr' => 1600000],
    ['ref' => 'JE-85',   'date' => '2026-10-29', 'tx_id' => 721, 'desc' => 'PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore', 'cr' => 100000],
    ['ref' => 'JE-86',   'date' => '2026-10-29', 'tx_id' => 722, 'desc' => 'PEMASUKAN Muller Cecilia INV-20261028-00406 France', 'cr' => 200000],
    ['ref' => 'JE-87',   'date' => '2026-10-29', 'tx_id' => 723, 'desc' => 'PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI', 'cr' => 300000],
    ['ref' => 'JE-82',   'date' => '2026-10-29', 'tx_id' => 724, 'desc' => 'PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 'cr' => 3800000],
    ['ref' => 'JE-81',   'date' => '2026-10-28', 'tx_id' => 725, 'desc' => 'DP. PEMASUKAN BPN Rachmat Wilianto INV-20261028-00405 INDONESIA', 'cr' => 200000],
    ['ref' => 'JE-79',   'date' => '2026-10-27', 'tx_id' => 726, 'desc' => 'PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA', 'cr' => 300000],
    ['ref' => 'JE-80',   'date' => '2026-10-27', 'tx_id' => 727, 'desc' => 'PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico', 'cr' => 100000],
    ['ref' => 'JE-76',   'date' => '2026-10-26', 'tx_id' => 728, 'desc' => 'PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA', 'cr' => 2500000],
    ['ref' => 'JE-68',   'date' => '2026-10-24', 'tx_id' => 729, 'desc' => 'PEMASUKAN fei INV-20261019-00394 China', 'cr' => 100000],
    ['ref' => 'JE-69',   'date' => '2026-10-24', 'tx_id' => 730, 'desc' => 'ANDRE ""IKUT TRANSFER""', 'cr' => 1100000],
    ['ref' => 'JE-65',   'date' => '2026-10-23', 'tx_id' => 731, 'desc' => 'PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA', 'cr' => 700000],
    ['ref' => 'JE-67',   'date' => '2026-10-23', 'tx_id' => 732, 'desc' => 'PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary', 'cr' => 100000],
    ['ref' => 'JE-58',   'date' => '2026-10-22', 'tx_id' => 733, 'desc' => 'PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA ""KOTOR""', 'cr' => 1500000],
    ['ref' => 'JE-59',   'date' => '2026-10-22', 'tx_id' => 734, 'desc' => 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 'cr' => 50000],
    ['ref' => 'JE-62',   'date' => '2026-10-22', 'tx_id' => 735, 'desc' => 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 'cr' => 50000],
    ['ref' => 'JE-55',   'date' => '2026-10-20', 'tx_id' => 736, 'desc' => 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2', 'cr' => 100000],
    ['ref' => 'JE-52',   'date' => '2026-10-19', 'tx_id' => 737, 'desc' => 'PEMASUKAN Daniel Martak INV-20261017-00393 France', 'cr' => 100000],
    ['ref' => 'JE-54',   'date' => '2026-10-19', 'tx_id' => 738, 'desc' => 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1', 'cr' => 100000],
    ['ref' => 'JE-49',   'date' => '2026-10-18', 'tx_id' => 739, 'desc' => 'PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA', 'cr' => 50000],
    ['ref' => 'JE-45',   'date' => '2026-10-17', 'tx_id' => 740, 'desc' => 'GANTI OPRASIONAL FERDI', 'cr' => 200000],
    ['ref' => 'JE-36',   'date' => '2026-10-16', 'tx_id' => 741, 'desc' => 'PEMASUKAN Vanessa Krobath INV-20261014-00389 Austria', 'cr' => 100000],
    ['ref' => 'JE-44',   'date' => '2026-10-16', 'tx_id' => 742, 'desc' => 'PEMASUKAN Ganason Saminathan INV-20261015-00392 Malaysia', 'cr' => 100000],
    ['ref' => 'JE-39',   'date' => '2026-10-16', 'tx_id' => 743, 'desc' => 'PEMASUKAN Cahyani Fajar Sukoco INV-20261007-00379 INDONESIA  Rp1.000.000', 'cr' => 1000000],
    ['ref' => 'JE-37',   'date' => '2026-10-16', 'tx_id' => 746, 'desc' => 'PEMASUKAN Kelly Ritschel INV-20261015-00390 Germany', 'cr' => 650000],
    ['ref' => 'JE-42',   'date' => '2026-10-15', 'tx_id' => 745, 'desc' => 'PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany', 'cr' => 200000],
    ['ref' => 'JE-32',   'date' => '2026-10-14', 'tx_id' => 746, 'desc' => 'PEMASUKAN Olivier vis INV-20261012-00387 Netherlands', 'cr' => 100000],
    ['ref' => 'JE-31',   'date' => '2026-10-14', 'tx_id' => 747, 'desc' => 'PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 'cr' => 400000],
    ['ref' => 'JE-17',   'date' => '2026-10-08', 'tx_id' => 748, 'desc' => 'pemasukan Tanti INV-20261007-00381 INDONESIA', 'cr' => 75000],
    ['ref' => 'JE-16',   'date' => '2026-10-08', 'tx_id' => 749, 'desc' => 'pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia', 'cr' => 100000],
    ['ref' => 'JE-12',   'date' => '2026-10-07', 'tx_id' => 750, 'desc' => 'pemasukan Farah Dhiya Amin INV-20261001-00374 Malaysia', 'cr' => 100000],
    ['ref' => 'JE-11',   'date' => '2026-10-06', 'tx_id' => 751, 'desc' => 'pemasukan Johannes Westrik INV-20261004-00378 Netherlands', 'cr' => 150000],
    ['ref' => 'JE-4',    'date' => '2026-10-04', 'tx_id' => 752, 'desc' => 'pemasukan jasa agensi', 'cr' => 1903671],
    ['ref' => 'JE-5',    'date' => '2026-10-04', 'tx_id' => 753, 'desc' => 'kas saat ini', 'cr' => 9277111],
];

$accId = 12;
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("DELETE FROM transaction_lines WHERE account_id = ?");
    $stmt->execute([$accId]);
    echo "Deleted " . $stmt->rowCount() . " existing account 12 lines\n";

    $stmt = $pdo->prepare("
        INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
        VALUES (?, ?, ?, 0, ?)
    ");
    $count = 0;
    $totalCr = 0;
    foreach ($entries as $e) {
        $stmt->execute([$e['tx_id'], $accId, $e['desc'], $e['cr']]);
        $count++;
        $totalCr += $e['cr'];
    }

    $pdo->commit();
    echo "Inserted $count lines. Total CR=$totalCr Balance=" . number_format($totalCr, 2, ',', '.') . " Cr\n";
    echo "Expected balance: 33.430.782 Cr\n";

    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) AS n, COALESCE(SUM(credit),0) AS total_cr FROM transaction_lines WHERE account_id = $accId");
    $r = $stmt->fetch();
    echo "Verification: lines={$r['n']} total_cr=" . number_format($r['total_cr'], 2, ',', '.') . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
