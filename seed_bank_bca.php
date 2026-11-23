<?php
// seed_bank_bca.php
// Membuat transaksi Bank BCA (id=3) sesuai data di Image 8.
require_once __DIR__ . '/config/database.php';

$businessId = 1;

// Account IDs
$accBankBca = 3;   // 120 Bank BCA
$accKas     = 2;   // 110 Kas
$accPiutang = 4;   // 130 Piutang Usaha
$accBeban   = 16;  // 510 Beban Listrik
$accPenjualan = 13;// 410 Penjualan

$transactions = [
    // [tanggal, description, [ [account_id, debit, credit, line_desc], ... ]]
    [
        '2026-10-01',
        'piutang andre',
        [
            [$accPiutang, 4100000.00, 0.00, 'Piutang Andre'],
            [$accBankBca, 0.00, 4100000.00, 'Piutang Andre'],
        ],
    ],
    [
        '2026-10-04',
        'kas saat ini',
        [
            [$accBankBca, 9277111.00, 0.00, 'Kas saat ini'],
            [$accKas, 0.00, 9277111.00, 'Kas saat ini'],
        ],
    ],
    [
        '2026-10-04',
        'admin bank bca',
        [
            [$accBeban, 14000.00, 0.00, 'Admin Bank BCA'],
            [$accBankBca, 0.00, 14000.00, 'Admin Bank BCA'],
        ],
    ],
    [
        '2026-10-04',
        'TRANSFER BALIK KE ANDRE',
        [
            [$accPiutang, 1100000.00, 0.00, 'Transfer Balik Ke Andre'],
            [$accBankBca, 0.00, 1100000.00, 'Transfer Balik Ke Andre'],
        ],
    ],
    [
        '2026-10-05',
        'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026',
        [
            [$accBeban, 200000.00, 0.00, 'Operasional Tamu Farah Dhiva Amin 04/10/2026'],
            [$accBankBca, 0.00, 200000.00, 'Operasional Tamu Farah Dhiva Amin 04/10/2026'],
        ],
    ],
    [
        '2026-10-05',
        'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026',
        [
            [$accBeban, 200000.00, 0.00, 'Operasional Tamu Farah Dhiva Amin 05/10/2026'],
            [$accBankBca, 0.00, 200000.00, 'Operasional Tamu Farah Dhiva Amin 05/10/2026'],
        ],
    ],
    [
        '2026-10-06',
        'PIUTANG ANDRE',
        [
            [$accPiutang, 300000.00, 0.00, 'Piutang Andre'],
            [$accBankBca, 0.00, 300000.00, 'Piutang Andre'],
        ],
    ],
    [
        '2026-10-06',
        'pemasukan Johannes Westrik INV-20261004-00378 Netherlands',
        [
            [$accBankBca, 150000.00, 0.00, 'Pemasukan Johannes Westrik INV-20261004-00378 Netherlands'],
            [$accPenjualan, 0.00, 150000.00, 'Pemasukan Johannes Westrik INV-20261004-00378 Netherlands'],
        ],
    ],
];

$insertTx = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (?, ?, ?, ?, 'journal')");
$insertLine = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?, ?, ?, ?, ?)");

foreach ($transactions as $tx) {
    [$tgl, $desc, $lines] = $tx;
    $insertTx->execute([$businessId, 'JE-BCA-' . date('Ymd', strtotime($tgl)) . '-' . uniqid(), $desc, $tgl]);
    $txId = $pdo->lastInsertId();
    foreach ($lines as [$accId, $debit, $credit, $lineDesc]) {
        $insertLine->execute([$txId, $accId, $lineDesc, $debit, $credit]);
    }
    echo "Inserted transaction id=$txId ($tgl) - $desc\n";
}

echo "Selesai. Transaksi Bank BCA telah ditambahkan.\n";
