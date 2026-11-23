USE managerio2;
SET @biz = 1;
SET @gaji = 48;
SET @bank_bca = 3;

-- 08/10/2026 JE-18 gaji aldi Dr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-18', 'gaji aldi', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @gaji, 'gaji aldi', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'gaji aldi', 0, 1000000);

-- 08/10/2026 JE-20 gaji heru Dr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-20', 'gaji heru', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @gaji, 'gaji heru', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'gaji heru', 0, 1000000);

-- 08/10/2026 JE-19 gaji andre Dr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-19', 'gaji andre', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @gaji, 'gaji andre', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'gaji andre', 0, 1000000);

-- 01/11/2026 JE-94 GAJI ANDRE Dr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-94', 'GAJI ANDRE', '2026-11-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @gaji, 'GAJI ANDRE', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GAJI ANDRE', 0, 1000000);

-- 01/11/2026 JE-93 GAJI ALDI Dr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-93', 'GAJI ALDI', '2026-11-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @gaji, 'GAJI ALDI', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GAJI ALDI', 0, 1000000);

-- 01/11/2026 JE-95 GAJI HERU Dr 1.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-95', 'GAJI HERU', '2026-11-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @gaji, 'GAJI HERU', 1000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'GAJI HERU', 0, 1000000);
