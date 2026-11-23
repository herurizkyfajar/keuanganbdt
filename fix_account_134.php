<?php
require_once __DIR__ . '/config/database.php';

// Fix account 134 (Piutang Operasional, id=56) to match exactly 22 entries
// Delete all existing account 56 lines, then insert correct ones

$entries = [
    ['ref' => 'JE-139',  'date' => '2026-11-16', 'desc' => 'ANDRE OPRASIONAL',              'tx_id' => 453, 'dr' => 600000, 'cr' => 0],
    ['ref' => 'JE-132',  'date' => '2026-11-09', 'desc' => 'ANDRE OPRASIONAL JAKARTA',      'tx_id' => 452, 'dr' => 600000, 'cr' => 0],
    ['ref' => 'JE-103',  'date' => '2026-11-02', 'desc' => 'ANDRE GANTI OPRASIONAL',        'tx_id' => 451, 'dr' => 0,      'cr' => 500000],
    ['ref' => 'JE-100',  'date' => '2026-11-02', 'desc' => 'ANDRE OPRASIONAL JKT',          'tx_id' => 450, 'dr' => 500000, 'cr' => 0],
    ['ref' => 'JE-99',   'date' => '2026-11-01', 'desc' => 'GANTI OPRASIONAL',              'tx_id' => 449, 'dr' => 0,      'cr' => 600000],
    ['ref' => 'JE-3010', 'date' => '2026-10-30', 'desc' => 'ANDRE GANRI OPRASIONAL 09/10/2026','tx_id' => 448, 'dr' => 600000, 'cr' => 0],
    ['ref' => 'JE-75',   'date' => '2026-10-26', 'desc' => 'ANDRE GANTI OPRASIONAL',        'tx_id' => 447, 'dr' => 600000, 'cr' => 0],
    ['ref' => 'JE-72',   'date' => '2026-10-25', 'desc' => 'ANDRE OPRASIONAL JAKARTA',      'tx_id' => 446, 'dr' => 600000, 'cr' => 0],
    ['ref' => 'JE-57',   'date' => '2026-10-21', 'desc' => 'GANTI OPRASIONAL 19/10/2026',   'tx_id' => 445, 'dr' => 200000, 'cr' => 0],
    ['ref' => 'JE-51',   'date' => '2026-10-19', 'desc' => 'BAYAR OPRASIONAL 17/10/2026',   'tx_id' => 444, 'dr' => 250000, 'cr' => 0],
    ['ref' => 'JE-50',   'date' => '2026-10-19', 'desc' => 'OPRASIONAL ANDRE',              'tx_id' => 443, 'dr' => 200000, 'cr' => 0],
    ['ref' => 'JE-47',   'date' => '2026-10-17', 'desc' => 'ANDRE OPRASIONAL 18-10-2026',   'tx_id' => 442, 'dr' => 250000, 'cr' => 0],
    ['ref' => 'JE-46',   'date' => '2026-10-17', 'desc' => 'GANTI OPRASIONAL',              'tx_id' => 441, 'dr' => 250000, 'cr' => 0],
    ['ref' => 'JE-1310', 'date' => '2026-10-13', 'desc' => 'ANDRE OPRASIONAL 14/10/2026',   'tx_id' => 440, 'dr' => 250000, 'cr' => 0],
    ['ref' => 'JE-27',   'date' => '2026-10-12', 'desc' => 'ANDRE OPRASIONAL JKT',          'tx_id' => 439, 'dr' => 600000, 'cr' => 0],
    ['ref' => 'JE-26',   'date' => '2026-10-11', 'desc' => 'ANDRE GANTI ETOLL',             'tx_id' => 438, 'dr' => 50000,  'cr' => 0],
    ['ref' => 'JE-23',   'date' => '2026-10-10', 'desc' => 'ANDRE ETOLL',                   'tx_id' => 437, 'dr' => 50000,  'cr' => 0],
    ['ref' => 'JE-21',   'date' => '2026-10-09', 'desc' => 'OPRASIONAL JKT',                'tx_id' => 436, 'dr' => 600000, 'cr' => 0],
    ['ref' => 'JE-14',   'date' => '2026-10-08', 'desc' => 'GANTI OPRASIONA 05-10-2026',    'tx_id' => 435, 'dr' => 200000, 'cr' => 0],
    ['ref' => 'JE-13',   'date' => '2026-10-07', 'desc' => 'ganti oprasional',              'tx_id' => 434, 'dr' => 200000, 'cr' => 0],
    ['ref' => 'JE-9',    'date' => '2026-10-05', 'desc' => 'OPRASIONAL TAMU Farah Dhiya Amin TANGGAL 05/10/2026', 'tx_id' => 433, 'dr' => 200000, 'cr' => 0],
    ['ref' => 'JE-8',    'date' => '2026-10-05', 'desc' => 'OPRASIONAL TAMU Farah Dhiya Amin TANGGAL 04/10/2026', 'tx_id' => 432, 'dr' => 200000, 'cr' => 0],
];

$accId = 56;
$businessId = 1;
$pdo->beginTransaction();

try {
    // Delete all existing account 56 lines
    $stmt = $pdo->prepare("DELETE FROM transaction_lines WHERE account_id = ?");
    $stmt->execute([$accId]);
    echo "Deleted " . $stmt->rowCount() . " existing account 56 lines\n";

    // Insert correct lines
    $stmt = $pdo->prepare("
        INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
        VALUES (?, ?, ?, ?, ?)
    ");
    $count = 0;
    $totalDr = 0;
    $totalCr = 0;
    foreach ($entries as $e) {
        $stmt->execute([$e['tx_id'], $accId, $e['desc'], $e['dr'], $e['cr']]);
        $count++;
        $totalDr += $e['dr'];
        $totalCr += $e['cr'];
        echo "  {$e['ref']} tx#{$e['tx_id']} DR=" . number_format($e['dr'],0) . " CR=" . number_format($e['cr'],0) . "\n";
    }

    $pdo->commit();
    echo "\nInserted $count lines. Total DR=$totalDr Total CR=$totalCr Balance=" . number_format($totalDr - $totalCr, 2, ',', '.') . " Dr\n";

    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) AS n, COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) AS bal FROM transaction_lines WHERE account_id = $accId");
    $r = $stmt->fetch();
    echo "Verification: lines=" . $r['n'] . " balance=" . number_format($r['bal'], 2, ',', '.') . " Dr\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
