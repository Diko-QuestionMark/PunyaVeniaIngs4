<?php
require_once '../includes/config.php';
$db = getDB();
$cats = $db->query("SELECT c.*,(SELECT COUNT(*) FROM materials m WHERE m.category_id=c.id AND m.is_published=1) as mat_count FROM categories c WHERE c.section='listening' ORDER BY c.sort_order")->fetchAll();
$materials = $db->query("SELECT m.*,c.name as cat_name FROM materials m JOIN categories c ON m.category_id=c.id WHERE c.section='listening' AND m.is_published=1 ORDER BY c.sort_order,m.sort_order")->fetchAll();
$miniTests = $db->query("SELECT t.* FROM tests t WHERE t.test_type='mini' AND t.is_published=1 AND EXISTS(SELECT 1 FROM test_questions tq JOIN questions q ON tq.question_id=q.id WHERE tq.test_id=t.id AND q.section='listening') ORDER BY t.id LIMIT 3")->fetchAll();
$totalMiniTests = $db->query("SELECT COUNT(*) FROM tests t WHERE t.test_type='mini' AND t.is_published=1 AND EXISTS(SELECT 1 FROM test_questions tq JOIN questions q ON tq.question_id=q.id WHERE tq.test_id=t.id AND q.section='listening')")->fetchColumn();
$videos = $db->query("SELECT * FROM learning_videos WHERE section='listening' AND is_published=1 ORDER BY sort_order")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Listening Comprehension — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:70px;">
<!-- Hero -->
<div style="background:linear-gradient(135deg,#1E3A8A,#1D4ED8,#2563EB);padding:70px 5% 60px;position:relative;overflow:hidden;">
  <div style="position:absolute;top:-40px;right:-40px;width:300px;height:300px;background:rgba(255,255,255,0.03);border-radius:50%;"></div>
  <div style="position:absolute;bottom:-60px;left:10%;width:200px;height:200px;background:rgba(255,255,255,0.04);border-radius:50%;"></div>
  <div style="max-width:900px;margin:0 auto;position:relative;" data-aos="fade-up">
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;">
      <div style="width:64px;height:64px;background:rgba(255,255,255,0.15);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">🎧</div>
      <div>
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#93C5FD;margin-bottom:4px;">Section 1 · TOEFL ITP</div>
        <h1 style="color:white;font-size:1.8rem;">Listening Comprehension</h1>
      </div>
    </div>
    <p style="color:#BFDBFE;line-height:1.7;font-size:0.95rem;max-width:700px;margin-bottom:28px;">
      Pertajam pendengaran Anda dan tangkap setiap detail percakapan dengan sempurna. Di sini, kita akan melatih kepekaan telinga Anda menghadapi berbagai situasi berbahasa Inggris—mulai dari obrolan santai sehari-hari hingga simulasi perkuliahan akademik yang menantang.
    </p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;font-size:0.82rem;"><strong><?= count($materials) ?></strong> Materi</div>
      <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;font-size:0.82rem;"><strong><?= $totalMiniTests ?></strong> Mini Test</div>
    </div>
  </div>
</div>

<div style="max-width:1200px;margin:0 auto;padding:50px 5% 80px;">

  <!-- Materials -->
  <?php if(!empty($materials)): ?>
  <div style="margin-bottom:48px;" data-aos="fade-up">
    <h2 style="font-size:1.2rem;margin-bottom:20px;">📚 Materi Listening (<?= count($materials) ?>)</h2>
    <div class="materials-grid">
      <?php foreach($materials as $index => $m): ?>
      <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($m['slug']) ?>" class="material-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
        <div class="material-badge badge-listening">🎧 <?= sanitize($m['cat_name']) ?></div>
        <h4><?= sanitize($m['title']) ?></h4>
        <p><?= sanitize($m['summary'] ?: 'Pelajari materi Listening ini dengan penjelasan lengkap dan contoh.') ?></p>
        <div class="material-footer">
          <span style="color:#94A3B8;font-size:0.78rem;"><i class="fas fa-book-open"></i> Baca Materi</span>
          <span style="color:#2563EB;font-size:0.82rem;font-weight:600;">→</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Mini Tests -->
  <?php if(!empty($miniTests)): ?>
  <div data-aos="fade-up" style="margin-bottom:48px;">
    <h2 style="font-size:1.2rem;margin-bottom:20px;">⚡ Mini Test Listening</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
      <?php foreach($miniTests as $index => $t): ?>
      <div class="test-card" data-aos="zoom-in" data-aos-delay="<?= $index * 50 ?>">
        <div class="test-type-badge badge-mini">⚡ Mini Test</div>
        <h3><?= sanitize($t['title']) ?></h3>
        <p style="color:#64748B;font-size:0.85rem;flex:1;"><?= sanitize($t['description'] ?: '') ?></p>
        <div class="test-meta"><div class="test-meta-item">📝 <?= $t['total_questions'] ?> soal</div><div class="test-meta-item">⏱️ <?= $t['time_limit'] ?> mnt</div></div>
        <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $t['id'] ?>" class="btn btn-primary btn-sm" style="margin-top:auto;width:100%;justify-content:center;">Mulai Test</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Video Pembelajaran -->
  <?php if(!empty($videos)): ?>
  <div data-aos="fade-up" style="margin-bottom:48px;">
    <h2 style="font-size:1.2rem;margin-bottom:20px;">📺 Video Pembelajaran Listening</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
      <?php foreach($videos as $index => $v): ?>
      <div style="background:white;border-radius:16px;border:1px solid #E2E8F0;padding:16px;box-shadow:0 10px 25px rgba(0,0,0,0.02);" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;">
          <iframe style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" src="<?= sanitize(getYoutubeEmbedUrl($v['youtube_url'])) ?>" title="<?= sanitize($v['title']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div style="margin-top:16px;">
          <h3 style="font-size:1.05rem;color:#0F172A;margin-bottom:6px;line-height:1.4;"><?= sanitize($v['title']) ?></h3>
          <p style="color:#64748B;font-size:0.85rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= sanitize($v['description'] ?? 'Video pembelajaran untuk materi ini.') ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>



  <!-- Tips Box -->
  <div style="background:linear-gradient(135deg,#EFF6FF,#DBEAFE);border-radius:20px;padding:28px 32px;margin-top:40px;border:1px solid #BFDBFE;" data-aos="fade-up">
    <h3 style="font-size:1rem;color:#1D4ED8;margin-bottom:12px;">💡 Tips Sukses Listening TOEFL</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <?php $tips=['Dengarkan dengan penuh perhatian — audio hanya diputar sekali!','Baca pilihan jawaban sebelum audio mulai','Fokus pada intonasi dan ekspresi pembicara','Jangan terpaku pada kata-kata sulit, tangkap makna umumnya','Latih telinga dengan podcast dan film berbahasa Inggris setiap hari','Catat kata kunci penting saat mendengarkan'];
      foreach($tips as $tip): ?>
      <div style="display:flex;gap:8px;align-items:flex-start;">
        <span style="color:#2563EB;font-size:0.9rem;flex-shrink:0;">✓</span>
        <span style="font-size:0.85rem;color:#1E40AF;"><?= $tip ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>