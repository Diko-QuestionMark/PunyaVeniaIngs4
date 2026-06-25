<?php
require_once '../includes/config.php';
requireUserLogin();
$db  = getDB();
$rid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT r.*,t.title,t.test_type,t.total_questions as test_total
    FROM user_test_results r JOIN tests t ON r.test_id=t.id
    WHERE r.id=? AND r.user_id=?
");
$stmt->execute([$rid,$uid]); $result = $stmt->fetch();
if (!$result) { flashMessage('danger','Hasil tidak ditemukan.'); redirect(SITE_URL.'/pages/dashboard.php'); }

// Load answers with question details
$answers = $db->prepare("
    SELECT a.*,q.question_text,q.option_a,q.option_b,q.option_c,q.option_d,
           q.correct_answer,q.explanation,q.section,q.passage_text
    FROM user_test_answers a JOIN questions q ON a.question_id=q.id
    WHERE a.result_id=? ORDER BY q.section, a.id
");
$answers->execute([$rid]); $answers = $answers->fetchAll();

// Breakdown by section
$secStats = ['listening'=>['correct'=>0,'total'=>0],'structure'=>['correct'=>0,'total'=>0],'reading'=>['correct'=>0,'total'=>0]];
foreach ($answers as $a) {
    $secStats[$a['section']]['total']++;
    if ($a['is_correct']) $secStats[$a['section']]['correct']++;
}

$pct       = $result['total_questions'] > 0 ? round($result['total_correct']/$result['total_questions']*100) : 0;
$scoreColor= $result['toefl_score']>=550?'#10B981':($result['toefl_score']>=450?'#F59E0B':'#EF4444');
$scoreDeg  = round($pct/100*360);
$level     = $result['toefl_score']>=600?'Excellent':($result['toefl_score']>=550?'Advanced':($result['toefl_score']>=500?'Upper-Intermediate':($result['toefl_score']>=450?'Intermediate':'Perlu Latihan Lebih')));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hasil Test — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background:#F1F5F9; }
.result-wrapper { max-width:900px; margin:0 auto; padding:30px 20px 80px; }
.score-ring-outer { width:170px;height:170px;border-radius:50%;background:conic-gradient(<?= $scoreColor ?> <?= $scoreDeg ?>deg, #E2E8F0 0deg);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;position:relative; }
.score-ring-inner { width:140px;height:140px;background:white;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center; }
.tab-btn { padding:10px 22px;border:none;border-radius:100px;background:transparent;font-family:'Poppins',sans-serif;font-weight:600;font-size:0.875rem;cursor:pointer;color:#64748B;transition:all 0.2s; }
.tab-btn.active { background:white;color:#2563EB;box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.review-item { background:white;border-radius:14px;border:1px solid #E2E8F0;padding:24px;margin-bottom:16px; }
.review-item.correct { border-left:4px solid #10B981; }
.review-item.wrong   { border-left:4px solid #EF4444; }
</style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:80px;">
<!-- Hero Result Banner -->
<div style="background:linear-gradient(135deg,#0F172A,#1E293B);padding:50px 5% 40px;text-align:center;">
  <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#60A5FA;margin-bottom:10px;">
    <?= $result['test_type']==='full'?'🏆 Full Test':'⚡ Mini Test' ?> Selesai!
  </div>
  <h1 style="color:white;font-size:1.5rem;margin-bottom:24px;"><?= sanitize($result['title']) ?></h1>

  <!-- Score Ring -->
  <div class="score-ring-outer">
    <div class="score-ring-inner">
      <div style="font-size:2rem;font-weight:900;color:#0F172A;line-height:1;"><?= $result['toefl_score'] ?></div>
      <div style="font-size:0.65rem;color:#94A3B8;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">/677</div>
    </div>
  </div>

  <div style="color:#A78BFA;font-size:1.2rem;font-weight:700;margin-bottom:6px;"><?= $level ?></div>
  <div style="color:#94A3B8;font-size:0.9rem;margin-bottom:30px;">
    <?= $result['total_correct'] ?> / <?= $result['total_questions'] ?> benar (<?= $pct ?>%)
    <?php if ($result['time_taken']): ?>
     · Waktu: <?= floor($result['time_taken']/60) ?>m <?= $result['time_taken']%60 ?>d
    <?php endif; ?>
  </div>

  <!-- Section Breakdown -->
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;max-width:600px;margin:0 auto;">
    <?php foreach(['listening'=>['🎧','Listening','#3B82F6'],'structure'=>['📝','Structure','#8B5CF6'],'reading'=>['📖','Reading','#10B981']] as $sec=>[$icon,$label,$color]): 
      $st = $secStats[$sec];
      $sp = $st['total']>0 ? round($st['correct']/$st['total']*100) : 0;
    ?>
    <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:16px 24px;text-align:center;min-width:150px;">
      <div style="font-size:1.3rem;margin-bottom:4px;"><?= $icon ?></div>
      <div style="font-size:0.7rem;color:#64748B;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;"><?= $label ?></div>
      <div style="font-size:1.4rem;font-weight:800;color:<?= $color ?>;"><?= $st['correct'] ?><span style="font-size:0.9rem;color:#64748B;">/ <?= $st['total'] ?></span></div>
      <div style="font-size:0.75rem;color:#94A3B8;"><?= $sp ?>%</div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="result-wrapper">
  <!-- Action Buttons -->
  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:28px;">
    <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $result['test_id'] ?>" class="btn btn-primary">
      <i class="fas fa-redo"></i> Ulangi Test
    </a>
    <a href="<?= SITE_URL ?>/pages/tests.php" class="btn btn-secondary">
      <i class="fas fa-list"></i> Test Lainnya
    </a>
    <a href="<?= SITE_URL ?>/pages/dashboard.php" class="btn btn-secondary">
      <i class="fas fa-chart-bar"></i> Dashboard
    </a>
    <button onclick="window.print()" class="btn btn-secondary">
      <i class="fas fa-print"></i> Cetak Hasil
    </button>
    <a href="<?= SITE_URL ?>/pages/certificate.php?id=<?= $rid ?>" target="_blank" class="btn" style="background:linear-gradient(135deg,#EAB308,#CA8A04);color:white;border:none;box-shadow:0 4px 12px rgba(234,179,8,0.3);">
      <i class="fas fa-certificate"></i> Lihat Sertifikat
    </a>
  </div>

  <!-- Tabs -->
  <div style="background:#F1F5F9;border-radius:100px;padding:4px;display:inline-flex;gap:2px;margin-bottom:24px;">
    <button class="tab-btn active" id="tab-review" onclick="showTab('review')">📋 Pembahasan Soal</button>
    <button class="tab-btn" id="tab-stats" onclick="showTab('stats')">📊 Statistik</button>
  </div>

  <!-- TAB: Review -->
  <div id="content-review">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
      <h3 style="font-size:1rem;">Pembahasan Soal (<?= count($answers) ?> soal)</h3>
      <div style="display:flex;gap:8px;">
        <button onclick="filterReview('all')" class="btn btn-secondary btn-sm" id="fAll">Semua</button>
        <button onclick="filterReview('wrong')" class="btn btn-secondary btn-sm" id="fWrong" style="color:#EF4444;">❌ Salah (<?= count(array_filter($answers,fn($a)=>!$a['is_correct'])) ?>)</button>
        <button onclick="filterReview('correct')" class="btn btn-secondary btn-sm" id="fCorrect" style="color:#10B981;">✅ Benar (<?= count(array_filter($answers,fn($a)=>$a['is_correct'])) ?>)</button>
      </div>
    </div>

    <?php foreach($answers as $i=>$a): ?>
    <div class="review-item <?= $a['is_correct']?'correct':'wrong' ?>" data-status="<?= $a['is_correct']?'correct':'wrong' ?>">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="background:<?= $a['is_correct']?'#D1FAE5':'#FEE2E2' ?>;color:<?= $a['is_correct']?'#065F46':'#991B1B' ?>;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;flex-shrink:0;">
            <?= $a['is_correct']?'✓':'✗' ?>
          </span>
          <span style="font-size:0.75rem;font-weight:700;color:#94A3B8;">Soal <?= $i+1 ?> · <?= ucfirst($a['section']) ?></span>
        </div>
        <div style="font-size:0.8rem;<?= $a['is_correct']?'color:#10B981;':'color:#EF4444;' ?>;font-weight:600;white-space:nowrap;">
          <?= $a['is_correct']?'Benar ✓':'Salah ✗' ?>
        </div>
      </div>

      <?php if ($a['passage_text']): ?>
      <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px 16px;margin-bottom:10px;font-size:0.85rem;line-height:1.7;color:#475569;max-height:140px;overflow-y:auto;">
        <?= nl2br(sanitize($a['passage_text'])) ?>
      </div>
      <?php endif; ?>

      <p style="font-size:0.9rem;font-weight:500;color:#1E293B;line-height:1.6;margin-bottom:14px;"><?= nl2br(sanitize($a['question_text'])) ?></p>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
        <?php foreach(['A','B','C','D'] as $opt):
          $key = 'option_'.strtolower($opt);
          $isCorrect = $opt === $a['correct_answer'];
          $isUser    = $opt === $a['user_answer'];
          $bg = $isCorrect ? '#D1FAE5' : ($isUser && !$isCorrect ? '#FEE2E2' : '#F8FAFC');
          $border = $isCorrect ? '#10B981' : ($isUser && !$isCorrect ? '#EF4444' : '#E2E8F0');
          $color = $isCorrect ? '#065F46' : ($isUser && !$isCorrect ? '#991B1B' : '#475569');
        ?>
        <div style="background:<?= $bg ?>;border:1.5px solid <?= $border ?>;border-radius:8px;padding:8px 12px;display:flex;align-items:flex-start;gap:8px;">
          <span style="width:22px;height:22px;border-radius:6px;background:<?= $border ?>;color:white;font-size:0.72rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $opt ?></span>
          <span style="font-size:0.82rem;color:<?= $color ?>;line-height:1.4;"><?= sanitize($a[$key]) ?></span>
          <?php if ($isCorrect): ?><span style="margin-left:auto;color:#10B981;font-size:0.8rem;">✓</span><?php endif; ?>
          <?php if ($isUser && !$isCorrect): ?><span style="margin-left:auto;color:#EF4444;font-size:0.8rem;">✗</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($a['explanation']): ?>
      <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:8px;padding:12px 16px;display:flex;gap:8px;">
        <span>💡</span>
        <div>
          <div style="font-size:0.75rem;font-weight:700;color:#1D4ED8;margin-bottom:4px;">Penjelasan</div>
          <div style="font-size:0.85rem;color:#1E40AF;line-height:1.6;"><?= sanitize($a['explanation']) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- TAB: Stats -->
  <div id="content-stats" style="display:none;">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:24px;">
      <?php foreach(['listening'=>['🎧','Listening','#3B82F6'],'structure'=>['📝','Structure','#8B5CF6'],'reading'=>['📖','Reading','#10B981']] as $sec=>[$icon,$label,$color]):
        $st = $secStats[$sec];
        $sp = $st['total']>0 ? round($st['correct']/$st['total']*100) : 0;
      ?>
      <div style="background:white;border:1px solid #E2E8F0;border-radius:16px;padding:24px;">
        <div style="font-size:1.5rem;margin-bottom:8px;"><?= $icon ?></div>
        <div style="font-size:0.8rem;font-weight:700;text-transform:uppercase;color:#94A3B8;letter-spacing:0.06em;margin-bottom:4px;"><?= $label ?></div>
        <div style="font-size:1.8rem;font-weight:800;color:<?= $color ?>;margin-bottom:4px;"><?= $sp ?>%</div>
        <div style="font-size:0.82rem;color:#64748B;"><?= $st['correct'] ?>/<?= $st['total'] ?> benar</div>
        <div style="margin-top:12px;height:6px;background:#F1F5F9;border-radius:100px;overflow:hidden;">
          <div style="height:100%;background:<?= $color ?>;width:<?= $sp ?>%;border-radius:100px;transition:width 1s ease;"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Score Table -->
    <div style="background:white;border:1px solid #E2E8F0;border-radius:16px;padding:24px;">
      <h3 style="font-size:1rem;margin-bottom:16px;">📋 Ringkasan Hasil</h3>
      <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
        <?php $rows=[
          ['Skor TOEFL ITP','<strong style="font-size:1.2rem;color:#2563EB;">'.$result['toefl_score'].'</strong> / 677'],
          ['Level',          '<span style="font-weight:700;color:'.$scoreColor.';">'.$level.'</span>'],
          ['Total Benar',    $result['total_correct'].' dari '.$result['total_questions'].' soal'],
          ['Persentase',     $pct.'%'],
          ['Waktu Pengerjaan', $result['time_taken'] ? floor($result['time_taken']/60).'m '.($result['time_taken']%60).'d' : '-'],
          ['Tanggal Test',   date('d F Y, H:i',strtotime($result['completed_at']))],
        ];
        foreach($rows as $row): ?>
        <tr style="border-bottom:1px solid #F1F5F9;">
          <td style="padding:10px 0;color:#64748B;font-weight:500;"><?= $row[0] ?></td>
          <td style="padding:10px 0;text-align:right;"><?= $row[1] ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>
</div>

<script>
function showTab(tab) {
  ['review','stats'].forEach(t=>{
    document.getElementById('content-'+t).style.display = t===tab?'block':'none';
    document.getElementById('tab-'+t).classList.toggle('active', t===tab);
  });
}
function filterReview(filter) {
  document.querySelectorAll('.review-item').forEach(el=>{
    el.style.display = filter==='all' || el.dataset.status===filter ? 'block' : 'none';
  });
  ['all','wrong','correct'].forEach(f=>{
    document.getElementById('f'+f.charAt(0).toUpperCase()+f.slice(1))?.classList.toggle('btn-primary', f===filter);
  });
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>