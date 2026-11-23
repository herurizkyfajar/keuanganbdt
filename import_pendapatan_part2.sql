USE managerio2;
SET @biz = 1;
SET @pendapatan = 12;
SET @bank_bca = 3;

-- 23/10/2026 JE-65 PEMASUKAN Ibu marina INV-20261022-00400 INDONESIA Cr 700.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-65', 'PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA', '2026-10-23', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA', 700000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Ibu marina  INV-20261022-00400 INDONESIA', 0, 700000);

-- 23/10/2026 JE-67 PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-67', 'PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary', '2026-10-23', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Daniella Cooper INV-20261021-00399 Hungary', 0, 100000);

-- 24/10/2026 JE-68 PEMASUKAN fei INV-20261019-00394 China Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-68', 'PEMASUKAN fei INV-20261019-00394 China', '2026-10-24', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN fei INV-20261019-00394 China', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN fei INV-20261019-00394 China', 0, 100000);

-- 24/10/2026 JE-69 ANDRE "IKUT TRANSFER" Cr 1.100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-69', 'ANDRE "IKUT TRANSFER"', '2026-10-24', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE "IKUT TRANSFER"', 1100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'ANDRE "IKUT TRANSFER"', 0, 1100000);

-- 26/10/2026 JE-76 PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA Cr 2.500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-76', 'PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA', '2026-10-26', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA', 2500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Vendy Andreas INV-20261026-00403 INDONESIA', 0, 2500000);

-- 27/10/2026 JE-79 PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA Cr 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-79', 'PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA', '2026-10-27', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA', 300000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PELUNASAN Vendy Andreas INV-20261026-00403 INDONESIA', 0, 300000);

-- 27/10/2026 JE-80 PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-80', 'PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico', '2026-10-27', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Ignacio Falcon INV-20261026-00404 Puerto Rico', 0, 100000);

-- 28/10/2026 JE-81 DP. PEMASUKAN BPN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-81', 'DP. PEMASUKAN BPN  Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', '2026-10-28', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'DP. PEMASUKAN BPN  Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'DP. PEMASUKAN BPN  Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', 0, 200000);

-- 29/10/2026 JE-82 PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia Cr 3.800.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-82', 'PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia', '2026-10-29', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 3800000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 0, 3800000);

-- 29/10/2026 JE-85 PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-85', 'PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore', '2026-10-29', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Raudha Zaini INV-20261012-00386 Singapore', 0, 100000);

-- 29/10/2026 JE-86 PEMASUKAN Muller Cecilia INV-20261028-00406 France Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-86', 'PEMASUKAN Muller Cecilia INV-20261028-00406 France', '2026-10-29', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Muller Cecilia INV-20261028-00406 France', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Muller Cecilia INV-20261028-00406 France', 0, 200000);

-- 29/10/2026 JE-87 PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI Cr 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-87', 'PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI', '2026-10-29', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI', 300000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Monisha P INV-20261023-00401 INDONESIA 2 HARI', 0, 300000);

-- 30/10/2026 JE-89 PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN Cr 1.600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-89', 'PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', '2026-10-30', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', 1600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PELUNASAN Rachmat Wilianto INV-20261028-00405 INDONESIA BPN', 0, 1600000);

-- 02/11/2026 JE-101 PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA Cr 800.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-101', 'PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA', '2026-11-02', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA', 800000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPATEN Syahrun Nisa INV-20261102-00409 INDONESIA', 0, 800000);

-- 04/11/2026 JE-106 PEMASUKAN ANDRE Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-106', 'PEMASUKAN ANDRE', '2026-11-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN ANDRE', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN ANDRE', 0, 100000);

-- 06/11/2026 JE-111 PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-111', 'PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium', '2026-11-06', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Alexandre Leytens INV-20261105-00415 Belgium', 0, 100000);

-- 06/11/2026 JE-112 PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-112', 'PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA', '2026-11-06', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Bpk Arifuddin INV-20261102-00411 INDONESIA', 0, 100000);

-- 08/11/2026 JE-117 DP Yunita widarani INV-20261104-00414 INDONESIA Cr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-117', 'DP Yunita widarani INV-20261104-00414 INDONESIA', '2026-11-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'DP Yunita widarani INV-20261104-00414 INDONESIA', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'DP Yunita widarani INV-20261104-00414 INDONESIA', 0, 1000000);

-- 09/11/2026 JE-119 Cr 2.400.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-119', '', '2026-11-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, '', 2400000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, '', 0, 2400000);

-- 09/11/2026 JE-122 PENDAPETAN Aswen INV-20261108-00416 Malaysia Cr 150.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-122', 'PENDAPETAN Aswen INV-20261108-00416 Malaysia', '2026-11-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Aswen INV-20261108-00416 Malaysia', 150000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Aswen INV-20261108-00416 Malaysia', 0, 150000);
