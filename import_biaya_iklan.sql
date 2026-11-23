USE managerio2;
SET @biz = 1;
SET @iklan = 41;
SET @bank_bca = 3;

-- 11/10/2026 JE-25 BAYAR ADS Dr 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-25', 'BAYAR ADS', '2026-10-11', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @iklan, 'BAYAR ADS', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'BAYAR ADS', 0, 500000);

-- 13/10/2026 JE-30 BAYAR AI Dr 150.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-30', 'BAYAR AI', '2026-10-13', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @iklan, 'BAYAR AI', 150000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'BAYAR AI', 0, 150000);

-- 17/10/2026 JE-48 PEMBAYARAN BACK UP DATA WEBSITE Dr 1.250.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-48', 'PEMBAYARAN BACK UP DATA WEBSITE', '2026-10-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @iklan, 'PEMBAYARAN BACK UP DATA WEBSITE', 1250000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMBAYARAN BACK UP DATA WEBSITE', 0, 1250000);

-- 06/11/2026 JE-110 PERPANJANG AI Dr 150.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-110', 'PERPANJANG AI', '2026-11-06', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @iklan, 'PERPANJANG AI', 150000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PERPANJANG AI', 0, 150000);

-- 10/11/2026 JE-127 PEMBELIAN FOLLOWERS IG Dr 120.619
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-127', 'PEMBELIAN FOLLOWERS IG', '2026-11-10', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @iklan, 'PEMBELIAN FOLLOWERS IG', 120619, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PEMBELIAN FOLLOWERS IG', 0, 120619);

-- 11/11/2026 JE-129 SUNTIK FOLLOWERS Dr 120.619
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-129', 'SUNTIK FOLLOWERS', '2026-11-11', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @iklan, 'SUNTIK FOLLOWERS', 120619, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SUNTIK FOLLOWERS', 0, 120619);

-- 12/11/2026 JE-131 SUNTIK FOLLOWERS IG Dr 160.825
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-131', 'SUNTIK FOLLOWERS IG', '2026-11-12', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @iklan, 'SUNTIK FOLLOWERS IG', 160825, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SUNTIK FOLLOWERS IG', 0, 160825);
