<?php
// Set flag to determine if we are on the home page (for transparent navbar)
$is_home = isset($is_home) ? $is_home : false;
?>
<nav class="navbar scrolled" id="navbar">
  <a href="<?= SITE_URL ?>/index.php" class="navbar-brand">
    <div class="brand-icon">T</div>
    TOEFLMaster
  </a>

  <ul class="navbar-nav">
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <li><a href="<?= SITE_URL ?>/index.php" class="nav-link <?= $is_home ? 'active' : '' ?>">Beranda</a></li>
    <li><a href="<?= SITE_URL ?>/pages/listening.php" class="nav-link <?= $current_page === 'listening.php' ? 'active' : '' ?>">Listening</a></li>
    <li><a href="<?= SITE_URL ?>/pages/structure.php" class="nav-link <?= $current_page === 'structure.php' ? 'active' : '' ?>">Structure & Written Expression</a></li>
    <li><a href="<?= SITE_URL ?>/pages/reading.php" class="nav-link <?= $current_page === 'reading.php' ? 'active' : '' ?>">Reading</a></li>
    <li><a href="<?= SITE_URL ?>/pages/tests.php" class="nav-link <?= $current_page === 'tests.php' ? 'active' : '' ?>">Latihan</a></li>
    <li><a href="<?= SITE_URL ?>/pages/ai_tutor.php" class="nav-link <?= $current_page === 'ai_tutor.php' ? 'active' : '' ?>">AI Tutor</a></li>
  </ul>

  <div class="navbar-actions">
    <?php if (isAdminLoggedIn()): ?>
      <a href="<?= SITE_URL ?>/admin/index.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-user-shield"></i> Admin Panel
      </a>
    <?php elseif (isUserLoggedIn()): ?>
      <a href="<?= SITE_URL ?>/pages/dashboard.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-user"></i> <?= isset($_SESSION['username']) ? sanitize($_SESSION['username']) : 'Dashboard' ?>
      </a>
    <?php else: ?>
      <a href="<?= SITE_URL ?>/pages/login.php" class="btn btn-secondary btn-sm">Masuk</a>
      <a href="<?= SITE_URL ?>/pages/register.php" class="btn btn-primary btn-sm">Daftar Gratis</a>
    <?php endif; ?>
  </div>
</nav>
