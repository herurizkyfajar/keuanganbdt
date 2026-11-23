USE managerio2;
SET @biz = 1;
SET @pendapatan = 12;
SET @bank_bca = 3;

-- 12/11/2026 JE-134 PENDAPETAN Nasser INV-20261110-00418 Pakistan Cr 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-134', 'PENDAPETAN Nasser INV-20261110-00418 Pakistan', '2026-11-12', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Nasser INV-20261110-00418 Pakistan', 300000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Nasser INV-20261110-00418 Pakistan', 0, 300000);

-- 13/11/2026 JE-135 PENDAPETAN Christina INV-20261113-00425 South Korea Cr 75.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-135', 'PENDAPETAN Christina INV-20261113-00425 South Korea', '2026-11-13', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Christina INV-20261113-00425 South Korea', 75000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Christina INV-20261113-00425 South Korea', 0, 75000);

-- 14/11/2026 JE-136 PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-136', 'PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan', '2026-11-14', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Nobuhiro Chiba INV-20261111-00421 Japan', 0, 100000);

-- 14/11/2026 JE-138 PENDAPETAN Kiran Kale INV-20261103-00412 India Cr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-138', 'PENDAPETAN Kiran Kale INV-20261103-00412 India', '2026-11-14', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Kiran Kale INV-20261103-00412 India', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Kiran Kale INV-20261103-00412 India', 0, 200000);

-- 14/11/2026 PENDAPETAN Maelen Smith INV-20261113-00426 Australia Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-141', 'PENDAPETAN Maelen Smith INV-20261113-00426 Australia', '2026-11-14', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Maelen Smith INV-20261113-00426 Australia', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Maelen Smith INV-20261113-00426 Australia', 0, 100000);

-- 16/11/2026 JE-140 DP PENDAPETAN Azizah INV-20261116-00427 Indonesia Cr 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-140', 'DP PENDAPETAN Azizah INV-20261116-00427 Indonesia', '2026-11-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'DP PENDAPETAN Azizah INV-20261116-00427 Indonesia', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'DP PENDAPETAN Azizah INV-20261116-00427 Indonesia', 0, 500000);

-- 17/11/2026 JE-144 PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia Cr 100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-144', 'PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia', '2026-11-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia', 100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @pendapatan, 'PENDAPETAN Kak chelsa INV-20261110-00417 Indonesia', 0, 100000);
