USE managerio2;
SET @biz = 1;
SET @piutang_andre = 53;
SET @kas = 2;

-- JE 6: 01/10/2026 piutang andre Debit 4.100.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-6', 'piutang andre', '2026-10-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'piutang andre', 4100000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'piutang andre', 0, 4100000);

-- JE 10: 06/10/2026 PIUTANG ANDRE Debit 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-10', 'PIUTANG ANDRE', '2026-10-06', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'PIUTANG ANDRE', 300000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'PIUTANG ANDRE', 0, 300000);

-- JE 22: 09/10/2026 GANTI PIUTANG 06/10/2026 Kredit 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-22', 'GANTI PIUTANG 06/10/2026', '2026-10-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'GANTI PIUTANG 06/10/2026', 0, 300000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'GANTI PIUTANG 06/10/2026', 300000, 0);

-- JE 28: 13/10/2026 PINJEM Debit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-28', 'PINJEM', '2026-10-13', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'PINJEM', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'PINJEM', 0, 500000);

-- JE 73: 25/10/2026 ANDRE BAYAR PIUTANG Kredit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-73', 'ANDRE BAYAR PIUTANG', '2026-10-25', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE BAYAR PIUTANG', 0, 500000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE BAYAR PIUTANG', 500000, 0);

-- JE 74: 25/10/2026 ANDRE GANTI UANG KACA MOBIL "PIUTANG" Kredit 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-74', 'ANDRE GANTI UANG KACA MOBIL "PIUTANG"', '2026-10-25', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE GANTI UANG KACA MOBIL "PIUTANG"', 0, 600000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE GANTI UANG KACA MOBIL "PIUTANG"', 600000, 0);

-- JE 77: 26/10/2026 ANDRE BAYAR PIUTANG Kredit 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-77', 'ANDRE BAYAR PIUTANG', '2026-10-26', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE BAYAR PIUTANG', 0, 300000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE BAYAR PIUTANG', 300000, 0);

-- JE 88: 29/10/2026 ANDRE NYICIL PIUTANG Kredit 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-88', 'ANDRE NYICIL PIUTANG', '2026-10-29', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE NYICIL PIUTANG', 0, 300000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE NYICIL PIUTANG', 300000, 0);

-- JE 98: 01/11/2026 ANDRE NYICIL PIUTANG Kredit 900.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-98', 'ANDRE NYICIL PIUTANG', '2026-11-01', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE NYICIL PIUTANG', 0, 900000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE NYICIL PIUTANG', 900000, 0);

-- JE 104: 02/11/2026 ANDRE NYICIL PIUTANG Kredit 600.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-104', 'ANDRE NYICIL PIUTANG', '2026-11-02', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE NYICIL PIUTANG', 0, 600000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE NYICIL PIUTANG', 600000, 0);

-- JE 105: 04/11/2026 ANDRE PINJEM Debit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-105', 'ANDRE PINJEM', '2026-11-04', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE PINJEM', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE PINJEM', 0, 500000);
