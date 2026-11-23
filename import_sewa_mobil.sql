USE managerio2;
SET @biz = 1;
SET @sewa = 37;
SET @bank_bca = 3;

-- 04/10/2026 JE-71 TRANSFER BALIK KE ANDRE Dr 1.100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-71', 'TRANSFER BALIK KE ANDRE', '2026-10-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'TRANSFER BALIK KE ANDRE', 1100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'TRANSFER BALIK KE ANDRE', 0, 1100000);

-- 15/10/2026 JE-34 BAYAR MOBIL DAN SUPIR Dr 450.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-34', 'BAYAR MOBIL DAN SUPIR', '2026-10-15', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'BAYAR MOBIL DAN SUPIR', 450000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'BAYAR MOBIL DAN SUPIR', 0, 450000);

-- 15/10/2026 JE-33 OPRASIONAL FERDI Dr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-33', 'OPRASIONAL FERDI', '2026-10-15', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'OPRASIONAL FERDI', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'OPRASIONAL FERDI', 0, 200000);

-- 16/10/2026 JE-40 SEWA DRIVER & MOBIL Dr 450.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-40', 'SEWA DRIVER & MOBIL', '2026-10-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'SEWA DRIVER & MOBIL', 450000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SEWA DRIVER & MOBIL', 0, 450000);

-- 16/10/2026 JE-38 SEWA DRIVER & MOBIL ANDRE Dr 700.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-38', 'SEWA DRIVER & MOBIL ANDRE', '2026-10-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'SEWA DRIVER & MOBIL ANDRE', 700000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SEWA DRIVER & MOBIL ANDRE', 0, 700000);

-- 23/10/2026 JE-63 SEWA DRIVER DAN MOBIL Dr 550.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-63', 'SEWA DRIVER DAN MOBIL', '2026-10-23', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'SEWA DRIVER DAN MOBIL', 550000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SEWA DRIVER DAN MOBIL', 0, 550000);

-- 27/10/2026 JE-78 SEWA DRIVER & MOBIL HIACE Vendy Andreas INV-20261026-00403 INDONESIA Dr 2.500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-78', 'SEWA DRIVER & MOBIL HIACE Vendy Andreas INV-20261026-00403 INDONESIA', '2026-10-27', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'SEWA DRIVER & MOBIL HIACE Vendy Andreas INV-20261026-00403 INDONESIA', 2500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SEWA DRIVER & MOBIL HIACE Vendy Andreas INV-20261026-00403 INDONESIA', 0, 2500000);

-- 29/10/2026 JE-84 SEWA DRIVER DAN MOBIL Dr 3.800.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-84', 'SEWA DRIVER DAN MOBIL', '2026-10-29', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'SEWA DRIVER DAN MOBIL', 3800000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SEWA DRIVER DAN MOBIL', 0, 3800000);

-- 30/10/2026 JE-92 FEE BRC Dr 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-92', 'FEE BRC', '2026-10-30', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'FEE BRC', 200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'FEE BRC', 0, 200000);

-- 30/10/2026 JE-91 BAYAR SEWA DRIVER DAN MOBIL Dr 1.400.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-91', 'BAYAR SEWA DRIVER DAN MOBIL', '2026-10-30', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'BAYAR SEWA DRIVER DAN MOBIL', 1400000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'BAYAR SEWA DRIVER DAN MOBIL', 0, 1400000);

-- 02/11/2026 JE-102 DENI NUR CAHYA SEWA DRIVER DAN MOBIL Dr 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-102', 'DENI NUR CAHYA SEWA DRIVER DAN MOBIL', '2026-11-02', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'DENI NUR CAHYA SEWA DRIVER DAN MOBIL', 600000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'DENI NUR CAHYA SEWA DRIVER DAN MOBIL', 0, 600000);

-- 07/11/2026 JE-113 RULLY Dr 1.200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-113', 'RULLY', '2026-11-07', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'RULLY', 1200000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'RULLY', 0, 1200000);

-- 07/11/2026 JE-114 ARDI Dr 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-114', 'ARDI', '2026-11-07', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'ARDI', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'ARDI', 0, 500000);

-- 09/11/2026 JE-121 SEWA DRIVER DAN MOBIL Dr 1.300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-121', 'SEWA DRIVER DAN MOBIL', '2026-11-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'SEWA DRIVER DAN MOBIL', 1300000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SEWA DRIVER DAN MOBIL', 0, 1300000);

-- 14/11/2026 JE-137 SEWA DRIVER DAN MOBIL Dr 2.000.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-137', 'SEWA DRIVER DAN MOBIL', '2026-11-14', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @sewa, 'SEWA DRIVER DAN MOBIL', 2000000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @bank_bca, 'SEWA DRIVER DAN MOBIL', 0, 2000000);
