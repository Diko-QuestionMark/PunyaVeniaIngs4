<?php
require_once '../includes/config.php';
$db  = getDB();
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$pid) redirect(SITE_URL.'/pages/materials.php');

$stmt = $db->prepare("SELECT ps.*,m.title as mat_title,m.slug as mat_slug FROM practice_sets ps JOIN materials m ON ps.material_id=m.id WHERE ps.id=? AND ps.is_published=1");
$stmt->execute([$pid]); $practice = $stmt->fetch();
if (!$practice) { flashMessage('danger','Latihan tidak ditemukan.'); redirect(SITE_URL.'/pages/materials.php'); }

$qStmt = $db->prepare("SELECT q.* FROM practice_questions pq JOIN questions q ON pq.question_id=q.id WHERE pq.practice_set_id=? ORDER BY pq.sort_order");
$qStmt->execute([$pid]); $questions = $qStmt->fetchAll();
if (empty($questions)) { flashMessage('danger','Latihan ini belum memiliki soal.'); redirect(SITE_URL.'/pages/material.php?slug='.$practice['mat_slug']); }

// Handle submission
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_practice'])) {
    $answers  = $_POST['answers'] ?? [];
    $correct  = 0; $total = count($questions);
    $details  = [];
    foreach ($questions as $q) {
        $ua  = $answers[$q['id']] ?? null;
        $ok  = $ua === $q['correct_answer'] ? 1 : 0;
        if ($ok) $correct++;
        $details[$q['id']] = ['ua'=>$ua,'ok'=>$ok,'correct'=>$q['correct_answer'],'exp'=>$q['explanation'],'options'=>['A'=>$q['option_a'],'B'=>$q['option_b'],'C'=>$q['option_c'],'D'=>$q['option_d']],'text'=>$q['question_text'],'passage'=>$q['passage_text']];
    }
    $pct = $total>0 ? round($correct/$total*100) : 0;
    if (isUserLoggedIn()) {
        $db->prepare("INSERT INTO user_practice_results (user_id,practice_set_id,score,total_correct,total_questions) VALUES (?,?,?,?,?)")
           ->execute([$_SESSION['user_id'],$pid,$pct,$correct,$total]);
    }
    // Show result inline
    $showResult = true; $resultData = ['correct'=>$correct,'total'=>$total,'pct'=>$pct,'details'=>$details];
} else { $showResult = false; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($practice['title']) ?> — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background:#F1F5F9; }
.practice-wrap { max-width:800px; margin:0 auto; padding:30px 20px 80px; }
</style>
</head>
<body>
<div class="quiz-header">
  <div style="display:flex;align-items:center;gap:14px;flex:1;">
    <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($practice['mat_slug']) ?>" style="color:#94A3B8;"><i class="fas fa-arrow-left"></i></a>
    <div>
      <div style="color:white;font-weight:700;font-size:0.95rem;"><?= sanitize($practice['title']) ?></div>
      <div style="color:#64748B;font-size:0.75rem;">Latihan · <?= count($questions) ?> soal · <?= $practice['time_limit'] ?> menit</div>
    </div>
  </div>
  <?php if (!$showResult): ?>
  <div class="quiz-timer" id="timer"><i class="fas fa-clock"></i> <span id="timerD"><?= sprintf('%02d:00', $practice['time_limit']) ?></span></div>
  <?php endif; ?>
</div>

<div class="quiz-wrapper practice-wrap" style="margin-top:20px;">

<?php if ($showResult):
  $r = $resultData;
  $medal = $r['pct']>=80?'🏆':($r['pct']>=60?'🎯':'💪');
?>
<!-- RESULT PANEL -->
<div style="background:white;border-radius:20px;border:1px solid #E2E8F0;padding:36px;text-align:center;margin-bottom:28px;">
  <div style="font-size:3rem;margin-bottom:12px;"><?= $medal ?></div>
  <h2 style="font-size:1.4rem;margin-bottom:6px;">Latihan Selesai!</h2>
  <div style="font-size:2.5rem;font-weight:900;color:<?= $r['pct']>=60?'#10B981':'#F59E0B' ?>;margin:12px 0;"><?= $r['correct'] ?><span style="font-size:1.2rem;color:#94A3B8;"> / <?= $r['total'] ?></span></div>
  <div style="font-size:1rem;color:#64748B;margin-bottom:20px;"><?= $r['pct'] ?>% benar</div>
  <?php if ($r['pct']>=80): ?>
  <div style="background:#D1FAE5;color:#065F46;padding:10px 16px;border-radius:10px;font-size:0.875rem;font-weight:600;display:inline-block;">✅ Bagus sekali! Kamu sudah menguasai materi ini dengan baik.</div>
  <?php elseif($r['pct']>=60): ?>
  <div style="background:#FEF3C7;color:#92400E;padding:10px 16px;border-radius:10px;font-size:0.875rem;font-weight:600;display:inline-block;">📖 Cukup baik! Pelajari kembali materi yang masih kurang.</div>
  <?php else: ?>
  <div style="background:#FEE2E2;color:#991B1B;padding:10px 16px;border-radius:10px;font-size:0.875rem;font-weight:600;display:inline-block;">💪 Jangan menyerah! Pelajari ulang materinya dan coba lagi.</div>
  <?php endif; ?>
  <div style="display:flex;gap:10px;justify-content:center;margin-top:20px;flex-wrap:wrap;">
    <a href="?id=<?= $pid ?>" class="btn btn-primary"><i class="fas fa-redo"></i> Ulangi Latihan</a>
    <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($practice['mat_slug']) ?>" class="btn btn-secondary"><i class="fas fa-book"></i> Kembali ke Materi</a>
  </div>
</div>

<!-- Answer Review -->
<h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">📋 Pembahasan</h3>
<?php foreach($questions as $i=>$q):
  $d = $r['details'][$q['id']];
?>
<div style="background:white;border-radius:14px;border:1px solid #E2E8F0;border-left:4px solid <?= $d['ok']?'#10B981':'#EF4444' ?>;padding:22px;margin-bottom:14px;">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
    <span style="background:<?= $d['ok']?'#D1FAE5':'#FEE2E2' ?>;color:<?= $d['ok']?'#065F46':'#991B1B' ?>;width:26px;height:26px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;flex-shrink:0;"><?= $d['ok']?'✓':'✗' ?></span>
    <span style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;">Soal <?= $i+1 ?></span>
  </div>
  <?php if($d['passage']): ?>
  <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:10px 14px;margin-bottom:10px;font-size:0.85rem;line-height:1.7;color:#475569;"><?= nl2br(sanitize($d['passage'])) ?></div>
  <?php endif; ?>
  <p style="font-size:0.9rem;font-weight:500;color:#1E293B;margin-bottom:12px;"><?= nl2br(sanitize($d['text'])) ?></p>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:10px;">
    <?php foreach(['A','B','C','D'] as $opt):
      $isC = $opt===$d['correct']; $isU = $opt===$d['ua'];
    ?>
    <div style="background:<?= $isC?'#D1FAE5':($isU&&!$isC?'#FEE2E2':'#F8FAFC') ?>;border:1.5px solid <?= $isC?'#10B981':($isU&&!$isC?'#EF4444':'#E2E8F0') ?>;border-radius:8px;padding:7px 12px;font-size:0.82rem;display:flex;gap:8px;align-items:flex-start;">
      <strong style="min-width:14px;"><?= $opt ?>.</strong> <?= sanitize($d['options'][$opt]) ?>
      <?php if($isC): ?><span style="margin-left:auto;color:#10B981;">✓</span><?php endif; ?>
      <?php if($isU&&!$isC): ?><span style="margin-left:auto;color:#EF4444;">✗</span><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if($d['exp']): ?>
  <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:8px;padding:10px 14px;font-size:0.82rem;color:#1E40AF;">💡 <?= sanitize($d['exp']) ?></div>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<?php else: ?>
<!-- QUIZ FORM -->
<form method="POST" id="practiceForm">
<input type="hidden" name="submit_practice" value="1">
  <?php foreach($questions as $i=>$q): ?>
  <div class="question-card" style="margin-bottom:20px;">
    <?php if($q['passage_text']): ?>
    <div class="passage-box"><?= nl2br(sanitize($q['passage_text'])) ?></div>
    <?php endif; ?>
    <div class="question-number">Soal <?= $i+1 ?> dari <?= count($questions) ?></div>
    <div class="question-text"><?= nl2br(sanitize($q['question_text'])) ?></div>
    <div class="options-list">
      <?php foreach(['A','B','C','D'] as $opt): $key='option_'.strtolower($opt); ?>
      <label class="option-item" onclick="this.parentElement.querySelectorAll('.option-item').forEach(x=>x.classList.remove('selected'));this.classList.add('selected');">
        <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt ?>" style="display:none;">
        <div class="option-key"><?= $opt ?></div>
        <div class="option-text"><?= sanitize($q[$key]) ?></div>
      </label>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <div style="text-align:center;padding:24px;background:white;border-radius:16px;border:1px solid #E2E8F0;">
    <p style="color:#64748B;font-size:0.875rem;margin-bottom:16px;">Pastikan semua soal sudah dijawab</p>
    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Submit latihan sekarang?')">
      <i class="fas fa-check"></i> Submit & Lihat Pembahasan
    </button>
  </div>
</form>

<script>
let rem = <?= $practice['time_limit'] * 60 ?>;
const ti = setInterval(()=>{
  rem--;
  if(rem<=0){clearInterval(ti);document.getElementById('practiceForm').submit();return;}
  const m=Math.floor(rem/60),s=rem%60;
  document.getElementById('timerD').textContent=String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
  if(rem<=60)document.getElementById('timer').classList.add('warning');
},1000);
</script>
<?php endif; ?>
</div>
</body>
</html>