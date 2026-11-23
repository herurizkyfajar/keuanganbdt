USE managerio2;
SET @biz = 1;
SET @brc = 51;
SET @bank_bca = 3;

-- 04/10/2026 JE-8 OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026 Dr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-8', 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026', '2026-10-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026', 0, 200000);

-- 05/10/2026 JE-9 OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026 Dr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-9', 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026', '2026-10-05', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026', 0, 200000);

-- 07/10/2026 JE-13 ganti operasional Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-13', 'ganti operasional', '2026-10-07', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ganti operasional', 0, 200000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ganti operasional', 200000, 0);

-- 08/10/2026 JE-14 GANTI OPRASIONAL 05-10-2026 Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-14', 'GANTI OPRASIONAL 05-10-2026', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'GANTI OPRASIONAL 05-10-2026', 0, 200000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GANTI OPRASIONAL 05-10-2026', 200000, 0);

-- 09/10/2026 JE-21 OPRASIONAL JKT Dr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-21', 'OPRASIONAL JKT', '2026-10-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'OPRASIONAL JKT', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'OPRASIONAL JKT', 0, 600000);

-- 10/10/2026 JE-23 ANDRE ETOLL Dr 50.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-23', 'ANDRE ETOLL', '2026-10-10', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE ETOLL', 50000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE ETOLL', 0, 50000);

-- 11/10/2026 JE-26 ANDRE GANTI ETOLL Cr 50.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-26', 'ANDRE GANTI ETOLL', '2026-10-11', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE GANTI ETOLL', 0, 50000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE GANTI ETOLL', 50000, 0);

-- 12/10/2026 JE-27 ANDRE OPRASIONAL JKT Dr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-27', 'ANDRE OPRASIONAL JKT', '2026-10-12', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE OPRASIONAL JKT', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE OPRASIONAL JKT', 0, 600000);

-- 13/10/2026 ANDRE OPRASIONAL 14/10/2026 Dr 250.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-1310', 'ANDRE OPRASIONAL 14/10/2026', '2026-10-13', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE OPRASIONAL 14/10/2026', 250000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE OPRASIONAL 14/10/2026', 0, 250000);

-- 17/10/2026 JE-47 ANDRE OPRASIONAL 18-10-2026 Dr 250.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-47', 'ANDRE OPRASIONAL 18-10-2026', '2026-10-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE OPRASIONAL 18-10-2026', 250000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE OPRASIONAL 18-10-2026', 0, 250000);

-- 17/10/2026 JE-46 GANTI OPRASIONAL Cr 250.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-46', 'GANTI OPRASIONAL', '2026-10-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'GANTI OPRASIONAL', 0, 250000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GANTI OPRASIONAL', 250000, 0);
