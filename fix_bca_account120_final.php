<?php
// fix_bca_account120_final.php
// Mengisi akun 120 - Bank BCA (account_id = 3) PERSIS sesuai daftar yang diberikan.
// HANYA menghapus/menambah baris untuk account_id = 3. Akun lain tidak diubah.
//
// Pemakaian:
//   php fix_bca_account120_final.php --dry-run
//   php fix_bca_account120_final.php --apply
require_once __DIR__ . '/config/database.php';

$apply = in_array('--apply', $argv ?? [], true);
$dryRun = !$apply;

$rows = require __DIR__ . '/bca_data_final.php';

const BCA_ACCOUNT_ID = 3;
const BUSINESS_ID = 1;

function fmt($n) {
    return number_format((float)$n, 2, ',', '.');
}

echo "=== FIX AKUN 120 - BANK BCA ===\n";
echo "Mode: " . ($dryRun ? 'DRY RUN (tidak ada perubahan)' : 'APPLY (perubahan disimpan)') . "\n";
echo "Baris data: " . count($rows) . "\n\n";

// ------------------------------------------------------------------
// 1. Snapshot saldo akun lain + baris BCA lama
// ------------------------------------------------------------------
$beforeBalances = [];
$stmt = $pdo->query("
    SELECT account_id, COUNT(*) AS n, SUM(debit) AS d, SUM(credit) AS c
    FROM transaction_lines
    GROUP BY account_id
    ORDER BY account_id
");
foreach ($stmt->fetchAll() as $r) {
    $beforeBalances[(int)$r['account_id']] = [
        'n' => (int)$r['n'],
        'd' => (float)$r['d'],
        'c' => (float)$r['c'],
        'bal' => (float)$r['d'] - (float)$r['c'],
    ];
}

$oldBca = [];
$stmt = $pdo->query("
    SELECT l.id, l.transaction_id, l.description, l.debit, l.credit,
           t.transaction_date, t.reference
    FROM transaction_lines l
    JOIN transactions t ON t.id = l.transaction_id
    WHERE l.account_id = " . BCA_ACCOUNT_ID . "
    ORDER BY l.id
");
$oldBcaLines = $stmt->fetchAll();
foreach ($oldBcaLines as $l) {
    // Map by reference and also by description so baris tanpa referensi
    // tetap memilih transaksi BCA lamanya.
    $keys = [];
    if ($l['reference'] !== null && $l['reference'] !== '') {
        $keys[] = $l['transaction_date'] . '|' . $l['reference'];
    }
    $keys[] = $l['transaction_date'] . '|DESC:' . $l['description'];
    foreach ($keys as $key) {
        if (!isset($oldBca[$key])) {
            $oldBca[$key] = (int)$l['transaction_id'];
        }
    }
}
echo "Baris BCA lama: " . count($oldBcaLines) . "\n";
echo "Saldo BCA lama: " . fmt($beforeBalances[BCA_ACCOUNT_ID]['bal'] ?? 0) . "\n\n";

// ------------------------------------------------------------------
// 2. Kandidat transaksi per tanggal (setelah baris BCA dihapus)
// ------------------------------------------------------------------
$pdo->beginTransaction();

$del = $pdo->prepare("DELETE FROM transaction_lines WHERE account_id = ?");
$del->execute([BCA_ACCOUNT_ID]);
echo "Baris BCA lama dihapus (dalam transaksi): " . $del->rowCount() . "\n";

// Ambil semua transaksi + net baris selain BCA
$stmt = $pdo->query("
    SELECT t.id, t.reference, t.transaction_date, t.description,
           COALESCE(SUM(l.debit), 0) AS d,
           COALESCE(SUM(l.credit), 0) AS c,
           COUNT(l.id) AS n
    FROM transactions t
    LEFT JOIN transaction_lines l ON l.transaction_id = t.id
    WHERE t.business_id = " . BUSINESS_ID . "
    GROUP BY t.id
    ORDER BY t.id
");
$txById = [];
$txByDateRef = [];
$txByDateDesc = [];
foreach ($stmt->fetchAll() as $t) {
    $t['id'] = (int)$t['id'];
    $t['d'] = (float)$t['d'];
    $t['c'] = (float)$t['c'];
    $t['n'] = (int)$t['n'];
    $t['net'] = $t['d'] - $t['c'];
    $txById[$t['id']] = $t;
    $txByDateRef[$t['transaction_date'] . '|' . ($t['reference'] ?? '')][] = $t;
    $txByDateDesc[$t['transaction_date'] . '|' . ($t['description'] ?? '')][] = $t;
}

function pickCandidate(array $candidates, float $desiredNet, array $oldBca, string $key): ?array {
    if (empty($candidates)) return null;
    usort($candidates, function ($a, $b) use ($desiredNet, $oldBca, $key) {
        $sa = abs($a['net'] + $desiredNet);
        $sb = abs($b['net'] + $desiredNet);
        if (abs($sa - $sb) > 0.001) return $sa <=> $sb;

        $ha = (isset($oldBca[$key]) && $oldBca[$key] === $a['id']) ? 1 : 0;
        $hb = (isset($oldBca[$key]) && $oldBca[$key] === $b['id']) ? 1 : 0;
        if ($ha !== $hb) return $hb <=> $ha;

        if ($a['n'] !== $b['n']) return $b['n'] <=> $a['n'];
        return $a['id'] <=> $b['id'];
    });
    return $candidates[0];
}

// ------------------------------------------------------------------
// 3. Pilih transaksi / buat baru, lalu insert baris BCA
// ------------------------------------------------------------------
$insertLine = $pdo->prepare("
    INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit)
    VALUES (?, ?, ?, ?, ?)
");
$insertTx = $pdo->prepare("
    INSERT INTO transactions (business_id, reference, description, transaction_date, type)
    VALUES (?, ?, ?, ?, 'journal')
");

$usedTx = [];
$created = 0;
$inserted = 0;
$chosen = [];
$pageOrder = [];

foreach ($rows as $idx => $r) {
    [$date, $ref, $desc, $debit, $credit, $expectedBal] = $r;
    $key = $date . '|' . ($ref !== null && $ref !== '' ? $ref : 'DESC:' . $desc);
    $desiredNet = (float)$debit - (float)$credit;

    $candidates = [];
    if ($ref !== null && $ref !== '') {
        $candidates = $txByDateRef[$date . '|' . $ref] ?? [];
    } else {
        $candidates = $txByDateDesc[$date . '|' . $desc] ?? [];
        if (empty($candidates)) {
            // fallback: same date, description contains
            foreach ($txByDateDesc as $k => $list) {
                if (strpos($k, $date . '|') === 0) {
                    foreach ($list as $t) {
                        if (trim($t['description'] ?? '') === trim($desc)) $candidates[] = $t;
                    }
                }
            }
        }
    }

    $pick = pickCandidate($candidates, $desiredNet, $oldBca, $key);
    if ($pick !== null) {
        $txId = $pick['id'];
        $action = 'pakai';
    } else {
        if (!$dryRun) {
            $insertTx->execute([BUSINESS_ID, ($ref !== null && $ref !== '') ? $ref : null, $desc, $date]);
            $txId = (int)$pdo->lastInsertId();
        } else {
            // simulate a new id above current max
            $txId = 900000 + $idx;
        }
        $created++;
        $action = 'baru';
    }

    $insertLine->execute([$txId, BCA_ACCOUNT_ID, $desc, $debit, $credit]);
    $inserted++;
    $usedTx[$txId] = true;
    $chosen[] = [
        'idx' => $idx + 1,
        'date' => $date,
        'ref' => $ref,
        'desc' => $desc,
        'debit' => $debit,
        'credit' => $credit,
        'expected' => $expectedBal,
        'tx' => $txId,
        'action' => $action,
        'candidates' => count($candidates),
    ];
}

echo "Baris BCA diinsert: $inserted\n";
echo "Transaksi baru dibuat: $created\n\n";

// ------------------------------------------------------------------
// 4. Verifikasi saldo BCA dan saldo akun lain
// ------------------------------------------------------------------
$stmt = $pdo->query("
    SELECT COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c, COUNT(*) AS n
    FROM transaction_lines WHERE account_id = " . BCA_ACCOUNT_ID
);
$bca = $stmt->fetch();
$bcaBal = (float)$bca['d'] - (float)$bca['c'];
echo "=== VERIFIKASI ===\n";
echo "Baris BCA: " . $bca['n'] . " (target 131)\n";
echo "Total debit BCA: " . fmt($bca['d']) . "\n";
echo "Total credit BCA: " . fmt($bca['c']) . "\n";
echo "Saldo BCA: " . fmt($bcaBal) . " (target 1.509.618,00)\n";

$stmt = $pdo->query("
    SELECT account_id, COUNT(*) AS n, SUM(debit) AS d, SUM(credit) AS c
    FROM transaction_lines
    GROUP BY account_id
    ORDER BY account_id
");
$afterBalances = [];
foreach ($stmt->fetchAll() as $r) {
    $afterBalances[(int)$r['account_id']] = [
        'n' => (int)$r['n'],
        'd' => (float)$r['d'],
        'c' => (float)$r['c'],
        'bal' => (float)$r['d'] - (float)$r['c'],
    ];
}

$otherChanged = 0;
foreach ($beforeBalances as $accId => $b) {
    if ($accId === BCA_ACCOUNT_ID) continue;
    $a = $afterBalances[$accId] ?? ['n' => 0, 'd' => 0, 'c' => 0, 'bal' => 0];
    if ($a['n'] !== $b['n'] || abs($a['bal'] - $b['bal']) > 0.001) {
        echo "AKUN LAIN BERUBAH! account_id=$accId before(n={$b['n']},bal=" . fmt($b['bal']) . ") after(n={$a['n']},bal=" . fmt($a['bal']) . ")\n";
        $otherChanged++;
    }
}
foreach ($afterBalances as $accId => $a) {
    if ($accId === BCA_ACCOUNT_ID) continue;
    if (!isset($beforeBalances[$accId])) {
        echo "AKUN BARU MUNCUL! account_id=$accId n={$a['n']} bal=" . fmt($a['bal']) . "\n";
        $otherChanged++;
    }
}
echo "Akun lain berubah: $otherChanged (target 0)\n";

// Transaksi yang tadinya punya baris BCA dan sekarang kosong
$oldTxIds = array_values(array_unique(array_map(fn($l) => (int)$l['transaction_id'], $oldBcaLines)));
$emptyOld = [];
if ($oldTxIds) {
    $in = implode(',', array_map('intval', $oldTxIds));
    $stmt = $pdo->query("
        SELECT t.id, t.reference, t.transaction_date, t.description
        FROM transactions t
        WHERE t.id IN ($in)
          AND NOT EXISTS (SELECT 1 FROM transaction_lines l WHERE l.transaction_id = t.id)
        ORDER BY t.id
    ");
    $emptyOld = $stmt->fetchAll();
}
echo "Transaksi lama yang menjadi kosong: " . count($emptyOld) . "\n";
foreach ($emptyOld as $t) {
    echo "  kosong: #{$t['id']} {$t['transaction_date']} {$t['reference']} {$t['description']}\n";
}
if ($apply && $emptyOld) {
    $ids = implode(',', array_map(fn($t) => (int)$t['id'], $emptyOld));
    $deleted = $pdo->exec("DELETE FROM transactions WHERE id IN ($ids)");
    echo "Transaksi kosong dihapus: $deleted\n";
}

// ------------------------------------------------------------------
// 5. Simulasi running balance seperti halaman detail-transaksi.php
//    (ORDER BY t.transaction_date, t.id)
// ------------------------------------------------------------------
$stmt = $pdo->query("
    SELECT l.id, l.transaction_id, l.description, l.debit, l.credit,
           t.transaction_date, t.reference
    FROM transaction_lines l
    JOIN transactions t ON t.id = l.transaction_id
    WHERE l.account_id = " . BCA_ACCOUNT_ID . " AND t.business_id = " . BUSINESS_ID . "
    ORDER BY t.transaction_date ASC, t.id ASC, l.id ASC
");
$ledger = $stmt->fetchAll();
$run = 0.0;
$pageMismatch = 0;
$byLineIdMismatch = 0;
foreach ($ledger as $l) {
    $run += (float)$l['debit'] - (float)$l['credit'];
}
echo "Saldo akhir (order halaman): " . fmt($run) . "\n";

// compare per row in chronological expected order by date, then line id
$stmt = $pdo->query("
    SELECT l.id, l.debit, l.credit
    FROM transaction_lines l
    JOIN transactions t ON t.id = l.transaction_id
    WHERE l.account_id = " . BCA_ACCOUNT_ID . " AND t.business_id = " . BUSINESS_ID . "
    ORDER BY t.transaction_date ASC, l.id ASC
");
$byLine = $stmt->fetchAll();
$run2 = 0.0;
$expectedBalances = array_column($rows, 5);
foreach ($byLine as $i => $l) {
    $run2 += (float)$l['debit'] - (float)$l['credit'];
    if (isset($expectedBalances[$i]) && abs($run2 - (float)$expectedBalances[$i]) > 0.01) {
        $byLineIdMismatch++;
    }
}
echo "Mismatch running balance jika urut line id: $byLineIdMismatch (target 0)\n";

// Compare page order (date, transaction id) to expected
$stmt = $pdo->query("
    SELECT l.id, l.debit, l.credit, t.id AS tx_id, t.transaction_date
    FROM transaction_lines l
    JOIN transactions t ON t.id = l.transaction_id
    WHERE l.account_id = " . BCA_ACCOUNT_ID . " AND t.business_id = " . BUSINESS_ID . "
    ORDER BY t.transaction_date ASC, t.id ASC, l.id ASC
");
$pageRows = $stmt->fetchAll();
$run3 = 0.0;
$expectedByDate = [];
foreach ($rows as $r) $expectedByDate[$r[0]][] = $r;
$pageMismatch = 0;
foreach ($pageRows as $i => $l) {
    $run3 += (float)$l['debit'] - (float)$l['credit'];
    if (isset($expectedBalances[$i]) && abs($run3 - (float)$expectedBalances[$i]) > 0.01) {
        $pageMismatch++;
    }
}
echo "Mismatch running balance urut halaman (tanggal, id transaksi): $pageMismatch dari " . count($pageRows) . "\n";

// ------------------------------------------------------------------
// 6. Tampilkan pemetaan transaksi
// ------------------------------------------------------------------
echo "\n=== PEMETAAN TRANSAKSI ===\n";
foreach ($chosen as $c) {
    echo str_pad((string)$c['idx'], 3, ' ', STR_PAD_LEFT) . " "
        . $c['date'] . " "
        . str_pad($c['ref'] ?? '-', 8)
        . " tx=" . str_pad((string)$c['tx'], 4, ' ', STR_PAD_LEFT)
        . " " . $c['action']
        . " cand=" . $c['candidates']
        . " D=" . fmt($c['debit'])
        . " C=" . fmt($c['credit'])
        . " exp=" . fmt($c['expected'])
        . "\n";
}

// ------------------------------------------------------------------
// 7. Commit / rollback
// ------------------------------------------------------------------
if ($dryRun) {
    $pdo->rollBack();
    echo "\nDRY RUN: semua perubahan di-rollback.\n";
} else {
    $pdo->commit();
    echo "\nAPPLY: perubahan disimpan.\n";
}

echo "Selesai.\n";
