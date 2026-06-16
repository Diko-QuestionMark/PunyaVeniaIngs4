<?php
require_once '../includes/config.php';
$db   = getDB();
$slug = $_GET['slug'] ?? '';
if (!$slug) redirect(SITE_URL.'/pages/materials.php');

$stmt = $db->prepare("SELECT m.*,c.name as cat_name,c.section,c.slug as cat_slug FROM materials m JOIN categories c ON m.category_id=c.id WHERE m.slug=? AND m.is_published=1");
$stmt->execute([$slug]); $material = $stmt->fetch();
if (!$material) { flashMessage('danger','Materi tidak ditemukan.'); redirect(SITE_URL.'/pages/materials.php'); }

// Sidebar: all materials in same section
$siblings = $db->prepare("SELECT m.title,m.slug,c.name as cat_name FROM materials m JOIN categories c ON m.category_id=c.id WHERE c.section=? AND m.is_published=1 ORDER BY c.sort_order,m.sort_order");
$siblings->execute([$material['section']]); $siblings = $siblings->fetchAll();

// Practice sets for this material
$practice = $db->prepare("SELECT * FROM practice_sets WHERE material_id=? AND is_published=1");
$practice->execute([$material['id']]); $practice = $practice->fetchAll();

// Prev / Next
$allMat = $db->query("SELECT id,slug,title FROM materials WHERE is_published=1 ORDER BY id")->fetchAll();
$matIdx = array_search($material['id'], array_column($allMat,'id'));
$prevMat = $matIdx > 0 ? $allMat[$matIdx-1] : null;
$nextMat = $matIdx < count($allMat)-1 ? $allMat[$matIdx+1] : null;

$secLabel = ['listening'=>'🎧 Listening','structure'=>'📝 Structure','reading'=>'📖 Reading'][$material['section']];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($material['title']) ?> — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <?= $secLabel ?>
    </div>
    <nav class="sidebar-nav">
      <?php 
      $lastCat = ''; 
      foreach($siblings as $s): 
        if ($s['cat_name'] !== $lastCat): $lastCat = $s['cat_name']; ?>
      <div class="sidebar-section-header"><?= sanitize($s['cat_name']) ?></div>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($s['slug']) ?>" 
         class="sidebar-link <?= $s['slug']===$slug?'active':'' ?>">
        <?= sanitize($s['title']) ?>
      </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main>
    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/index.php">Beranda</a>
      <span class="breadcrumb-sep">›</span>
      <a href="<?= SITE_URL ?>/pages/<?= $material['section'] ?>.php"><?= ucfirst($material['section']) ?></a>
      <span class="breadcrumb-sep">›</span>
      <span style="color:#0F172A;"><?= sanitize($material['title']) ?></span>
    </div>

    <!-- Material Content -->
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;padding:36px;margin-bottom:24px;">
      <div class="content-body">
        <?= $material['content'] // Already HTML from admin ?>
      </div>
    </div>

    <!-- Practice Sets -->
    <?php if (!empty($practice)): ?>
    <div style="background:linear-gradient(135deg,#EFF6FF,#F5F3FF);border-radius:20px;border:1px solid #BFDBFE;padding:28px;margin-bottom:24px;">
      <h3 style="font-size:1.1rem;margin-bottom:6px;">✏️ Latihan Soal Materi Ini</h3>
      <p style="color:#64748B;font-size:0.875rem;margin-bottom:20px;">Uji pemahamanmu dengan latihan soal yang relevan.</p>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
        <?php foreach($practice as $ps): ?>
        <div style="background:white;border-radius:14px;border:1px solid #E2E8F0;padding:20px;transition:all 0.2s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
          <div style="font-size:1rem;font-weight:700;margin-bottom:6px;"><?= sanitize($ps['title']) ?></div>
          <?php if($ps['description']): ?>
          <p style="font-size:0.82rem;color:#94A3B8;margin-bottom:12px;"><?= sanitize($ps['description']) ?></p>
          <?php endif; ?>
          <div style="display:flex;gap:12px;font-size:0.78rem;color:#94A3B8;margin-bottom:14px;">
            <span><i class="fas fa-clock"></i> <?= $ps['time_limit'] ?> menit</span>
          </div>
          <a href="<?= SITE_URL ?>/pages/take-practice.php?id=<?= $ps['id'] ?>" class="btn btn-primary btn-sm btn-block" style="justify-content:center;">
            Mulai Latihan <i class="fas fa-arrow-right"></i>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Prev/Next Navigation -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:10px;">
      <?php if($prevMat): ?>
      <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($prevMat['slug']) ?>" 
         style="background:white;border:1px solid #E2E8F0;border-radius:14px;padding:16px 20px;display:flex;align-items:center;gap:10px;transition:all 0.2s;" 
         onmouseover="this.style.borderColor='#2563EB'" onmouseout="this.style.borderColor='#E2E8F0'">
        <i class="fas fa-arrow-left" style="color:#94A3B8;"></i>
        <div>
          <div style="font-size:0.72rem;color:#94A3B8;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Sebelumnya</div>
          <div style="font-size:0.875rem;font-weight:600;color:#0F172A;"><?= sanitize($prevMat['title']) ?></div>
        </div>
      </a>
      <?php else: ?><div></div><?php endif; ?>
      <?php if($nextMat): ?>
      <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($nextMat['slug']) ?>" 
         style="background:white;border:1px solid #E2E8F0;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:flex-end;gap:10px;transition:all 0.2s;" 
         onmouseover="this.style.borderColor='#2563EB'" onmouseout="this.style.borderColor='#E2E8F0'">
        <div style="text-align:right;">
          <div style="font-size:0.72rem;color:#94A3B8;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Selanjutnya</div>
          <div style="font-size:0.875rem;font-weight:600;color:#0F172A;"><?= sanitize($nextMat['title']) ?></div>
        </div>
        <i class="fas fa-arrow-right" style="color:#94A3B8;"></i>
      </a>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>