<?php
require_once '../includes/config.php';
requireUserLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

$totalTests    = $db->prepare("SELECT COUNT(*) FROM user_test_results WHERE user_id=?"); $totalTests->execute([$uid]); $totalTests = $totalTests->fetchColumn();
$avgScore      = $db->prepare("SELECT AVG(toefl_score) FROM user_test_results WHERE user_id=?"); $avgScore->execute([$uid]); $avgScore = round($avgScore->fetchColumn() ?? 0);
$bestScore     = $db->prepare("SELECT MAX(toefl_score) FROM user_test_results WHERE user_id=?"); $bestScore->execute([$uid]); $bestScore = $bestScore->fetchColumn() ?? 0;
$totalPractice = $db->prepare("SELECT COUNT(*) FROM user_practice_results WHERE user_id=?"); $totalPractice->execute([$uid]); $totalPractice = $totalPractice->fetchColumn();

$recentTests = $db->prepare("
    SELECT r.*,t.title,t.test_type FROM user_test_results r
    JOIN tests t ON r.test_id=t.id
    WHERE r.user_id=? ORDER BY r.completed_at DESC LIMIT 5
");
$recentTests->execute([$uid]); $recentTests = $recentTests->fetchAll();

$availableTests = $db->query("SELECT * FROM tests WHERE is_published=1 ORDER BY test_type,id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar scrolled">
  <a href="<?= SITE_URL ?>/index.php" class="navbar-brand">
    <div class="brand-icon">T</div>TOEFLMaster
  </a>
  <ul class="navbar-nav">
    <li><a href="<?= SITE_URL ?>/index.php" class="nav-link">Beranda</a></li>
    <li><a href="<?= SITE_URL ?>/pages/listening.php" class="nav-link">Listening</a></li>
    <li><a href="<?= SITE_URL ?>/pages/structure.php" class="nav-link">Structure</a></li>
    <li><a href="<?= SITE_URL ?>/pages/reading.php" class="nav-link">Reading</a></li>
    <li><a href="<?= SITE_URL ?>/pages/tests.php" class="nav-link">Latihan Soal</a></li>
  </ul>
  <div class="navbar-actions">
    <span style="font-size:0.85rem;color:#64748B;">Halo, <strong><?= sanitize($user['full_name'] ?: $user['username']) ?></strong>!</span>
    <a href="<?= SITE_URL ?>/pages/logout.php" class="btn btn-secondary btn-sm">Keluar</a>
  </div>
</nav>

<div style="padding-top:90px;max-width:1200px;margin:0 auto;padding-left:5%;padding-right:5%;padding-bottom:80px;">

  <?php $flash=getFlashMessage(); if($flash): ?>
  <div class="alert alert-<?= $flash['type'] ?>" style="margin-top:20px;"><?= sanitize($flash['message']) ?></div>
  <?php endif; ?>

  <!-- Welcome Banner -->
  <div style="background:linear-gradient(135deg,#1E293B,#0F172A);border-radius:20px;padding:32px 36px;margin:30px 0 28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;">
    <div>
      <p style="color:#64748B;font-size:0.85rem;margin-bottom:6px;">Selamat datang kembali 👋</p>
      <h1 style="color:white;font-size:1.6rem;margin-bottom:8px;"><?= sanitize($user['full_name'] ?: $user['username']) ?></h1>
      <p style="color:#94A3B8;font-size:0.9rem;">Terus semangat belajar! Skor terbaikmu saat ini: <strong style="color:#60A5FA;font-size:1.1rem;"><?= $bestScore ?: '-' ?></strong><?= $bestScore ? '/677' : '' ?></p>
    </div>
    <div style="display:flex;gap:12px;">
      <a href="<?= SITE_URL ?>/pages/tests.php?type=full" class="btn btn-primary">🏆 Ambil Full Test</a>
      <a href="<?= SITE_URL ?>/pages/materials.php" class="btn btn-secondary" style="background:rgba(255,255,255,0.08);color:white;border-color:rgba(255,255,255,0.15);">📚 Belajar Materi</a>
    </div>
  </div>

  <!-- Stats -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
    <?php $stats=[
      ['label'=>'Total Test Diambil','value'=>$totalTests,'icon'=>'🏆','color'=>'#DBEAFE'],
      ['label'=>'Rata-rata Skor','value'=>$avgScore?:'-','icon'=>'📊','color'=>'#EDE9FE'],
      ['label'=>'Skor Terbaik','value'=>$bestScore?:'-','icon'=>'⭐','color'=>'#D1FAE5'],
      ['label'=>'Latihan Selesai','value'=>$totalPractice,'icon'=>'✅','color'=>'#FEF3C7'],
    ];
    foreach($stats as $s): ?>
    <div style="background:white;border:1px solid #E2E8F0;border-radius:16px;padding:22px;display:flex;align-items:center;gap:14px;">
      <div style="width:46px;height:46px;border-radius:12px;background:<?= $s['color'] ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;"><?= $s['icon'] ?></div>
      <div>
        <div style="font-size:0.75rem;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;"><?= $s['label'] ?></div>
        <div style="font-size:1.6rem;font-weight:800;color:#0F172A;"><?= $s['value'] ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1.2fr;gap:24px;">
    <!-- Available Tests -->
    <div class="card">
      <div class="card-header">
        <strong>📋 Test Tersedia</strong>
        <a href="<?= SITE_URL ?>/pages/tests.php" style="font-size:0.82rem;color:#2563EB;">Lihat Semua</a>
      </div>
      <div style="padding:16px;">
        <?php if(empty($availableTests)): ?>
        <p style="color:#94A3B8;text-align:center;padding:20px;">Belum ada test tersedia.</p>
        <?php else: foreach(array_slice($availableTests,0,6) as $t): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #F1F5F9;">
          <div style="width:36px;height:36px;border-radius:10px;background:<?= $t['test_type']==='mini'?'#FEF3C7':'#EDE9FE' ?>;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
            <?= $t['test_type']==='mini'?'⚡':'🏆' ?>
          </div>
          <div style="flex:1;">
            <div style="font-size:0.875rem;font-weight:600;color:#0F172A;"><?= sanitize($t['title']) ?></div>
            <div style="font-size:0.75rem;color:#94A3B8;"><?= $t['total_questions'] ?> soal · <?= $t['time_limit'] ?> menit</div>
          </div>
          <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">Mulai</a>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Recent Results -->
    <div class="card">
      <div class="card-header">
        <strong>📊 Riwayat Test</strong>
      </div>
      <div style="padding:16px;">
        <?php if(empty($recentTests)): ?>
        <div style="text-align:center;padding:40px 20px;">
          <div style="font-size:2.5rem;margin-bottom:10px;">🎯</div>
          <p style="color:#64748B;font-weight:500;">Kamu belum mengambil test apapun.</p>
          <p style="color:#94A3B8;font-size:0.85rem;">Mulai dengan mini test untuk mengukur kemampuanmu!</p>
          <a href="<?= SITE_URL ?>/pages/tests.php" class="btn btn-primary btn-sm" style="margin-top:12px;">Lihat Test</a>
        </div>
        <?php else: foreach($recentTests as $r): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #F1F5F9;">
          <div style="width:44px;height:44px;border-radius:12px;background:<?= $r['toefl_score']>=500?'#D1FAE5':($r['toefl_score']>=450?'#FEF3C7':'#FEE2E2') ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;color:<?= $r['toefl_score']>=500?'#065F46':($r['toefl_score']>=450?'#92400E':'#991B1B') ?>;flex-shrink:0;">
            <?= $r['toefl_score'] ?>
          </div>
          <div style="flex:1;">
            <div style="font-size:0.875rem;font-weight:600;color:#0F172A;"><?= sanitize($r['title']) ?></div>
            <div style="font-size:0.75rem;color:#94A3B8;"><?= $r['total_correct'] ?>/<?= $r['total_questions'] ?> benar · <?= timeAgo($r['completed_at']) ?></div>
          </div>
          <a href="<?= SITE_URL ?>/pages/result.php?id=<?= $r['id'] ?>" class="btn btn-secondary btn-sm">Detail</a>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Quick Links -->
  <div style="margin-top:24px;">
    <h3 style="font-size:1rem;font-weight:700;color:#0F172A;margin-bottom:16px;">⚡ Akses Cepat</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <?php 
      $quickLinks = [
        ['href'=>'listening.php','icon'=>'🎧','label'=>'Materi Listening'],
        ['href'=>'structure.php','icon'=>'📝','label'=>'Materi Structure'],
        ['href'=>'reading.php',  'icon'=>'📖','label'=>'Materi Reading'],
        ['href'=>'tests.php?type=mini','icon'=>'⚡','label'=>'Mini Test'],
        ['href'=>'tests.php?type=full','icon'=>'🏆','label'=>'Full Test'],
        ['href'=>'materials.php','icon'=>'📚','label'=>'Semua Materi'],
      ];
      foreach($quickLinks as $l): ?>
      <a href="<?= SITE_URL ?>/pages/<?= $l['href'] ?>" style="display:flex;align-items:center;gap:8px;padding:12px 18px;background:white;border:1px solid #E2E8F0;border-radius:100px;font-size:0.875rem;font-weight:500;color:#334155;transition:all 0.2s;" onmouseover="this.style.borderColor='#2563EB';this.style.color='#2563EB';" onmouseout="this.style.borderColor='#E2E8F0';this.style.color='#334155';">
        <?= $l['icon'] ?> <?= $l['label'] ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</body>
</html>