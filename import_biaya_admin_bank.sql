USE managerio2;
SET @biz = 1;
SET @admin_bank = 30;
SET @bank_bca = 3;

-- 04/10/2026 JE-7 admin bank bca Dr 14.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-7', 'admin bank bca', '2026-10-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'admin bank bca', 14000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'admin bank bca', 0, 14000);

-- 08/10/2026 JE-43 ADMIN BANK Dr 13.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-43', 'ADMIN BANK', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN BANK', 13000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN BANK', 0, 13000);

-- 10/10/2026 JE-24 ADMIN GOPAY ANDRE ETOLL Dr 1.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-24', 'ADMIN GOPAY ANDRE ETOLL', '2026-10-10', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN GOPAY ANDRE ETOLL', 1000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN GOPAY ANDRE ETOLL', 0, 1000);

-- 15/10/2026 JE-35 ADMIN GOPAY Dr 1.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-35', 'ADMIN GOPAY', '2026-10-15', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN GOPAY', 1000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN GOPAY', 0, 1000);

-- 16/10/2026 JE-41 ADMIN QR Dr 4.950
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-41', 'ADMIN QR', '2026-10-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN QR', 4950, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN QR', 0, 4950);

-- 23/10/2026 JE-66 ADMIN QR Dr 2.100
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-66', 'ADMIN QR', '2026-10-23', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN QR', 2100, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN QR', 0, 2100);

-- 24/10/2026 JE-70 ADMIN QR Dr 3.300
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-70', 'ADMIN QR', '2026-10-24', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN QR', 3300, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN QR', 0, 3300);

-- 29/10/2026 JE-83 ADMIN QR Dr 11.400
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-83', 'ADMIN QR', '2026-10-29', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN QR', 11400, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN QR', 0, 11400);

-- 30/10/2026 JE-90 ADMIN QR Dr 4.800
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-90', 'ADMIN QR', '2026-10-30', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN QR', 4800, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN QR', 0, 4800);

-- 01/11/2026 JE-96 ADMIN BANK Dr 13.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-96', 'ADMIN BANK', '2026-11-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN BANK', 13000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN BANK', 0, 13000);

-- 04/11/2026 JE-107 ADMIN BANK Dr 14.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-107', 'ADMIN BANK', '2026-11-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN BANK', 14000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN BANK', 0, 14000);

-- 07/11/2026 JE-115 ADMIN TF GOPAY ARDI Dr 1.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-115', 'ADMIN TF GOPAY ARDI', '2026-11-07', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN TF GOPAY ARDI', 1000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN TF GOPAY ARDI', 0, 1000);

-- 08/11/2026 JE-118 ADMIN QR Dr 3.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-118', 'ADMIN QR', '2026-11-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN QR', 3000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN QR', 0, 3000);

-- 09/11/2026 JE-120 ADMIN QR Dr 7.200
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-120', 'ADMIN QR', '2026-11-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @admin_bank, 'ADMIN QR', 7200, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ADMIN QR', 0, 7200);
