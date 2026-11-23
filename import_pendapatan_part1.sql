USE managerio2;
SET @biz = 1;
SET @pendapatan = 12;
SET @bank_bca = 3;

-- 04/10/2026 JE-4 pemasukan jasa agensi Cr 1.903.671
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-4', 'pemasukan jasa agensi', '2026-10-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'pemasukan jasa agensi', 1903671, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'pemasukan jasa agensi', 0, 1903671);

-- 04/10/2026 JE-5 kas saat ini Cr 9.277.111
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-5', 'kas saat ini', '2026-10-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'kas saat ini', 9277111, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'kas saat ini', 0, 9277111);

-- 06/10/2026 JE-11 pemasukan Johannes Westrik INV-20261004-00378 Netherlands Cr 150.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-11', 'pemasukan Johannes Westrik INV-20261004-00378 Netherlands', '2026-10-06', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'pemasukan Johannes Westrik INV-20261004-00378 Netherlands', 150000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'pemasukan Johannes Westrik INV-20261004-00378 Netherlands', 0, 150000);

-- 07/10/2026 JE-12 pemasukan Farah Dhiya Amin INV-20261001-00374 Malaysia Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-12', 'pemasukan Farah Dhiya Amin INV-20261001-00374 Malaysia', '2026-10-07', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'pemasukan Farah Dhiya Amin INV-20261001-00374 Malaysia', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'pemasukan Farah Dhiya Amin INV-20261001-00374 Malaysia', 0, 100000);

-- 08/10/2026 JE-16 pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-16', 'pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'pemasukan Nurul Natasha Binti Mahmud INV-20260920-00350 Malaysia', 0, 100000);

-- 08/10/2026 JE-17 pemasukan Tanti INV-20261007-00381 INDONESIA Cr 75.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-17', 'pemasukan Tanti INV-20261007-00381 INDONESIA', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'pemasukan Tanti INV-20261007-00381 INDONESIA', 75000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'pemasukan Tanti INV-20261007-00381 INDONESIA', 0, 75000);

-- 14/10/2026 JE-31 PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia Cr 400.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-31', 'PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia', '2026-10-14', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 400000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN DP Tamilselvi Palanisamy INV-20260731-00244 Malaysia', 0, 400000);

-- 14/10/2026 JE-32 PEMASUKAN Olivier vis INV-20261012-00387 Netherlands Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-32', 'PEMASUKAN Olivier vis INV-20261012-00387 Netherlands', '2026-10-14', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Olivier vis INV-20261012-00387 Netherlands', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Olivier vis INV-20261012-00387 Netherlands', 0, 100000);

-- 15/10/2026 JE-42 PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-42', 'PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany', '2026-10-15', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN DP Kelly Ritschel INV-20261015-00390 Germany', 0, 200000);

-- 16/10/2026 JE-37 PEMASUKAN Kelly Ritschel INV-20261015-00390 Germany Cr 650.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-37', 'PEMASUKAN Kelly Ritschel INV-20261015-00390 Germany', '2026-10-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Kelly Ritschel INV-20261015-00390 Germany', 650000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Kelly Ritschel INV-20261015-00390 Germany', 0, 650000);

-- 16/10/2026 JE-39 PEMASUKAN Cahyani Fajar Sukoco INV-20261007-00379 INDONESIA Rp1.000.000 Cr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-39', 'PEMASUKAN Cahyani Fajar Sukoco INV-20261007-00379 INDONESIA Rp1.000.000', '2026-10-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Cahyani Fajar Sukoco INV-20261007-00379 INDONESIA Rp1.000.000', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Cahyani Fajar Sukoco INV-20261007-00379 INDONESIA Rp1.000.000', 0, 1000000);

-- 16/10/2026 JE-44 PEMASUKAN Ganason Saminathan INV-20261015-00392 Malaysia Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-44', 'PEMASUKAN Ganason Saminathan INV-20261015-00392 Malaysia', '2026-10-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Ganason Saminathan INV-20261015-00392 Malaysia', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Ganason Saminathan INV-20261015-00392 Malaysia', 0, 100000);

-- 17/10/2026 JE-45 GANTI OPRASIONAL FERDI Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-45', 'GANTI OPRASIONAL FERDI', '2026-10-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GANTI OPRASIONAL FERDI', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'GANTI OPRASIONAL FERDI', 0, 200000);

-- 18/10/2026 JE-49 PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA Cr 50.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-49', 'PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA', '2026-10-18', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA', 50000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Budi Hariyanto INV-20261015-00391 INDONESIA', 0, 50000);

-- 19/10/2026 JE-52 PEMASUKAN Daniel Martak INV-20261017-00393 France Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-52', 'PEMASUKAN Daniel Martak INV-20261017-00393 France', '2026-10-19', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Daniel Martak INV-20261017-00393 France', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Daniel Martak INV-20261017-00393 France', 0, 100000);

-- 19/10/2026 JE-54 PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1 Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-54', 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1', '2026-10-19', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 1', 0, 100000);

-- 20/10/2026 JE-55 PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2 Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-55', 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2', '2026-10-20', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN ROSITA INV-20261014-00388 Malaysia DAY 2', 0, 100000);

-- 22/10/2026 JE-58 PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA "KOTOR" Cr 1.500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-58', 'PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA "KOTOR"', '2026-10-22', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA "KOTOR"', 1500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Peter Lok INV-20261021-00398 INDONESIA "KOTOR"', 0, 1500000);

-- 22/10/2026 JE-59 PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh Cr 50.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-59', 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', '2026-10-22', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 50000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 0, 50000);

-- 22/10/2026 JE-62 PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh Cr 50.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-62', 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', '2026-10-22', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 50000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PEMASUKAN Mr Russell INV-20261021-00396 Bangladesh', 0, 50000);
