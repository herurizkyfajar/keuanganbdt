# ManagerIO2 — Aplikasi Akuntansi Web (PHP + MySQL)

Aplikasi akuntansi berbasis browser dengan tampilan dan fungsi mirip **Manager.io**, dibangun dari nol dengan **PHP + MySQL**, siap dijalankan di Laragon atau shared hosting.

## Tampilan (Mirip Manager.io)

Antarmuka berbentuk desktop app, sangat mirip Manager.io:
- **Sidebar penuh tinggi** di kiri dengan branding perusahaan di atas, search bar, dan navigasi modul
- **Section header**: UTAMA, TRANSAKSI, MASTER DATA, LAPORAN, PENGATURAN
- **Breadcrumb pill** dengan nama perusahaan di atas konten
- **Kartu statistik** di dashboard (Total Aset, Saldo Bank, Faktur Belum Lunas, Hutang Belum Lunas)
- **Panel kanan** Ringkasan Akun di dashboard
- **Tabel data** dengan badge tipe transaksi dan jumlah dalam format Indonesia (titik sebagai pemisah ribuan)
- **Modal form** untuk input data
- Responsive untuk mobile

## Modul Utama

| Modul | Fungsi |
|---|---|
| Dashboard | Ringkasan aset, bank, piutang, hutang, dan transaksi terbaru |
| Chart of Accounts | Rekening dengan hierarki induk/anak dan tipe |
| Jurnal Umum | Input transaksi debit/kredit dengan validasi saldo pas |
| Faktur Penjualan | Buat faktur pelanggan dengan item dan status |
| Faktur Pembelian | Buat faktur pemasok dengan item dan status |
| Rekening Bank | Kelola rekening bank dan saldo pembuka |
| Pelanggan | Data pelanggan & saldo piutang |
| Pemasok | Data pemasok & saldo hutang |
| Barang & Jasa | Master produk dan jasa dengan harga jual/beli |
| Klaim Biaya | Input dan verifikasi klaim biaya karyawan |
| Laporan | Laba Rugi, Neraca, Neraca Saldo (range tanggal) |
| Pengaturan | Profil perusahaan & mata uang |

## Struktur Folder

```
managerio2/
├─ config/
│  └─ database.php          Koneksi PDO MySQL
├─ includes/
│  ├─ header.php            Sidebar + navigasi + breadcrumb
│  └─ footer.php            Modal JS + penutup halaman
├─ assets/
│  ├─ css/style.css         Gaya mirip Manager.io (desktop-style)
│  └─ js/main.js            Interaksi modal, tabel, validasi
├─ dashboard.php
├─ chart-of-accounts.php
├─ journal-entries.php
├─ sales-invoices.php
├─ purchase-invoices.php
├─ bank-accounts.php
├─ customers.php
├─ suppliers.php
├─ items.php
├─ expense-claims.php
├─ reports.php
├─ settings.php
├─ database.sql             Skema + data contoh
└─ README.md
```

## Instalasi di Laragon

1. Aktifkan Apache/Nginx dan MySQL di Laragon.
2. Impor skema database:

   ```sql
   -- Di phpMyAdmin atau MySQL CLI
   source C:\laragon\www\managerio2\database.sql;
   ```

3. Buka browser:
   ```
   http://localhost/managerio2/dashboard.php
   ```

## Instalasi di Shared Hosting

1. Unggah isi folder ke `public_html`.
2. Buat database MySQL di cPanel.
3. Edit `config/database.php` dengan kredensial hosting.
4. Impor `database.sql` via phpMyAdmin.
5. Buka domain Anda.

## Data Contoh (termasuk di database.sql)

- Perusahaan: CV Contoh Usaha (IDR)
- Akun: Kas, Bank BCA, Piutang Usaha, Persediaan, Utang Dagang, Modal Awal, Penjualan, Beban Listrik, Beban Gaji, dll.
- Pelanggan & pemasok contoh
- Item contoh: Laptop ASUS, Konsultasi Pajak, Tinta Printer
- Transaksi contoh: penjualan tunai, pembelian utang, pembayaran listrik

## Catatan

- Ini adalah aplikasi **baru yang dibangun sendiri**, tidak ada modifikasi pada binary proprietary Manager.io atau ManagerServer.exe.
- Data tersimpan di **MySQL**, cocok untuk shared hosting dan akses online.
- Untuk produksi, tambahkan autentikasi login, manajemen user, dan backup database teratur.
