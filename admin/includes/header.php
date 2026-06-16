<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Admin Panel' ?> — TOEFLMaster Admin</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-sidebar-logo">
    <img src="<?= SITE_URL ?>/assets/icon.png" alt="Admin" class="admin-logo-icon" style="background:none; object-fit:contain; padding:2px;">
    <div>
      <div class="admin-logo-text">TOEFLMaster</div>
      <div class="admin-logo-sub">Admin Panel</div>
    </div>
  </div>

  <nav class="admin-nav">
    <div class="admin-nav-section">Dashboard</div>
    <a href="<?= SITE_URL ?>/admin/index.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' && !isset($_GET['page']) ? 'active' : '' ?>">
      <span class="admin-nav-icon">🏠</span> Dashboard
    </a>

    <div class="admin-nav-section">Konten</div>
    <a href="<?= SITE_URL ?>/admin/pages/materials.php" class="admin-nav-link <?= strpos($_SERVER['REQUEST_URI'],'materials') !== false ? 'active' : '' ?>">
      <span class="admin-nav-icon">📚</span> Materi
    </a>
    <a href="<?= SITE_URL ?>/admin/pages/questions.php" class="admin-nav-link <?= strpos($_SERVER['REQUEST_URI'],'questions') !== false ? 'active' : '' ?>">
      <span class="admin-nav-icon">❓</span> Bank Soal
    </a>
    <a href="<?= SITE_URL ?>/admin/pages/practice.php" class="admin-nav-link <?= strpos($_SERVER['REQUEST_URI'],'practice') !== false ? 'active' : '' ?>">
      <span class="admin-nav-icon">✏️</span> Latihan Soal
    </a>
    <a href="<?= SITE_URL ?>/admin/pages/tests.php" class="admin-nav-link <?= strpos($_SERVER['REQUEST_URI'],'admin/pages/tests') !== false ? 'active' : '' ?>">
      <span class="admin-nav-icon">🏆</span> Test (Mini & Full)
    </a>
    <a href="<?= SITE_URL ?>/admin/pages/categories.php" class="admin-nav-link <?= strpos($_SERVER['REQUEST_URI'],'categories') !== false ? 'active' : '' ?>">
      <span class="admin-nav-icon">🏷️</span> Kategori
    </a>

    <div class="admin-nav-section">Data</div>
    <a href="<?= SITE_URL ?>/admin/pages/users.php" class="admin-nav-link <?= strpos($_SERVER['REQUEST_URI'],'users') !== false ? 'active' : '' ?>">
      <span class="admin-nav-icon">👥</span> Pengguna
    </a>
    <a href="<?= SITE_URL ?>/admin/pages/results.php" class="admin-nav-link <?= strpos($_SERVER['REQUEST_URI'],'results') !== false ? 'active' : '' ?>">
      <span class="admin-nav-icon">📊</span> Hasil Test
    </a>

    <div class="admin-nav-section">Akun</div>
    <a href="<?= SITE_URL ?>/index.php" target="_blank" class="admin-nav-link">
      <span class="admin-nav-icon">🌐</span> Lihat Website
    </a>
    <a href="<?= SITE_URL ?>/admin/logout.php" class="admin-nav-link" style="color:#EF4444;">
      <span class="admin-nav-icon">🚪</span> Keluar
    </a>
  </nav>
</aside>

<!-- MAIN -->
<div class="admin-main">
  <!-- TOP BAR -->
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button onclick="document.getElementById('adminSidebar').classList.toggle('open')" style="display:none;background:none;border:none;cursor:pointer;font-size:1.2rem;color:#64748B;" id="menuToggle">
        <i class="fas fa-bars"></i>
      </button>
      <div class="admin-topbar-title"><?= $pageTitle ?? 'Dashboard' ?></div>
    </div>
    <div class="admin-topbar-actions">
      <a href="<?= SITE_URL ?>/admin/pages/questions.php?action=add" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Tambah Soal
      </a>
      <div class="admin-user-badge">
        <div class="admin-avatar"><?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?></div>
        <span class="admin-user-name"><?= sanitize($admin['full_name'] ?? $admin['username'] ?? 'Admin') ?></span>
      </div>
    </div>
  </header>

  <!-- FLASH MESSAGE -->
  <?php $flash = getFlashMessage(); if ($flash): ?>
  <div style="padding:0 32px;margin-top:16px;">
    <div class="alert alert-<?= $flash['type'] ?>">
      <?= $flash['type'] === 'success' ? '✅' : '❌' ?>
      <?= sanitize($flash['message']) ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- CONTENT -->
  <div class="admin-content">