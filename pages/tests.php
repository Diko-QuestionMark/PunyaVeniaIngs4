<?php
require_once '../includes/config.php';
$db = getDB();
$type = $_GET['type'] ?? '';
$where = $type ? "WHERE test_type=?" : "WHERE 1";
$params = $type ? [$type] : [];
$stmt = $db->prepare("SELECT * FROM tests $where AND is_published=1 ORDER BY test_type='full' DESC, id");
$stmt->execute($params); $tests = $stmt->fetchAll();
$miniTests = array_filter($tests, fn($t)=>$t['test_type']==='mini');
$fullTests = array_filter($tests, fn($t)=>$t['test_type']==='full');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Latihan Soal TOEFL — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:90px;">
  <!-- Page Header -->
  <div style="background:linear-gradient(135deg,#1E293B,#0F172A);padding:60px 5% 50px;text-align:center;" data-aos="fade-down">
    <div style="display:inline-block;background:rgba(37,99,235,0.15);border:1px solid rgba(37,99,235,0.3);color:#60A5FA;padding:6px 16px;border-radius:100px;font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Latihan Soal</div>
    <h1 style="color:white;font-size:2rem;margin-bottom:12px;">Pilih Mode Latihan</h1>
    <p style="color:#94A3B8;font-size:1rem;max-width:550px;margin:0 auto;line-height:1.6;">Waktunya membuktikan hasil belajar Anda. Hadapi tantangan soal yang dirancang khusus untuk mendongkrak skor TOEFL Anda ke level berikutnya.</p>
    <div style="display:flex;gap:10px;justify-content:center;margin-top:24px;">
      <a href="?type=" class="btn btn-sm <?= !$type?'btn-primary':'btn-secondary' ?>" style="<?= !$type?'':'background:rgba(255,255,255,0.08);color:white;border-color:rgba(255,255,255,0.2);' ?>">Semua</a>
      <a href="?type=mini" class="btn btn-sm <?= $type==='mini'?'btn-primary':'btn-secondary' ?>" style="<?= $type==='mini'?'':'background:rgba(255,255,255,0.08);color:white;border-color:rgba(255,255,255,0.2);' ?>">⚡ Mini Test</a>
      <a href="?type=full" class="btn btn-sm <?= $type==='full'?'btn-primary':'btn-secondary' ?>" style="<?= $type==='full'?'':'background:rgba(255,255,255,0.08);color:white;border-color:rgba(255,255,255,0.2);' ?>">🏆 Full Test</a>
    </div>
  </div>

  <div style="max-width:1200px;margin:0 auto;padding:50px 5% 80px;">

    <?php if(!$type || $type==='full'): ?>
    <!-- FULL TESTS -->
    <div style="margin-bottom:48px;">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;" data-aos="fade-right">
        <div style="width:44px;height:44px;background:#EDE9FE;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">🏆</div>
        <div>
          <h2 style="font-size:1.2rem;margin-bottom:2px;">Full Test — Simulasi TOEFL</h2>
          <p style="color:#64748B;font-size:0.85rem;">Rasakan atmosfer dan tantangan ujian TOEFL ITP yang sesungguhnya. Siapkan fokus penuh Anda!</p>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px;">
        <?php foreach($fullTests as $index => $t): ?>
        <div class="test-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
          <div class="test-type-badge badge-full">🏆 Full Test</div>
          <h3><?= sanitize($t['title']) ?></h3>
          <p><?= sanitize($t['description'] ?: 'Simulasi TOEFL lengkap dengan '.$t['total_questions'].' soal mencakup semua bagian.') ?></p>
          <div class="test-meta">
            <div class="test-meta-item"><i class="fas fa-list-ol"></i> <?= $t['total_questions'] ?> Soal</div>
            <div class="test-meta-item"><i class="fas fa-clock"></i> <?= $t['time_limit'] ?> Menit</div>
            <div class="test-meta-item"><i class="fas fa-chart-bar"></i> Skor 200–677</div>
          </div>
          <div style="display:flex;gap:8px;margin-top:auto;">
            <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $t['id'] ?>" class="btn btn-primary" style="flex:1;justify-content:center;">
              Mulai Full Test <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
        <?php endforeach; if(empty($fullTests)): ?>
        <div style="text-align:center;padding:40px;color:#94A3B8;">Belum ada full test tersedia.</div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if(!$type || $type==='mini'): ?>
    <!-- MINI TESTS -->
    <div>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;" data-aos="fade-right">
        <div style="width:44px;height:44px;background:#FEF3C7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">⚡</div>
        <div>
          <h2 style="font-size:1.2rem;margin-bottom:2px;">Mini Test — Latihan Fokus</h2>
          <p style="color:#64748B;font-size:0.85rem;">Latihan singkat namun padat, sangat cocok untuk mengevaluasi pemahaman materi Anda hari ini.</p>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
        <?php foreach($miniTests as $index => $t): ?>
        <div class="test-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
          <div class="test-type-badge badge-mini">⚡ Mini Test</div>
          <h3><?= sanitize($t['title']) ?></h3>
          <p><?= sanitize($t['description'] ?: 'Latihan terfokus dengan '.$t['total_questions'].' soal pilihan.') ?></p>
          <div class="test-meta">
            <div class="test-meta-item"><i class="fas fa-list-ol"></i> <?= $t['total_questions'] ?> Soal</div>
            <div class="test-meta-item"><i class="fas fa-clock"></i> <?= $t['time_limit'] ?> Menit</div>
          </div>
          <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $t['id'] ?>" class="btn btn-primary" style="margin-top:auto;justify-content:center;">
            Mulai Mini Test <i class="fas fa-arrow-right"></i>
          </a>
        </div>
        <?php endforeach; if(empty($miniTests)): ?>
        <div style="text-align:center;padding:40px;color:#94A3B8;">Belum ada mini test tersedia.</div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>