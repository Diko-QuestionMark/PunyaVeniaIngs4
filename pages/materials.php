<?php
require_once '../includes/config.php';
$db = getDB();

$section = $_GET['section'] ?? '';
$search  = trim($_GET['q'] ?? '');
$where   = ["m.is_published=1"];
$params  = [];

if ($section) { $where[] = "c.section=?"; $params[] = $section; }
if ($search)  { $where[] = "(m.title LIKE ? OR m.summary LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereSQL = 'WHERE '.implode(' AND ', $where);
$stmt = $db->prepare("SELECT m.*,c.name as cat_name,c.section FROM materials m JOIN categories c ON m.category_id=c.id $whereSQL ORDER BY c.sort_order,m.sort_order");
$stmt->execute($params);
$materials = $stmt->fetchAll();

$totalCounts = $db->query("SELECT c.section, COUNT(m.id) as cnt FROM materials m JOIN categories c ON m.category_id=c.id WHERE m.is_published=1 GROUP BY c.section")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Semua Materi TOEFL — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:70px;">
  <!-- Page Header -->
  <div style="background:linear-gradient(135deg,#0F172A,#1E293B);padding:60px 5% 50px;text-align:center;" data-aos="fade-down">
    <div style="display:inline-block;background:rgba(37,99,235,0.15);border:1px solid rgba(37,99,235,0.3);color:#60A5FA;padding:6px 16px;border-radius:100px;font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px;">Semua Materi</div>
    <h1 style="color:white;font-size:2rem;margin-bottom:10px;">12 Materi Lengkap TOEFL</h1>
    <p style="color:#94A3B8;max-width:500px;margin:0 auto 28px;font-size:0.95rem;">Pelajari semua materi TOEFL dari Listening, Structure, hingga Reading secara terstruktur.</p>

    <!-- Search -->
    <form method="GET" style="max-width:480px;margin:0 auto;">
      <div style="display:flex;gap:8px;">
        <input type="text" name="q" class="form-control" placeholder="Cari materi..." value="<?= sanitize($search) ?>"
               style="flex:1;background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.15);color:white;padding:12px 18px;font-size:0.9rem;">
        <?php if($section): ?><input type="hidden" name="section" value="<?= sanitize($section) ?>"><?php endif; ?>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
      </div>
    </form>
  </div>

  <div style="max-width:1200px;margin:0 auto;padding:40px 5% 80px;">
    <!-- Filter Tabs -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:32px;" data-aos="fade-up">
      <?php
      $filters = [
        [''           ,'Semua Materi', '📚', array_sum($totalCounts ?: [])],
        ['listening'  ,'Listening',    '🎧', $totalCounts['listening'] ?? 0],
        ['structure'  ,'Structure',    '📝', $totalCounts['structure'] ?? 0],
        ['reading'    ,'Reading',      '📖', $totalCounts['reading']   ?? 0],
      ];
      foreach($filters as [$val,$label,$icon,$cnt]):
        $isActive = $section === $val;
        $href = '?section='.$val.($search?"&q=".urlencode($search):'');
      ?>
      <a href="<?= $href ?>" style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:100px;font-size:0.875rem;font-weight:600;transition:all 0.2s;
        <?= $isActive
          ? 'background:#2563EB;color:white;box-shadow:0 4px 14px rgba(37,99,235,0.3);'
          : 'background:white;color:#64748B;border:1.5px solid #E2E8F0;' ?>">
        <?= $icon ?> <?= $label ?>
        <span style="<?= $isActive?'background:rgba(255,255,255,0.25);':'background:#F1F5F9;' ?>padding:1px 8px;border-radius:100px;font-size:0.72rem;"><?= $cnt ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Results Info -->
    <?php if($search || $section): ?>
    <div style="margin-bottom:20px;color:#64748B;font-size:0.875rem;">
      Menampilkan <strong><?= count($materials) ?></strong> materi
      <?php if($search): ?> untuk "<strong><?= sanitize($search) ?></strong>"<?php endif; ?>
      <?php if($section): ?> di bagian <strong><?= ucfirst($section) ?></strong><?php endif; ?>
      · <a href="<?= SITE_URL ?>/pages/materials.php" style="color:#2563EB;">Reset filter</a>
    </div>
    <?php endif; ?>

    <!-- Materials Grid -->
    <?php if(empty($materials)): ?>
    <div style="text-align:center;padding:80px 20px;color:#94A3B8;">
      <div style="font-size:3rem;margin-bottom:16px;">🔍</div>
      <h3 style="color:#64748B;margin-bottom:8px;">Materi tidak ditemukan</h3>
      <p style="font-size:0.875rem;">Coba kata kunci lain atau <a href="<?= SITE_URL ?>/pages/materials.php" style="color:#2563EB;">lihat semua materi</a>.</p>
    </div>
    <?php else: ?>

    <?php
    // Group by section if showing all
    if (!$section) {
      $grouped = [];
      foreach($materials as $m) $grouped[$m['section']][] = $m;
      $secInfo = [
        'listening' => ['🎧','Listening Comprehension','#2563EB'],
        'structure' => ['📝','Structure & Written Expression','#7C3AED'],
        'reading'   => ['📖','Reading Comprehension','#10B981'],
      ];
      foreach($secInfo as $sec => [$icon,$title,$color]):
        if (empty($grouped[$sec])) continue;
    ?>
    <div style="margin-bottom:48px;" data-aos="fade-up">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:14px;border-bottom:2px solid #F1F5F9;" data-aos="fade-right">
        <span style="font-size:1.3rem;"><?= $icon ?></span>
        <h2 style="font-size:1.1rem;color:#0F172A;"><?= $title ?></h2>
        <span style="background:#F1F5F9;color:#64748B;padding:2px 10px;border-radius:100px;font-size:0.75rem;font-weight:600;"><?= count($grouped[$sec]) ?> materi</span>
        <a href="<?= SITE_URL ?>/pages/<?= $sec ?>.php" style="margin-left:auto;font-size:0.82rem;color:#2563EB;font-weight:600;">Lihat Semua →</a>
      </div>
      <div class="materials-grid">
        <?php foreach($grouped[$sec] as $index => $m): ?>
        <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($m['slug']) ?>" class="material-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
          <div class="material-badge badge-<?= $m['section'] ?>"><?= $icon ?> <?= sanitize($m['cat_name']) ?></div>
          <h4><?= sanitize($m['title']) ?></h4>
          <p><?= sanitize($m['summary'] ?: 'Klik untuk membaca materi lengkap.') ?></p>
          <div class="material-footer">
            <span style="color:#94A3B8;font-size:0.78rem;">📖 Baca Materi</span>
            <span style="color:<?= $color ?>;font-size:0.82rem;font-weight:600;">→</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach;
    } else { ?>
    <div class="materials-grid">
      <?php foreach($materials as $index => $m): ?>
      <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($m['slug']) ?>" class="material-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
        <div class="material-badge badge-<?= $m['section'] ?>">
          <?= ['listening'=>'🎧','structure'=>'📝','reading'=>'📖'][$m['section']] ?>
          <?= sanitize($m['cat_name']) ?>
        </div>
        <h4><?= sanitize($m['title']) ?></h4>
        <p><?= sanitize($m['summary'] ?: 'Klik untuk membaca materi lengkap.') ?></p>
        <div class="material-footer">
          <span style="color:#94A3B8;font-size:0.78rem;">📖 Baca Materi</span>
          <span style="color:#2563EB;font-size:0.82rem;font-weight:600;">→</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php } ?>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>