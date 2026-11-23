USE managerio2;
SET @biz = 1;
SET @piutang_opr = 56;
SET @kas = 2;

-- 05/10/2026 JE 8 OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026 Debit 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-8', 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026', '2026-10-05', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 04/10/2026', 0, 200000);

-- 05/10/2026 JE 9 OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026 Debit 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-9', 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026', '2026-10-05', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'OPRASIONAL TAMU Farah Dhiva Amin TANGGAL 05/10/2026', 0, 200000);

-- 07/10/2026 JE 13 ganti operasional Credit 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-13', 'ganti operasional', '2026-10-07', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'ganti operasional', 0, 200000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ganti operasional', 200000, 0);

-- 08/10/2026 JE 14 GANTI OPRASIONA 05-10-2026 Credit 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-14', 'GANTI OPRASIONAL 05-10-2026', '2026-10-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'GANTI OPRASIONAL 05-10-2026', 0, 200000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'GANTI OPRASIONAL 05-10-2026', 200000, 0);

-- 09/10/2026 JE 21 OPRASIONAL JKT Debit 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-21', 'OPRASIONAL JKT', '2026-10-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'OPRASIONAL JKT', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'OPRASIONAL JKT', 0, 600000);

-- 01/11/2026 JE 99 GANTI OPRASIONAL Credit 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-99', 'GANTI OPRASIONAL', '2026-11-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'GANTI OPRASIONAL', 0, 600000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'GANTI OPRASIONAL', 600000, 0);

-- 02/11/2026 JE 100 ANDRE OPRASIONAL JKT Debit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-100', 'ANDRE OPRASIONAL JKT', '2026-11-02', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'ANDRE OPRASIONAL JKT', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE OPRASIONAL JKT', 0, 500000);

-- 02/11/2026 JE 103 ANDRE GANTI OPRASIONAL Credit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-103', 'ANDRE GANTI OPRASIONAL', '2026-11-02', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'ANDRE GANTI OPRASIONAL', 0, 500000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE GANTI OPRASIONAL', 500000, 0);

-- 09/11/2026 JE 132 ANDRE OPRASIONAL JAKARTA Debit 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-132', 'ANDRE OPRASIONAL JAKARTA', '2026-11-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'ANDRE OPRASIONAL JAKARTA', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE OPRASIONAL JAKARTA', 0, 600000);

-- 16/11/2026 JE 139 ANDRE OPRASIONAL Debit 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-139', 'ANDRE OPRASIONAL', '2026-11-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_opr, 'ANDRE OPRASIONAL', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE OPRASIONAL', 0, 600000);
