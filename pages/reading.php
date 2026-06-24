<?php
require_once '../includes/config.php';
$db = getDB();
$cats = $db->query("SELECT c.*,(SELECT COUNT(*) FROM materials m WHERE m.category_id=c.id AND m.is_published=1) as mat_count FROM categories c WHERE c.section='reading' ORDER BY c.sort_order")->fetchAll();
$materials = $db->query("SELECT m.*,c.name as cat_name FROM materials m JOIN categories c ON m.category_id=c.id WHERE c.section='reading' AND m.is_published=1 ORDER BY c.sort_order,m.sort_order")->fetchAll();
$miniTests = $db->query("SELECT t.* FROM tests t WHERE t.test_type='mini' AND t.is_published=1 AND EXISTS(SELECT 1 FROM test_questions tq JOIN questions q ON tq.question_id=q.id WHERE tq.test_id=t.id AND q.section='reading') ORDER BY t.id LIMIT 3")->fetchAll();
$totalMiniTests = $db->query("SELECT COUNT(*) FROM tests t WHERE t.test_type='mini' AND t.is_published=1 AND EXISTS(SELECT 1 FROM test_questions tq JOIN questions q ON tq.question_id=q.id WHERE tq.test_id=t.id AND q.section='reading')")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reading Comprehension — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:70px;">
<div style="background:linear-gradient(135deg,#064E3B,#065F46,#047857);padding:70px 5% 60px;position:relative;overflow:hidden;">
  <div style="position:absolute;top:-40px;right:-40px;width:300px;height:300px;background:rgba(255,255,255,0.03);border-radius:50%;"></div>
  <div style="position:absolute;bottom:-60px;left:10%;width:200px;height:200px;background:rgba(255,255,255,0.04);border-radius:50%;"></div>
  <div style="max-width:900px;margin:0 auto;position:relative;" data-aos="fade-up">
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;">
      <div style="width:64px;height:64px;background:rgba(255,255,255,0.15);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">📖</div>
      <div>
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#6EE7B7;margin-bottom:4px;">Section 3 · TOEFL ITP</div>
        <h1 style="color:white;font-size:1.8rem;">Reading Comprehension</h1>
      </div>
    </div>
    <p style="color:#A7F3D0;line-height:1.7;font-size:0.95rem;max-width:700px;margin-bottom:28px;">
      Menyelami teks panjang berbahasa Inggris kini tak lagi membosankan. Latih insting membaca Anda untuk menangkap ide pokok dengan cepat, menebak makna dari konteks, dan menemukan informasi krusial tanpa harus membaca kata per kata.
    </p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;font-size:0.82rem;"><strong><?= count($materials) ?></strong> Materi</div>
      <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;font-size:0.82rem;"><strong><?= $totalMiniTests ?></strong> Mini Test</div>
    </div>
  </div>
</div>

<div style="max-width:1200px;margin:0 auto;padding:50px 5% 80px;">

  <?php if(!empty($materials)): ?>
  <div style="margin-bottom:48px;" data-aos="fade-up">
    <h2 style="font-size:1.2rem;margin-bottom:20px;">📚 Materi Reading (<?= count($materials) ?>)</h2>
    <div class="materials-grid">
      <?php foreach($materials as $index => $m): ?>
      <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($m['slug']) ?>" class="material-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
        <div class="material-badge badge-reading">📖 <?= sanitize($m['cat_name']) ?></div>
        <h4><?= sanitize($m['title']) ?></h4>
        <p><?= sanitize($m['summary'] ?: 'Materi strategi membaca efektif untuk TOEFL Reading Comprehension.') ?></p>
        <div class="material-footer">
          <span style="color:#94A3B8;font-size:0.78rem;"><i class="fas fa-book-open"></i> Baca Materi</span>
          <span style="color:#10B981;font-size:0.82rem;font-weight:600;">→</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if(!empty($miniTests)): ?>
  <div style="margin-bottom:40px;" data-aos="fade-up">
    <h2 style="font-size:1.2rem;margin-bottom:20px;">⚡ Mini Test Reading</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
      <?php foreach($miniTests as $index => $t): ?>
      <div class="test-card" data-aos="zoom-in" data-aos-delay="<?= $index * 50 ?>">
        <div class="test-type-badge badge-mini">⚡ Mini Test</div>
        <h3><?= sanitize($t['title']) ?></h3>
        <p style="color:#64748B;font-size:0.85rem;flex:1;"><?= sanitize($t['description'] ?: '') ?></p>
        <div class="test-meta"><div class="test-meta-item">📝 <?= $t['total_questions'] ?> soal</div><div class="test-meta-item">⏱️ <?= $t['time_limit'] ?> mnt</div></div>
        <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $t['id'] ?>" class="btn btn-sm" style="background:#D1FAE5;color:#065F46;border:none;border-radius:100px;padding:8px 18px;font-weight:600;font-size:0.85rem;text-align:center;">Mulai Test</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div style="background:linear-gradient(135deg,#ECFDF5,#D1FAE5);border-radius:20px;padding:28px 32px;border:1px solid #A7F3D0;" data-aos="fade-up">
    <h3 style="font-size:1rem;color:#065F46;margin-bottom:12px;">💡 Tips Sukses Reading TOEFL</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <?php $tips=['Baca topic sentence setiap paragraf terlebih dahulu (skimming)','Gunakan teknik scanning untuk mencari detail spesifik','Jangan terjebak pertanyaan vocabulary — gunakan konteks!','Perhatikan kata signal: however, therefore, in contrast, etc.','Kelola waktu: rata-rata 11 menit per passage','Jawab soal dari yang paling mudah, skip yang sulit'];
      foreach($tips as $tip): ?>
      <div style="display:flex;gap:8px;align-items:flex-start;">
        <span style="color:#10B981;font-size:0.9rem;flex-shrink:0;">✓</span>
        <span style="font-size:0.85rem;color:#065F46;"><?= $tip ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>