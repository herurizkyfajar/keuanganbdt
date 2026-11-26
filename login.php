<?php
// login.php - Halaman login standalone (tanpa sidebar/header app)
require_once __DIR__ . '/config/database.php';

// Secure session cookie settings
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
ini_set('session.cookie_secure', $isHttps ? '1' : '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

session_start();

// If already logged in, redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Create failed_logins table for rate limiting (brute-force protection)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS failed_logins (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_ip (ip_address),
        INDEX idx_attempted (attempted_at)
    ) ENGINE=InnoDB;
");

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Permintaan tidak valid. Silakan coba lagi.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Rate limiting: maksimal 5 percobaan per 15 menit per email atau IP
        $window = 15 * 60; // 15 menit
        $maxAttempts = 5;

        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM failed_logins WHERE (email = ? OR ip_address = ?) AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$email, $ip, $window]);
        $attempts = (int)$stmt->fetchColumn();

        if ($attempts >= $maxAttempts) {
            $error = 'Terlalu banyak percobaan login gagal. Silakan coba lagi dalam 15 menit.';
        } elseif (empty($email) || empty($password)) {
            $error = 'Email dan password harus diisi.';
        } else {
            $stmt = $pdo->prepare("SELECT id, email, password, name, role, is_active FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if (!$user['is_active']) {
                    $error = 'Akun Anda tidak aktif. Hubungi administrator.';
                } else {
                    // Hapus catatan gagal login saat berhasil
                    $del = $pdo->prepare("DELETE FROM failed_logins WHERE email = ? OR ip_address = ?");
                    $del->execute([$email, $ip]);

                    // Regenerate session ID untuk mencegah session fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    if (empty($_SESSION['business_id'])) {
                        $biz = $pdo->query("SELECT id FROM businesses ORDER BY id ASC LIMIT 1")->fetch();
                        $_SESSION['business_id'] = $biz ? (int)$biz['id'] : null;
                        $_SESSION['business_name'] = $biz ? $biz['name'] : 'Manajer Akuntansi';
                    }

                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                // Catat percobaan gagal
                $ins = $pdo->prepare("INSERT INTO failed_logins (email, ip_address) VALUES (?, ?)");
                $ins->execute([$email, $ip]);
                $error = 'Email atau password salah.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk - Bandung Driver Tour</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root {
    --bd-blue-deep: #0b4f9e;
    --bd-blue: #1565c0;
    --bd-blue-mid: #1976d2;
    --bd-green: #8bc34a;
    --bd-green-dark: #689f38;
    --bd-navy: #0d2b52;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  html, body {
    width: 100%;
    height: 100%;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: var(--bd-navy);
    -webkit-font-smoothing: antialiased;
  }

  /* ================= Full-screen layout ================= */
  .login-layout {
    display: flex;
    width: 100vw;
    height: 100vh;
    min-height: 100vh;
    position: relative;
    overflow: hidden;
    background: #ffffff;
  }

  /* ---------- Left Panel (Hero) ---------- */
  .login-hero {
    flex: 1 1 55%;
    position: relative;
    background: url('assets/img/gedung-sate-drive.png') 35% 50%/cover no-repeat;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 2.8rem 3.5rem 0 4rem;
    min-width: 0;
  }

  /* No overlay - background image visible clearly */
  .login-hero::before {
    display: none;
  }

  .login-hero::after {
    display: none;
  }

  .hero-top {
    position: relative;
    z-index: 3;
    flex-shrink: 0;
  }

  /* Logo */
  .logo-inline {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 2.6rem;
  }
  .logo-inline .logo-img {
    width: 200px;
    height: auto;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
  }

  /* Headline */
  .hero-headline {
    font-size: 3rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 1.15rem;
    letter-spacing: -1px;
    color: #ffffff;
    text-shadow: 0 2px 8px rgba(0,0,0,0.55);
  }
  .hero-headline .bandung {
    color: #b9f762;
    text-shadow: 0 2px 8px rgba(0,0,0,0.55);
  }

  .hero-sub {
    font-size: 1.1rem;
    line-height: 1.65;
    color: #ffffff;
    max-width: 500px;
    margin-bottom: 2.4rem;
    font-weight: 400;
    text-shadow: 0 1px 4px rgba(0,0,0,0.45);
  }

  /* Feature boxes */
  .hero-features {
    display: flex;
    gap: 1.15rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }
  .feature-box {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    background: rgba(255,255,255,0.96);
    padding: 0.85rem 1.15rem;
    border-radius: 0.9rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.14);
    min-width: 175px;
  }
  .feature-box .feat-icon {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--bd-blue);
    border-radius: 0.7rem;
    flex-shrink: 0;
  }
  .feature-box .feat-icon svg {
    width: 22px;
    height: 22px;
  }
  .feature-box .feat-label {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--bd-navy);
    line-height: 1.35;
  }

  /* Car illustration area - fills remaining space */
  .hero-image-wrap {
    position: relative;
    z-index: 2;
    flex: 1;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    min-height: 0;
    margin-left: -4rem;
    margin-right: -3.5rem;
  }
  .hero-car-svg {
    width: 100%;
    max-width: 700px;
    height: auto;
    display: block;
    filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));
  }

  /* ---------- Curved green swoosh divider ---------- */
  .swoosh-divider {
    position: absolute;
    top: 0;
    right: -10px;
    width: 240px;
    height: 100%;
    z-index: 10;
    pointer-events: none;
  }
  .swoosh-divider svg {
    width: 100%;
    height: 100%;
    display: block;
  }

  /* ---------- Right Panel (Login Card) ---------- */
  .login-card-panel {
    flex: 1 1 45%;
    position: relative;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 2rem;
    z-index: 4;
    min-width: 0;
    overflow: hidden;
  }

  /* City skyline silhouette */
  .city-skyline {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 160px;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
  }
  .city-skyline svg {
    width: 100%;
    height: 100%;
    display: block;
    opacity: 0.45;
  }

  /* Green accent bottom-right */
  .green-accent {
    position: absolute;
    bottom: -30px;
    right: -30px;
    width: 280px;
    height: 280px;
    z-index: 1;
    pointer-events: none;
  }
  .green-accent svg {
    width: 100%;
    height: 100%;
    display: block;
  }

  .login-card {
    position: relative;
    z-index: 3;
    width: 100%;
    max-width: 430px;
    background: #ffffff;
    border-radius: 1.6rem;
    padding: 2.8rem 2.5rem;
    box-shadow: 0 28px 80px rgba(0,0,0,0.13), 0 6px 20px rgba(0,0,0,0.06);
  }

  .login-card .logo-center {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
  }
  .login-card .logo-img-small {
    width: 120px;
    height: auto;
  }

  .login-card h2 {
    font-size: 1.9rem;
    font-weight: 800;
    color: var(--bd-navy);
    text-align: center;
    margin: 0 0 0.55rem;
    letter-spacing: -0.4px;
  }

  .login-card .sub {
    font-size: 0.93rem;
    color: #4b5563;
    text-align: center;
    margin-bottom: 2rem;
    line-height: 1.55;
  }

  .login-card .form-group {
    margin-bottom: 1.2rem;
  }
  .login-card .form-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--bd-navy);
    margin-bottom: 0.5rem;
  }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }
  .input-wrapper .input-icon {
    position: absolute;
    left: 1.05rem;
    color: #9ca3af;
    font-size: 1.05rem;
    pointer-events: none;
    z-index: 3;
    display: inline-flex;
    align-items: center;
  }
  .input-wrapper input {
    width: 100%;
    padding: 0.9rem 1rem 0.9rem 3rem;
    border: 1.5px solid #e0e4ea;
    border-radius: 1rem;
    font-size: 0.95rem;
    color: #1f2937;
    background: #f8f9fb;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    outline: none;
  }
  .input-wrapper input::placeholder {
    color: #9ca3af;
  }
  .input-wrapper input:focus {
    border-color: var(--bd-blue);
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(21,101,192,0.10);
  }

  .input-wrapper .password-toggle {
    position: absolute;
    right: 1.05rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    padding: 0.4rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    z-index: 4;
    transition: color 0.2s;
  }
  .input-wrapper .password-toggle:hover {
    color: var(--bd-blue);
  }
  .input-wrapper .password-toggle i {
    font-size: 1.1rem;
  }

  .btn-masuk {
    width: 100%;
    padding: 1rem 1rem;
    background: linear-gradient(135deg, #0d6efd 0%, #1565c0 60%, #0b4f9e 100%);
    color: #ffffff;
    border: none;
    border-radius: 1rem;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    margin-top: 0.4rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    transition: transform 0.15s, box-shadow 0.2s;
    outline: none;
    box-shadow: 0 8px 24px rgba(13,110,253,0.30);
  }
  .btn-masuk:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(13,110,253,0.40);
  }
  .btn-masuk:active {
    transform: translateY(0);
  }
  .btn-masuk i {
    font-size: 1rem;
  }

  .login-card .footer-note {
    margin-top: 1.8rem;
    text-align: center;
    font-size: 0.88rem;
    color: #6b7280;
    line-height: 1.5;
  }
  .login-card .footer-note a {
    color: var(--bd-green-dark);
    font-weight: 700;
    text-decoration: none;
    transition: opacity 0.2s;
  }
  .login-card .footer-note a:hover {
    opacity: 0.85;
    text-decoration: underline;
  }

  .error-alert {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 0.8rem 1rem;
    border-radius: 0.9rem;
    font-size: 0.86rem;
    margin-bottom: 1.4rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }

  /* ================= Responsive ================= */
  @media (max-width: 992px) {
    .login-layout {
      flex-direction: column;
      height: auto;
      min-height: 100vh;
      overflow: visible;
    }
    .login-hero {
      min-height: 380px;
      padding: 2.2rem 1.5rem 2rem;
      flex: none;
    }
    .login-hero::before {
      width: 400px;
      height: 400px;
      top: -15%;
      left: -5%;
    }
    .hero-headline { font-size: 2.1rem; }
    .hero-sub { font-size: 0.98rem; max-width: 100%; }
    .hero-image-wrap { display: none; }
    .swoosh-divider { display: none; }
    .hero-features { gap: 0.75rem; }
    .feature-box { min-width: 0; flex: 1; }

    .login-card-panel {
      flex: none;
      width: 100%;
      padding: 2rem 1rem;
      min-height: auto;
    }
    .login-card {
      max-width: 100%;
      padding: 2rem 1.5rem;
      border-radius: 1.2rem;
      box-shadow: 0 12px 40px rgba(0,0,0,0.08);
    }
    .city-skyline { height: 100px; }
  }

  @media (max-width: 480px) {
    .hero-headline { font-size: 1.75rem; }
    .login-card { padding: 1.75rem 1.1rem; }
    .login-card h2 { font-size: 1.55rem; }
    .btn-masuk { padding: 0.85rem 1rem; font-size: 0.95rem; }
    .hero-features { flex-direction: column; }
  }

  /* ---------- Login page footer (menempel di bawah, di atas gambar) ---------- */
  .login-layout .app-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 0.9rem 1.5rem;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(13,43,82,0.10);
    font-size: 0.82rem;
    color: var(--bd-navy);
    z-index: 10;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: nowrap;
    width: 100%;
    min-width: 0;
  }
  .login-layout .app-footer .footer-container {
    width: 100%;
    min-width: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: nowrap;
  }
  .login-layout .app-footer .footer-copyright {
    font-weight: 700;
    letter-spacing: -0.2px;
    white-space: nowrap;
    min-width: 0;
    overflow: hidden;
  }
  .login-layout .app-footer .footer-credit {
    font-size: 0.8rem;
    color: var(--bd-navy);
    opacity: 0.9;
    white-space: nowrap;
    min-width: 0;
    overflow: hidden;
    margin-left: auto;
  }
  .login-layout .app-footer .footer-credit a {
    color: var(--bd-blue);
    font-weight: 700;
    text-decoration: underline;
    text-underline-offset: 2px;
    transition: color 0.2s ease;
    white-space: nowrap;
  }
  .login-layout .app-footer .footer-credit a:hover {
    color: var(--bd-blue-mid);
  }
  /* jaga konten tidak tertutup oleh footer absolute */
  .login-layout > .login-hero { padding-bottom: 3.2rem; }
  .login-layout > .login-card-panel { padding-bottom: 3.2rem; }

  /* ================= App Launcher (tombol aplikasi) ================= */
  .app-launcher-wrap {
    position: absolute;
    top: 1.2rem;
    right: 1.2rem;
    z-index: 6;
  }
  .app-launcher-btn {
    position: relative;
    width: 3.2rem;
    height: 3.2rem;
    border-radius: 50%;
    background: rgba(20, 30, 48, 0.62);
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 18px rgba(0,0,0,0.35);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    transition: transform 0.15s, box-shadow 0.2s, background 0.2s;
    outline: none;
  }
  .app-launcher-btn:hover {
    background: rgba(20, 30, 48, 0.78);
    box-shadow: 0 8px 24px rgba(0,0,0,0.45);
    transform: translateY(-1px);
  }
  .app-launcher-btn:active {
    transform: scale(0.96);
  }
  .app-launcher-dots {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(3, 1fr);
    gap: 0.22rem;
    width: 62%;
    height: 62%;
  }
  .app-launcher-dot {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
  }

  /* Popup backdrop & panel - Menu Favorit */
  .app-launcher-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.35);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    z-index: 100;
    display: none;
    padding: 1rem;
  }
  .app-launcher-popup {
    position: fixed;
    top: 1.2rem;
    right: 1.2rem;
    width: 320px;
    max-width: calc(100vw - 2.4rem);
    max-height: 90vh;
    background: #ffffff;
    border-radius: 1.5rem;
    box-shadow: 0 25px 60px rgba(0,0,0,0.25);
    z-index: 105;
    display: none;
    flex-direction: column;
    overflow: hidden;
    color: #111827;
    animation: popupIn 0.2s cubic-bezier(0.18, 0.89, 0.32, 1.28) forwards;
    opacity: 0;
  }
  .app-launcher-popup.open {
    display: flex;
    transform: none;
    opacity: 1;
  }
  @keyframes popupIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  /* Header biru gradien */
  .app-launcher-header {
    background: linear-gradient(135deg, #0b4f9e 0%, #1565c0 60%, #1976d2 100%);
    padding: 1rem 1.25rem 0.75rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
  }
  .app-launcher-header::after {
    content: "";
    position: absolute;
    right: -40px;
    top: -40px;
    width: 120px;
    height: 120px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
  }
  .app-launcher-brand {
    display: flex;
    align-items: center;
    gap: 0.85rem;
  }
  .app-launcher-brand .brand-icon {
    width: 2.75rem;
    height: 2.75rem;
    background: #ffffff;
    border-radius: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  }
  .app-launcher-brand .brand-icon i {
    color: #0b4f9e;
    font-size: 1.35rem;
  }
  .app-launcher-brand-info h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
    line-height: 1.2;
  }
  .app-category-section {
    margin-bottom: 1.4rem;
  }
  .app-category-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 0.75rem;
    padding-left: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .app-category-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.9rem 0.7rem;
  }

  /* Grid 3 kolom (container section) */
  .app-launcher-grid {
    display: block;
    padding: 0.9rem 1rem 1rem;
    overflow-y: auto;
    flex: 1;
  }
  .app-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.45rem;
    text-align: center;
    text-decoration: none;
    color: #374151;
    padding: 0.65rem 0.3rem;
    border-radius: 1rem;
    transition: background 0.15s, transform 0.1s;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.15;
  }
  .app-item:hover { background: #f3f4f6; }
  .app-item:active { transform: scale(0.96); }
  .app-item .app-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  }
  .app-item .app-label {
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.15;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 80px;
    color: #111827;
  }

  /* Dekorasi lingkaran di pojok kanan bawah popup - DIHAPUS */

  @media (max-width: 640px) {
    .app-launcher-wrap { top: 1rem; right: 1rem; }
    .app-launcher-popup { top: 0.8rem; right: 0.8rem; left: 0.8rem; width: auto; max-height: 92vh; }
    .app-launcher-grid { padding: 0.7rem 0.7rem 0.9rem; }
  }
</style>
</head>
<body>

<div class="login-layout">

  <!-- Tombol launcher aplikasi di kanan atas layar -->
  <div class="app-launcher-wrap">
    <button type="button" class="app-launcher-btn" id="appLauncherBtn" title="Aplikasi" aria-label="Buka aplikasi" aria-expanded="false" aria-controls="appLauncherPopup">
      <span class="app-launcher-dots" aria-hidden="true">
        <span class="app-launcher-dot"></span><span class="app-launcher-dot"></span><span class="app-launcher-dot"></span>
        <span class="app-launcher-dot"></span><span class="app-launcher-dot"></span><span class="app-launcher-dot"></span>
        <span class="app-launcher-dot"></span><span class="app-launcher-dot"></span><span class="app-launcher-dot"></span>
      </span>
    </button>
  </div>

  <!-- Left: Hero panel -->
  <div class="login-hero">
    <div class="hero-top">
      <div class="logo-inline">
        <img src="assets/img/logo-bandung-driver-tour.webp" alt="Bandung Driver Tour" class="logo-img">
      </div>

      <h1 class="hero-headline">
        Sistem Akuntansi<br><span class="bandung">PT Bandung Driver Tour</span>
      </h1>
      <p class="hero-sub">
        Aplikasi manajemen keuangan dan akuntansi untuk mencatat transaksi, mengelola laporan keuangan, dan memonitor pertumbuhan bisnis Bandung Driver Tour secara terstruktur dan terintegrasi.
      </p>

      <div class="hero-features">
        <div class="feature-box">
          <span class="feat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="3"/></svg>
          </span>
          <span class="feat-label">Manajemen<br>Transaksi Keuangan</span>
        </div>
        <div class="feature-box">
          <span class="feat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><line x1="8" y1="8" x2="12" y2="12"/></svg>
          </span>
          <span class="feat-label">Laporan<br>Keuangan &amp; Jurnal</span>
        </div>
        <div class="feature-box">
          <span class="feat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
          </span>
          <span class="feat-label">Dashboard<br>Bisnis Realtime</span>
        </div>
      </div>
    </div>

    <!-- Car photo is now part of background image -->
    <div class="hero-image-wrap" style="display:none;">
      <svg class="hero-car-svg" viewBox="0 0 700 280" xmlns="http://www.w3.org/2000/svg">
        <!-- Road -->
        <rect x="0" y="230" width="700" height="50" fill="#1a5c9e" opacity="0.5"/>
        <line x1="0" y1="255" x2="700" y2="255" stroke="#ffffff" stroke-width="2" stroke-dasharray="30 20" opacity="0.4"/>

        <!-- Car body - white Toyota Innova style MPV -->
        <g transform="translate(80, 40)">
          <!-- Shadow -->
          <ellipse cx="280" cy="215" rx="240" ry="18" fill="#0a3d75" opacity="0.4"/>

          <!-- Main body lower -->
          <path d="M40 180 L45 130 Q50 115 70 110 L200 100 L420 95 Q460 93 490 100 L530 115 Q555 125 565 145 L570 180 Z" fill="#f5f5f5" stroke="#e0e0e0" stroke-width="1"/>

          <!-- Main body upper (cabin) -->
          <path d="M80 115 L120 55 Q130 42 150 40 L370 38 Q400 38 420 48 L470 80 L490 105 L200 108 Z" fill="#ffffff" stroke="#e8e8e8" stroke-width="1"/>

          <!-- Windshield -->
          <path d="M130 65 L155 48 L365 46 L400 55 L440 85 L140 90 Z" fill="#b3d9f2" opacity="0.85"/>

          <!-- Side windows -->
          <path d="M150 68 L155 50 L230 49 L230 88 L148 88 Z" fill="#a8d4f0" opacity="0.9"/>
          <path d="M240 49 L310 48 L310 88 L240 88 Z" fill="#a8d4f0" opacity="0.9"/>
          <path d="M320 48 L370 47 L400 55 L430 82 L320 87 Z" fill="#a8d4f0" opacity="0.9"/>

          <!-- Window pillars -->
          <rect x="232" y="48" width="6" height="40" fill="#e0e0e0"/>
          <rect x="312" y="47" width="6" height="41" fill="#e0e0e0"/>

          <!-- Front hood line -->
          <path d="M470 85 L530 100 Q555 110 565 130" stroke="#d0d0d0" stroke-width="1.5" fill="none"/>

          <!-- Headlights -->
          <ellipse cx="545" cy="125" rx="18" ry="10" fill="#fff9c4" stroke="#e0e0e0" stroke-width="1"/>
          <ellipse cx="545" cy="125" rx="12" ry="6" fill="#fff59d"/>

          <!-- Grille -->
          <rect x="555" y="135" width="12" height="25" rx="3" fill="#37474f"/>
          <line x1="558" y1="140" x2="565" y2="140" stroke="#78909c" stroke-width="1"/>
          <line x1="558" y1="145" x2="565" y2="145" stroke="#78909c" stroke-width="1"/>
          <line x1="558" y1="150" x2="565" y2="150" stroke="#78909c" stroke-width="1"/>

          <!-- Rear light -->
          <rect x="42" y="130" width="10" height="22" rx="3" fill="#c62828"/>
          <rect x="44" y="133" width="6" height="7" rx="1" fill="#ef5350"/>

          <!-- Door lines -->
          <line x1="230" y1="60" x2="230" y2="175" stroke="#e0e0e0" stroke-width="1"/>
          <line x1="340" y1="55" x2="340" y2="175" stroke="#e0e0e0" stroke-width="1"/>

          <!-- Door handles -->
          <rect x="200" y="118" width="20" height="5" rx="2.5" fill="#bdbdbd"/>
          <rect x="310" y="115" width="20" height="5" rx="2.5" fill="#bdbdbd"/>

          <!-- Side mirror -->
          <path d="M455 70 L475 65 L478 78 L458 80 Z" fill="#e0e0e0"/>

          <!-- Lower body trim -->
          <path d="M50 170 L555 168 L560 175 L50 178 Z" fill="#eceff1"/>

          <!-- Wheel arches -->
          <path d="M90 175 Q130 155 170 175" stroke="#e0e0e0" stroke-width="2" fill="none"/>
          <path d="M400 172 Q440 152 480 172" stroke="#e0e0e0" stroke-width="2" fill="none"/>

          <!-- Front wheel -->
          <circle cx="130" cy="185" r="38" fill="#263238"/>
          <circle cx="130" cy="185" r="30" fill="#455a64"/>
          <circle cx="130" cy="185" r="24" fill="#546e7a"/>
          <circle cx="130" cy="185" r="8" fill="#90a4ae"/>
          <!-- Wheel spokes -->
          <line x1="130" y1="161" x2="130" y2="209" stroke="#78909c" stroke-width="2"/>
          <line x1="106" y1="185" x2="154" y2="185" stroke="#78909c" stroke-width="2"/>
          <line x1="113" y1="168" x2="147" y2="202" stroke="#78909c" stroke-width="2"/>
          <line x1="147" y1="168" x2="113" y2="202" stroke="#78909c" stroke-width="2"/>

          <!-- Rear wheel -->
          <circle cx="440" cy="182" r="38" fill="#263238"/>
          <circle cx="440" cy="182" r="30" fill="#455a64"/>
          <circle cx="440" cy="182" r="24" fill="#546e7a"/>
          <circle cx="440" cy="182" r="8" fill="#90a4ae"/>
          <line x1="440" y1="158" x2="440" y2="206" stroke="#78909c" stroke-width="2"/>
          <line x1="416" y1="182" x2="464" y2="182" stroke="#78909c" stroke-width="2"/>
          <line x1="423" y1="165" x2="457" y2="199" stroke="#78909c" stroke-width="2"/>
          <line x1="457" y1="165" x2="423" y2="199" stroke="#78909c" stroke-width="2"/>

          <!-- Brand logo on front -->
          <ellipse cx="548" cy="115" rx="8" ry="5" fill="#c62828"/>
        </g>

        <!-- Mountain / scenery background -->
        <g opacity="0.3">
          <path d="M0 180 L80 100 L160 140 L240 80 L320 130 L400 70 L480 120 L560 90 L640 130 L700 110 L700 230 L0 230 Z" fill="#1565c0"/>
          <path d="M0 200 L100 140 L200 170 L300 120 L400 160 L500 110 L600 150 L700 130 L700 230 L0 230 Z" fill="#1976d2"/>
        </g>
      </svg>
    </div>

    <!-- Curved green swooshes -->
    <div class="swoosh-divider">
      <svg viewBox="0 0 240 900" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <!-- White curved shape -->
        <path d="M90 0 Q 180 225 115 450 Q 50 675 145 900 L 240 900 L 240 0 Z" fill="#ffffff"/>
        <!-- Green swoosh 1 -->
        <path d="M60 0 Q 155 225 90 450 Q 25 675 120 900 L 155 900 Q 60 675 125 450 Q 190 225 90 0 Z" fill="#8bc34a" opacity="0.9"/>
        <!-- Green swoosh 2 -->
        <path d="M28 0 Q 125 225 60 450 Q -5 675 90 900 L 108 900 Q 18 675 80 450 Q 145 225 60 0 Z" fill="#689f38" opacity="0.55"/>
      </svg>
    </div>
  </div>

  <!-- Right: Login card -->
  <div class="login-card-panel">

    <!-- City skyline silhouette -->
    <div class="city-skyline">
      <svg viewBox="0 0 600 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 160 L0 110 L18 110 L18 88 L30 88 L30 110 L48 110 L48 98 L65 98 L65 80 L78 80 L78 98 L95 98 L95 68 L112 68 L112 56 L125 56 L125 68 L142 68 L142 98 L165 98 L165 85 L182 85 L182 62 L195 62 L195 85 L212 85 L212 105 L238 105 L238 75 L255 75 L255 50 L272 50 L272 75 L288 75 L288 98 L312 98 L312 82 L330 82 L330 65 L345 65 L345 48 L358 48 L358 65 L375 65 L375 92 L400 92 L400 75 L418 75 L418 58 L435 58 L435 75 L452 75 L452 100 L478 100 L478 82 L495 82 L495 62 L512 62 L512 82 L528 82 L528 105 L555 105 L555 88 L572 88 L572 110 L600 110 L600 160 Z" fill="#dbe7f3"/>
        <!-- Bridge -->
        <path d="M300 160 L350 55 L400 160" stroke="#dbe7f3" stroke-width="2.5" fill="none"/>
        <path d="M318 160 L350 68 L382 160" stroke="#dbe7f3" stroke-width="1.5" fill="none"/>
        <path d="M305 115 L395 115" stroke="#dbe7f3" stroke-width="1.5" fill="none"/>
        <path d="M310 90 L390 90" stroke="#dbe7f3" stroke-width="1" fill="none"/>
        <path d="M315 70 L385 70" stroke="#dbe7f3" stroke-width="1" fill="none"/>
        <!-- Tower -->
        <rect x="540" y="38" width="14" height="122" fill="#dbe7f3" rx="2"/>
        <ellipse cx="547" cy="32" rx="12" ry="7" fill="#dbe7f3"/>
        <rect x="543" y="20" width="8" height="14" fill="#dbe7f3" rx="1"/>
      </svg>
    </div>

    <!-- Green accent bottom right -->
    <div class="green-accent">
      <svg viewBox="0 0 280 280" xmlns="http://www.w3.org/2000/svg">
        <path d="M80 280 Q 80 175 165 125 Q 250 75 280 0 L 280 280 Z" fill="#8bc34a" opacity="0.22"/>
        <path d="M130 280 Q 130 200 195 155 Q 260 110 280 60 L 280 280 Z" fill="#689f38" opacity="0.18"/>
      </svg>
    </div>

    <div class="login-card">
      <div class="logo-center">
        <img src="assets/img/logo-bandung-driver-tour.webp" alt="Bandung Driver Tour" class="logo-img-small">
      </div>

      <h2>Masuk ke Akun</h2>
      <p class="sub">Silakan masuk menggunakan email dan password untuk mengakses sistem Bandung Driver Tour.</p>

      <?php if ($error): ?>
        <div class="error-alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-wrapper">
            <span class="input-icon"><i class="bi bi-envelope"></i></span>
            <input type="email" id="email" name="email" placeholder="email@contoh.com" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <button type="button" id="togglePassword" class="password-toggle" aria-label="Tampilkan password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-masuk">
          Masuk <i class="bi bi-arrow-right"></i>
        </button>
      </form>

      <div class="footer-note">
        Belum punya akun? Silakan <a href="#">hubungi administrator</a>.
      </div>
    </div>
  </div>

  <footer class="app-footer">
    <div class="footer-container">
      <div class="footer-copyright">© 2026 Bandung Driver Tour. All Rights Reserved.</div>
      <div class="footer-credit">Website by <a href="http://websiterakyat.id/" target="_blank" rel="noopener noreferrer">Website Rakyat</a>.</div>
    </div>
  </footer>
</div>

<!-- Popup: Menu Favorit -->
<div class="app-launcher-backdrop" id="appLauncherBackdrop" aria-hidden="true"></div>
<div class="app-launcher-popup" id="appLauncherPopup" role="dialog" aria-modal="true" aria-label="Menu Favorit">
  <div class="app-launcher-header">
    <div class="app-launcher-brand-info">
      <h3>Ekosistem Bandung Driver Tour</h3>
    </div>
  </div>
  <div class="app-launcher-grid" id="appLauncherGrid">
    <!-- Diisi oleh JavaScript -->
  </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
  const passwordInput = document.getElementById('password');
  const icon = this.querySelector('i');
  const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordInput.setAttribute('type', type);
  icon.classList.toggle('bi-eye');
  icon.classList.toggle('bi-eye-slash');
});

/* ================= App Launcher Popup ================= */
(function () {
  const btn = document.getElementById('appLauncherBtn');
  const popup = document.getElementById('appLauncherPopup');
  const backdrop = document.getElementById('appLauncherBackdrop');
  const grid = document.getElementById('appLauncherGrid');

  // Daftar aplikasi yang dimiliki (modul yang tersedia di sistem)
  const apps = [
    { name: 'Booking', href: 'https://booking.bandungdrivertour.com/login', icon: 'bi-calendar-check', color: '#2563eb', category: 'Aplikasi' },
    { name: 'Keuangan', href: 'https://keu.bandungdrivertour.com/', icon: 'bi-wallet2', color: '#15803d', category: 'Aplikasi' },
    { name: 'BDT', href: 'http://bandungdrivertour.com/', icon: 'bi-globe', color: '#7c3aed', category: 'Website' },
    { name: 'BRC', href: 'https://www.bandungrentcar.com/', icon: 'bi-globe', color: '#f59e0b', category: 'Website' },
    { name: 'DB', href: 'http://driversbandung.com/', icon: 'bi-globe', color: '#06b6d4', category: 'Website' },
  ];

  function renderApps() {
    const categories = [...new Set(apps.map(a => a.category))];
    grid.innerHTML = categories.map(function (cat) {
      const catApps = apps.filter(function (a) { return a.category === cat; });
      return `
        <div class="app-category-section">
          <div class="app-category-label">${cat}</div>
          <div class="app-category-grid">
            ${catApps.map(function (app) {
              return `
                <a href="${app.href}" class="app-item" aria-label="${app.name}">
                  <span class="app-icon" style="background:${app.color}; color:#ffffff;">
                    <i class="bi ${app.icon}"></i>
                  </span>
                  <span class="app-label">${app.name}</span>
                </a>`;
            }).join('')}
          </div>
        </div>`;
    }).join('');
  }

  function openPopup() {
    popup.classList.add('open');
    backdrop.style.display = 'flex';
    backdrop.setAttribute('aria-hidden', 'false');
    btn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closePopup() {
    popup.classList.remove('open');
    backdrop.style.display = 'none';
    backdrop.setAttribute('aria-hidden', 'true');
    btn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  if (btn && popup && backdrop) {
    btn.addEventListener('click', openPopup);
    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) closePopup();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && popup.classList.contains('open')) closePopup();
    });
  }

  renderApps();
})();
</script>

</body>
</html>
