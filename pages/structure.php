<?php
require_once '../includes/config.php';
$db = getDB();
$cats = $db->query("SELECT c.*,(SELECT COUNT(*) FROM materials m WHERE m.category_id=c.id AND m.is_published=1) as mat_count FROM categories c WHERE c.section='structure' ORDER BY c.sort_order")->fetchAll();
$materials = $db->query("SELECT m.*,c.name as cat_name FROM materials m JOIN categories c ON m.category_id=c.id WHERE c.section='structure' AND m.is_published=1 ORDER BY c.sort_order,m.sort_order")->fetchAll();
$miniTests = $db->query("SELECT t.* FROM tests t WHERE t.test_type='mini' AND t.is_published=1 AND EXISTS(SELECT 1 FROM test_questions tq JOIN questions q ON tq.question_id=q.id WHERE tq.test_id=t.id AND q.section='structure') ORDER BY t.id LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Structure & Written Expression — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:70px;">
<div style="background:linear-gradient(135deg,#4C1D95,#6D28D9,#7C3AED);padding:70px 5% 60px;position:relative;overflow:hidden;">
  <div style="max-width:900px;margin:0 auto;position:relative;">
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;">
      <div style="width:64px;height:64px;background:rgba(255,255,255,0.15);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">📝</div>
      <div>
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#C4B5FD;margin-bottom:4px;">Section 2 · TOEFL ITP</div>
        <h1 style="color:white;font-size:1.8rem;">Structure & Written Expression</h1>
      </div>
    </div>
    <p style="color:#DDD6FE;line-height:1.7;font-size:0.95rem;max-width:700px;margin-bottom:28px;">
      Bagian kedua TOEFL ITP mengukur penguasaan tata bahasa Inggris tulis. Terdiri dari <strong>40 soal</strong> (15 Sentence Completion + 25 Error Identification) dengan durasi <strong>25 menit</strong>.
    </p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;font-size:0.82rem;"><strong>40</strong> Soal</div>
      <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;font-size:0.82rem;"><strong>25</strong> Menit</div>
      <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:white;padding:8px 16px;border-radius:8px;font-size:0.82rem;"><strong><?= count($materials) ?></strong> Materi</div>
    </div>
  </div>
</div>

<div style="max-width:1200px;margin:0 auto;padding:50px 5% 80px;">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:48px;">
    <?php $parts=[
      ['Part 1','Sentence Completion','Lengkapi kalimat dengan struktur gramatikal yang tepat','15 soal','🔤','#EDE9FE','#6D28D9'],
      ['Part 2','Error Identification','Temukan dan identifikasi kesalahan gramatikal dalam kalimat','25 soal','🔍','#FEF3C7','#92400E'],
    ];
    foreach($parts as $p): ?>
    <div style="background:<?= $p[5] ?>;border-radius:16px;padding:24px;border:1px solid <?= $p[5] ?>;">
      <div style="font-size:1.5rem;margin-bottom:8px;"><?= $p[4] ?></div>
      <div style="font-size:0.72rem;font-weight:700;color:<?= $p[6] ?>;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;"><?= $p[0] ?></div>
      <div style="font-size:1.05rem;font-weight:700;color:#0F172A;margin-bottom:4px;"><?= $p[1] ?></div>
      <div style="font-size:0.85rem;color:#64748B;margin-bottom:10px;"><?= $p[2] ?></div>
      <div style="font-weight:700;color:<?= $p[6] ?>;font-size:0.82rem;"><?= $p[3] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if(!empty($materials)): ?>
  <div style="margin-bottom:48px;">
    <h2 style="font-size:1.2rem;margin-bottom:20px;">📚 Materi Structure (<?= count($materials) ?>)</h2>
    <div class="materials-grid">
      <?php foreach($materials as $m): ?>
      <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($m['slug']) ?>" class="material-card">
        <div class="material-badge badge-structure">📝 <?= sanitize($m['cat_name']) ?></div>
        <h4><?= sanitize($m['title']) ?></h4>
        <p><?= sanitize($m['summary'] ?: 'Materi grammar tata bahasa Inggris untuk TOEFL Structure.') ?></p>
        <div class="material-footer">
          <span style="color:#94A3B8;font-size:0.78rem;"><i class="fas fa-book-open"></i> Baca Materi</span>
          <span style="color:#7C3AED;font-size:0.82rem;font-weight:600;">→</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if(!empty($miniTests)): ?>
  <div style="margin-bottom:40px;">
    <h2 style="font-size:1.2rem;margin-bottom:20px;">⚡ Mini Test Structure</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
      <?php foreach($miniTests as $t): ?>
      <div class="test-card">
        <div class="test-type-badge badge-mini">⚡ Mini Test</div>
        <h3><?= sanitize($t['title']) ?></h3>
        <p style="color:#64748B;font-size:0.85rem;flex:1;"><?= sanitize($t['description'] ?: '') ?></p>
        <div class="test-meta"><div class="test-meta-item">📝 <?= $t['total_questions'] ?> soal</div><div class="test-meta-item">⏱️ <?= $t['time_limit'] ?> mnt</div></div>
        <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $t['id'] ?>" class="btn btn-sm" style="background:#EDE9FE;color:#6D28D9;border:none;border-radius:100px;padding:8px 18px;font-weight:600;font-size:0.85rem;text-align:center;">Mulai Test</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE);border-radius:20px;padding:28px 32px;border:1px solid #DDD6FE;">
    <h3 style="font-size:1rem;color:#6D28D9;margin-bottom:12px;">💡 Tips Sukses Structure TOEFL</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <?php $tips=['Kuasai pola Subject-Verb Agreement terlebih dahulu','Pelajari penggunaan Relative Clause (who, which, that)','Perhatikan Parallel Structure dalam kalimat','Pahami perbedaan Active vs Passive Voice','Kenali error tipe umum: Articles, Prepositions, Conjunctions','Latihan soal Error ID dengan membaca kalimat teliti kata per kata'];
      foreach($tips as $tip): ?>
      <div style="display:flex;gap:8px;align-items:flex-start;">
        <span style="color:#7C3AED;font-size:0.9rem;flex-shrink:0;">✓</span>
        <span style="font-size:0.85rem;color:#4C1D95;"><?= $tip ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>