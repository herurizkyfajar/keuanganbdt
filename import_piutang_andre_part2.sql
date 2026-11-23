USE managerio2;
SET @biz = 1;
SET @piutang_andre = 53;
SET @kas = 2;

-- JE 108: 06/11/2026 ANDRE BAYAR PIUTANG Kredit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-108', 'ANDRE BAYAR PIUTANG', '2026-11-06', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE BAYAR PIUTANG', 0, 500000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE BAYAR PIUTANG', 500000, 0);

-- JE 116: 08/11/2026 ANDRE BAYAR PIUTANG Kredit 200.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-116', 'ANDRE BAYAR PIUTANG', '2026-11-08', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE BAYAR PIUTANG', 0, 200000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE BAYAR PIUTANG', 200000, 0);

-- JE 123: 09/11/2026 ANDRE BAYAR PIUTANG Kredit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-123', 'ANDRE BAYAR PIUTANG', '2026-11-09', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE BAYAR PIUTANG', 0, 500000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE BAYAR PIUTANG', 500000, 0);

-- JE 125: 10/11/2026 ANDRE BAYAR PIUTANG Kredit 700.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-125', 'ANDRE BAYAR PIUTANG', '2026-11-10', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE BAYAR PIUTANG', 0, 700000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE BAYAR PIUTANG', 700000, 0);

-- JE 141: 16/11/2026 ANDRE PINJEM Debit 700.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-141', 'ANDRE PINJEM', '2026-11-16', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE PINJEM', 700000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE PINJEM', 0, 700000);

-- JE 142: 17/11/2026 ANDRE BAYAR FEE Debit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-142', 'ANDRE BAYAR FEE', '2026-11-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'ANDRE BAYAR FEE', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'ANDRE BAYAR FEE', 0, 500000);

-- JE 143: 17/11/2026 PINJEM Debit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-143', 'PINJEM', '2026-11-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'PINJEM', 500000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'PINJEM', 0, 500000);

-- JE 145: 17/11/2026 GANTI FEE HUSEN Kredit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-145', 'GANTI FEE HUSEN', '2026-11-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'GANTI FEE HUSEN', 0, 500000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'GANTI FEE HUSEN', 500000, 0);

-- JE 146: 17/11/2026 GANTI SALDO Kredit 500.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-146', 'GANTI SALDO', '2026-11-17', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'GANTI SALDO', 0, 500000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'GANTI SALDO', 500000, 0);

-- JE 147: 18/11/2026 PINJEM SALDO BUAT ETOLL Debit 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-147', 'PINJEM SALDO BUAT ETOLL', '2026-11-18', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'PINJEM SALDO BUAT ETOLL', 300000, 0);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'PINJEM SALDO BUAT ETOLL', 0, 300000);

-- JE 148: 19/11/2026 GANTI SLADO ETOLL Kredit 300.000
INSERT INTO transactions (business_id, reference, description, transaction_date, type) VALUES (@biz, 'JE-148', 'GANTI SLADO ETOLL', '2026-11-19', 'journal');
SET @tx := LAST_INSERT_ID();
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @piutang_andre, 'GANTI SLADO ETOLL', 0, 300000);
INSERT INTO transaction_lines (transaction_id, account_id, description, debit, credit) VALUES (@tx, @kas, 'GANTI SLADO ETOLL', 300000, 0);
