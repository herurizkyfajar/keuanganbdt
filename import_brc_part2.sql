USE managerio2;
SET @biz = 1;
SET @brc = 51;
SET @bank_bca = 3;

-- 19/10/2026 JE-50 OPRASIONAL ANDRE Dr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-50', 'OPRASIONAL ANDRE', '2026-10-19', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'OPRASIONAL ANDRE', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'OPRASIONAL ANDRE', 0, 200000);

-- 19/10/2026 JE-51 BAYAR OPRASIONAL 17/10/2026 Cr 250.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-51', 'BAYAR OPRASIONAL 17/10/2026', '2026-10-19', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'BAYAR OPRASIONAL 17/10/2026', 0, 250000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'BAYAR OPRASIONAL 17/10/2026', 250000, 0);

-- 21/10/2026 JE-57 GANTI OPRASIONAL 19/10/2026 Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-57', 'GANTI OPRASIONAL 19/10/2026', '2026-10-21', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'GANTI OPRASIONAL 19/10/2026', 0, 200000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GANTI OPRASIONAL 19/10/2026', 200000, 0);

-- 25/10/2026 JE-72 ANDRE OPRASIONAL JAKARTA Dr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-72', 'ANDRE OPRASIONAL JAKARTA', '2026-10-25', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE OPRASIONAL JAKARTA', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE OPRASIONAL JAKARTA', 0, 600000);

-- 26/10/2026 JE-75 ANDRE GANTI OPRASIONAL Cr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-75', 'ANDRE GANTI OPRASIONAL', '2026-10-26', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE GANTI OPRASIONAL', 0, 600000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE GANTI OPRASIONAL', 600000, 0);

-- 30/10/2026 ANDRE GANRI OPRASIONAL 09/10/2026 Dr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-3010', 'ANDRE GANRI OPRASIONAL 09/10/2026', '2026-10-30', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE GANRI OPRASIONAL 09/10/2026', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE GANRI OPRASIONAL 09/10/2026', 0, 600000);

-- 01/11/2026 JE-99 GANTI OPRASIONAL Cr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-99', 'GANTI OPRASIONAL', '2026-11-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'GANTI OPRASIONAL', 0, 600000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GANTI OPRASIONAL', 600000, 0);

-- 02/11/2026 JE-100 ANDRE OPRASIONAL JKT Dr 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-100', 'ANDRE OPRASIONAL JKT', '2026-11-02', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE OPRASIONAL JKT', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE OPRASIONAL JKT', 0, 500000);

-- 02/11/2026 JE-103 ANDRE GANTI OPRASIONAL Cr 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-103', 'ANDRE GANTI OPRASIONAL', '2026-11-02', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE GANTI OPRASIONAL', 0, 500000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE GANTI OPRASIONAL', 500000, 0);

-- 09/11/2026 JE-132 ANDRE OPRASIONAL JAKARTA Dr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-132', 'ANDRE OPRASIONAL JAKARTA', '2026-11-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE OPRASIONAL JAKARTA', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE OPRASIONAL JAKARTA', 0, 600000);

-- 16/11/2026 JE-139 ANDRE OPRASIONAL Dr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-139', 'ANDRE OPRASIONAL', '2026-11-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @brc, 'ANDRE OPRASIONAL', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ANDRE OPRASIONAL', 0, 600000);
