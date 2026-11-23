<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
session_start();

// Require login for all pages except login.php
$currentFile = basename($_SERVER['PHP_SELF']);
if ($currentFile !== 'login.php' && $currentFile !== 'logout.php' && empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['business_id'])) {
    $stmt = $pdo->query("SELECT id, name, currency FROM businesses ORDER BY id ASC LIMIT 1");
    $biz = $stmt->fetch();
    $_SESSION['business_id'] = $biz ? $biz['id'] : null;
    $_SESSION['business_name'] = $biz ? $biz['name'] : 'Manajer Akuntansi';
    $_SESSION['business_currency'] = $biz ? $biz['currency'] : 'IDR';
}
$businessId = $_SESSION['business_id'];
$businessName = $_SESSION['business_name'] ?? 'Manajer Akuntansi';
$businessCurrency = $_SESSION['business_currency'] ?? 'IDR';

// Bootstrap tabel fitur AI (jalankan sekali, aman dipanggil berulang)
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS business_ai_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            business_id INT UNSIGNED NOT NULL,
            provider ENUM('openai','custom','ollama') DEFAULT 'custom',
            api_key TEXT,
            base_url VARCHAR(500),
            model VARCHAR(100) DEFAULT 'gpt-3.5-turbo',
            temperature DECIMAL(3,2) DEFAULT 0.7,
            max_tokens INT UNSIGNED DEFAULT 1000,
            is_enabled TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
            UNIQUE KEY unique_business_ai (business_id)
        ) ENGINE=InnoDB;
        CREATE TABLE IF NOT EXISTS ai_chat_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            business_id INT UNSIGNED NOT NULL,
            role ENUM('user','assistant','system') NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
            INDEX idx_ai_chat_business (business_id, created_at)
        ) ENGINE=InnoDB;
    ");
} catch (PDOException $e) {
    // Tabel akan dibuat melalui database.sql jika gagal; tidak mengganggu aplikasi
}

$userName = $_SESSION['user_name'] ?? 'Pengguna';
$userRole = $_SESSION['user_role'] ?? 'user';
$isLoginPage = ($currentFile === 'login.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($businessName) ?> - Manajer Akuntansi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
<?php if (!$isLoginPage): ?>
  <!-- Sidebar (full height, no separate topbar) -->
  <nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="brand-icon"><i class="bi bi-building"></i></span>
      <div class="brand-info">
        <div class="brand-name"><?= htmlspecialchars($businessName) ?></div>
        <div class="brand-currency"><?= htmlspecialchars($businessCurrency) ?></div>
      </div>
    </div>

    <div class="sidebar-search">
      <input type="text" placeholder="Cari modul...">
    </div>

    <nav>
      <div class="nav-section">UTAMA</div>
      <ul class="nav-list">
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'chart-of-accounts.php' ? 'active' : '' ?>" href="chart-of-accounts.php">
            <i class="bi bi-journal-text"></i> Chart of Accounts
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'journal-entries.php' ? 'active' : '' ?>" href="journal-entries.php">
            <i class="bi bi-currency-exchange"></i> Jurnal Umum
          </a>
        </li>
      </ul>

      <div class="nav-section">TRANSAKSI</div>
      <ul class="nav-list">
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'sales-invoices.php' ? 'active' : '' ?>" href="sales-invoices.php">
            <i class="bi bi-receipt-cutoff"></i> Faktur Penjualan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'purchase-invoices.php' ? 'active' : '' ?>" href="purchase-invoices.php">
            <i class="bi bi-cart-fill"></i> Faktur Pembelian
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'bank-accounts.php' ? 'active' : '' ?>" href="bank-accounts.php">
            <i class="bi bi-building"></i> Rekening Bank
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'expense-claims.php' ? 'active' : '' ?>" href="expense-claims.php">
            <i class="bi bi-wallet2"></i> Klaim Biaya
          </a>
        </li>
      </ul>

      <div class="nav-section">MASTER DATA</div>
      <ul class="nav-list">
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'customers.php' ? 'active' : '' ?>" href="customers.php">
            <i class="bi bi-people"></i> Pelanggan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'suppliers.php' ? 'active' : '' ?>" href="suppliers.php">
            <i class="bi bi-person-badge"></i> Pemasok
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'items.php' ? 'active' : '' ?>" href="items.php">
            <i class="bi bi-box-seam"></i> Barang &amp; Jasa
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'chart-of-accounts.php' ? 'active' : '' ?>" href="chart-of-accounts.php">
            <i class="bi bi-journal-text"></i> Kelola Data Akun
          </a>
        </li>
      </ul>

      <div class="nav-section">LAPORAN</div>
      <ul class="nav-list">
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'reports.php' ? 'active' : '' ?>" href="reports.php">
            <i class="bi bi-graph-up"></i> Laporan Keuangan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'grafik.php' ? 'active' : '' ?>" href="grafik.php">
            <i class="bi bi-bar-chart"></i> Grafik Keuangan
          </a>
        </li>
      </ul>

      <div class="nav-section">PENGATURAN</div>
      <ul class="nav-list">
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'settings.php' ? 'active' : '' ?>" href="settings.php">
            <i class="bi bi-gear"></i> Pengaturan Perusahaan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'users.php' ? 'active' : '' ?>" href="users.php">
            <i class="bi bi-people"></i> Kelola Pengguna Admin
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'profile.php' ? 'active' : '' ?>" href="profile.php">
            <i class="bi bi-person"></i> Kelola Profil Akun
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'import.php' ? 'active' : '' ?>" href="import.php">
            <i class="bi bi-upload"></i> Import Data CSV
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'export.php' ? 'active' : '' ?>" href="export.php">
            <i class="bi bi-download"></i> Ekspor Data SQL
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentFile == 'ai-settings.php' ? 'active' : '' ?>" href="ai-settings.php">
            <i class="bi bi-robot"></i> Asisten AI
          </a>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-name"><?= htmlspecialchars($userName) ?></div>
        <div class="sidebar-user-role"><?= htmlspecialchars($userRole) ?></div>
      </div>
      <a href="logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
      <div style="font-size:0.7rem; color:#999; margin-top:0.5rem;">v2.0 &bull; <?php echo date('Y'); ?></div>
    </div>
  </nav>

  <!-- Sidebar backdrop (mobile) -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<?php endif; ?>

  <!-- Main Content -->
  <main class="main">
    <div class="main-inner">
      <!-- Breadcrumb / company pill -->
      <?php if (!$isLoginPage): ?>
      <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
        <span style="background:#fff; border:1px solid var(--border); border-radius:2rem; padding:0.35rem 0.9rem; font-size:0.82rem; color:var(--muted); font-weight:600; box-shadow:var(--shadow);">
          <i class="bi bi-building me-1" style="font-size:0.75rem;"></i>
          <?= htmlspecialchars($businessName) ?>
        </span>
      </div>
      <?php endif; ?>

      <header class="page-header">
        <div class="page-title-box">
          <h1 id="pageTitle"></h1>
          <p id="pageSubtitle"></p>
        </div>
        <div class="page-actions" id="pageActions"></div>
      </header>
