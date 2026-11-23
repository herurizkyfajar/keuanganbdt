USE managerio2;
SET @biz = 1;
SET @web = 43;
SET @bank_bca = 3;

-- 08/10/2026 JE-15 pemasukan Dennis Riedeman INV-20261007-00382 Netherlands Cr 50.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-15', 'pemasukan Dennis Riedeman INV-20261007-00382 Netherlands', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'pemasukan Dennis Riedeman INV-20261007-00382 Netherlands', 50000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @web, 'pemasukan Dennis Riedeman INV-20261007-00382 Netherlands', 0, 50000);

-- 12/11/2026 JE-133 PENDAPETAN Dali Schmölzer INV-20261110-00420 Netherlands Cr 75.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-133', 'PENDAPETAN Dali Schmölzer INV-20261110-00420 Netherlands', '2026-11-12', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'PENDAPETAN Dali Schmölzer INV-20261110-00420 Netherlands', 75000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @web, 'PENDAPETAN Dali Schmölzer INV-20261110-00420 Netherlands', 0, 75000);
