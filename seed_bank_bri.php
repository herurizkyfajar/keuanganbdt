<?php
// seed_bank_bri.php
// Membuat transaksi Bank BRI (id=28) sesuai data di Image 1 dan Image 2.
require_once __DIR__ . '/config/database.php';

$businessId = 1;

// Account IDs
$accBankBri = 28;   // 121 Bank BRI
$accModal   = 10;   // 310 Modal Awal
$accPenjualan = 13; // 410 Penjualan
$accPiutang   = 4;  // 130 Piutang Usaha
$accAdminBank = 30; // 610 Biaya Administrasi Bank
$accBebanGaji = 17; // 520 Beban Gaji
$accTax       = 50; // 630 Tax
$accBunga     = 31; // 611 Bunga diterima

$transactions = [
    // [tanggal, description, [ [account_id, debit, credit, line_desc], ... ]]
    [
        '2026-09-05',
        'Modal Andre F R',
        [
            [$accBankBri, 5000000.00, 0.00, 'Modal Andre F R'],
            [$accModal, 0.00, 5000000.00, 'Modal Andre F R'],
        ],
    ],
    [
        '2026-09-05',
        'Modal Heru R F',
        [
            [$accBankBri, 5000000.00, 0.00, 'Modal Heru R F'],
            [$accModal, 0.00, 5000000.00, 'Modal Heru R F'],
        ],
    ],
    [
        '2026-09-05',
        'Modal Aldi Maulana',
        [
            [$accBankBri, 5000000.00, 0.00, 'Modal Aldi Maulana'],
            [$accModal, 0.00, 5000000.00, 'Modal Aldi Maulana'],
        ],
    ],
    [
        '2026-10-04',
        'pemasukan jasa agensi',
        [
            [$accBankBri, 1903671.00, 0.00, 'Pemasukan jasa agensi'],
            [$accPenjualan, 0.00, 1903671.00, 'Pemasukan jasa agensi'],
        ],
    ],
    [
        '2026-10-08',
        'ADMIN BANK',
        [
            [$accAdminBank, 13000.00, 0.00, 'Admin Bank'],
            [$accBankBri, 0.00, 13000.00, 'Admin Bank'],
        ],
    ],
    [
        '2026-10-08',
        'gaji aldi',
        [
            [$accBebanGaji, 1000000.00, 0.00, 'Gaji Aldi'],
            [$accBankBri, 0.00, 1000000.00, 'Gaji Aldi'],
        ],
    ],
    [
        '2026-10-08',
        'gaji heru',
        [
            [$accBebanGaji, 1000000.00, 0.00, 'Gaji Heru'],
            [$accBankBri, 0.00, 1000000.00, 'Gaji Heru'],
        ],
    ],
    [
        '2026-10-08',
        'gaji andre',
        [
            [$accBebanGaji, 1000000.00, 0.00, 'Gaji Andre'],
            [$accBankBri, 0.00, 1000000.00, 'Gaji Andre'],
        ],
    ],
    [
        '2026-10-15',
        'PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany',
        [
            [$accBankBri, 200000.00, 0.00, 'Pemasukan DP Kelly Ritschel INV-20261015-00390 Germany'],
            [$accPenjualan, 0.00, 200000.00, 'Pemasukan DP Kelly Ritschel INV-20261015-00390 Germany'],
        ],
    ],
    [
        '2026-10-20',
        'PENGELUARAN TAX',
        [
            [$accTax, 1228.00, 0.00, 'Pengeluaran Tax'],
            [$accBankBri, 0.00, 1228.00, 'Pengeluaran Tax'],
        ],
    ],
    [
        '2026-10-20',
        'INTERST ON ACCOUNT',
        [
            [$accBankBri, 6141.00, 0.00, 'Interest on Account'],
            [$accBunga, 0.00, 6141.00, 'Interest on Account'],
        ],
    ],
    [
        '2026-10-22',
        'PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA "KOTOR"',
        [
            [$accBankBri, 1500000.00, 0.00, 'Pemasukan Peter Lok INV-20261021-00398 INDONESIA KOTOR'],
            [$accPenjualan, 0.00, 1500000.00, 'Pemasukan Peter Lok INV-20261021-00398 INDONESIA KOTOR'],
        ],
    ],
    [
        '2026-10-26',
        'PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA',
        [
            [$accBankBri, 2500000.00, 0.00, 'Pemasukan Vendy Andreas INV-20261026-00403 INDONESIA'],
            [$accPenjualan, 0.00, 2500000.00, 'Pemasukan Vendy Andreas INV-20261026-00403 INDONESIA'],
        ],
    ],
    [
        '2026-10-27',
        'PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA',
        [
            [$accBankBri, 300000.00, 0.00, 'Pelunasan Vendy Andreas INV-20261026-00403 INDONESIA'],
            [$accPiutang, 0.00, 300000.00, 'Pelunasan Vendy Andreas INV-20261026-00403 INDONESIA'],
        ],
    ],
    [
        '2026-11-01',
        'ADMIN BANK',
        [
            [$accAdminBank, 13000.00, 0.00, 'Admin Bank'],
            [$accBankBri, 0.00, 13000.00, 'Admin Bank'],
        ],
    ],
    [
        '2026-11-01',
        'GAJI ANDRE',
        [
            [$accBebanGaji, 1000000.00, 0.00, 'Gaji Andre'],
            [$accBankBri, 0.00, 1000000.00, 'Gaji Andre'],
        ],
    ],
    [
        '2026-11-01',
        'GAJI ALDI',
        [
            [$accBebanGaji, 1000000.00, 0.00, 'Gaji Aldi'],
            [$accBankBri, 0.00, 1000000.00, 'Gaji Aldi'],
        ],
    ],
    [
        '2026-11-01',
        'GAJI HERU',
        [
            [$accBebanGaji, 1000000.00, 0.00, 'Gaji Heru'],
            [$accBankBri, 0.00, 1000000.00, 'Gaji Heru'],
        ],
    ],
    [
        '2026-11-02',
        'PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA',
        [
            [$accBankBri, 800000.00, 0.00, 'Pendapaten Syahrun Nisa INV-20261102-00409 INDONESIA'],
            [$accPenjualan, 0.00, 800000.00, 'Pendapaten Syahrun Nisa INV-20261102-00409 INDONESIA'],
        ],
    ],
];

$insertTx = $pdo->prepare("INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (?, ?, ?, ?, 'journal')");
$insertLine = $pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (?, ?, ?, ?, ?)");

foreach ($transactions as $tx) {
    [$tgl, $desc, $lines] = $tx;
    $insertTx->execute([$businessId, 'JE-BRI-' . date('Ymd', strtotime($tgl)) . '-' . uniqid(), $desc, $tgl]);
    $txId = $pdo->lastInsertId();
    foreach ($lines as [$accId, $debit, $credit, $lineDesc]) {
        $insertLine->execute([$txId, $accId, $lineDesc, $debit, $credit]);
    }
    echo "Inserted transaction id=$txId ($tgl) - $desc\n";
}

echo "Selesai. Transaksi Bank BRI telah ditambahkan.\n";
