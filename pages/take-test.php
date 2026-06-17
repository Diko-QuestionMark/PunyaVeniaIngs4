<?php
require_once '../includes/config.php';
$db  = getDB();
$tid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$tid) { redirect(SITE_URL.'/pages/tests.php'); }

$stmt = $db->prepare("SELECT * FROM tests WHERE id=? AND is_published=1"); $stmt->execute([$tid]);
$test = $stmt->fetch();
if (!$test) { flashMessage('danger','Test tidak ditemukan.'); redirect(SITE_URL.'/pages/tests.php'); }

// Load questions
$qStmt = $db->prepare("
    SELECT q.* FROM test_questions tq
    JOIN questions q ON tq.question_id = q.id
    WHERE tq.test_id = ? ORDER BY tq.sort_order
");
$qStmt->execute([$tid]); $questions = $qStmt->fetchAll();
if (empty($questions)) { flashMessage('danger','Test ini belum memiliki soal.'); redirect(SITE_URL.'/pages/tests.php'); }

// Handle submission
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_test'])) {
    if (!isUserLoggedIn()) { redirect(SITE_URL.'/pages/login.php'); }
    $uid      = $_SESSION['user_id'];
    $answers  = $_POST['answers'] ?? [];
    $timeTaken= (int)($_POST['time_taken'] ?? 0);
    $correct  = 0; $total = count($questions);

    // Calculate score
    $detailRows = [];
    foreach ($questions as $q) {
        $userAns = $answers[$q['id']] ?? null;
        $isOk    = $userAns === $q['correct_answer'] ? 1 : 0;
        if ($isOk) $correct++;
        $detailRows[] = ['qid'=>$q['id'], 'ans'=>$userAns, 'ok'=>$isOk];
    }
    $toeflScore = calculateTOEFLScore($correct, $total);
    $pct        = $total > 0 ? round($correct/$total*100) : 0;

    $db->prepare("INSERT INTO user_test_results (user_id,test_id,score,toefl_score,total_correct,total_questions,time_taken,started_at,completed_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())")
       ->execute([$uid,$tid,$pct,$toeflScore,$correct,$total,$timeTaken]);
    $resultId = $db->lastInsertId();

    $ins = $db->prepare("INSERT INTO user_test_answers (result_id,question_id,user_answer,is_correct) VALUES (?,?,?,?)");
    foreach ($detailRows as $d) $ins->execute([$resultId,$d['qid'],$d['ans'],$d['ok']]);

    redirect(SITE_URL.'/pages/result.php?id='.$resultId);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($test['title']) ?> — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background: #F1F5F9; }
.quiz-wrapper { max-width: 860px; margin: 0 auto; padding: 30px 20px 80px; }
.quiz-nav-dots { display: flex; flex-wrap: wrap; gap: 6px; padding: 16px 20px; background: white; border-radius: 12px; margin-bottom: 20px; border: 1px solid #E2E8F0; }
.q-dot { width: 32px; height: 32px; border-radius: 8px; border: 2px solid #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all 0.2s; color: #64748B; background: white; }
.q-dot.answered { background: #DBEAFE; border-color: #2563EB; color: #1D4ED8; }
.q-dot.current  { background: #2563EB; border-color: #2563EB; color: white; }
.passage-box { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; max-height: 220px; overflow-y: auto; font-size: 0.9rem; line-height: 1.8; color: #334155; }
</style>
</head>
<body>
<!-- QUIZ HEADER -->
<div class="quiz-header">
  <div style="display:flex;align-items:center;gap:16px;flex:1;">
    <a href="<?= SITE_URL ?>/pages/tests.php" onclick="return confirm('Keluar dari test? Progress akan hilang.')" style="color:#94A3B8;font-size:1rem;"><i class="fas fa-times"></i></a>
    <div>
      <div style="color:white;font-weight:700;font-size:0.95rem;"><?= sanitize($test['title']) ?></div>
      <div style="color:#64748B;font-size:0.75rem;"><?= count($questions) ?> soal · <?= $test['test_type']==='full'?'Simulasi TOEFL':'Mini Test' ?></div>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:16px;">
    <div id="progressLabel" style="color:#94A3B8;font-size:0.82rem;">0 / <?= count($questions) ?> dijawab</div>
    <div class="quiz-timer" id="timer">
      <i class="fas fa-clock"></i> <span id="timerDisplay"><?= sprintf('%02d:%02d', floor($test['time_limit']*60/60), ($test['time_limit']*60)%60) ?></span>
    </div>
  </div>
</div>

<!-- PROGRESS BAR -->
<div class="quiz-progress-bar" style="position:fixed;top:60px;left:0;right:0;z-index:99;">
  <div class="quiz-progress-fill" id="progressBar" style="width:0%;"></div>
</div>

<form method="POST" id="testForm">
<input type="hidden" name="submit_test" value="1">
<input type="hidden" name="time_taken" id="timeTakenField" value="0">

<div class="quiz-wrapper">
  <!-- Dot Navigation -->
  <div class="quiz-nav-dots" id="dotNav">
    <?php foreach($questions as $i=>$q): ?>
    <div class="q-dot <?= $i===0?'current':'' ?>" id="dot-<?= $i ?>" onclick="goTo(<?= $i ?>)"><?= $i+1 ?></div>
    <?php endforeach; ?>
  </div>

  <!-- Questions -->
  <?php foreach($questions as $i=>$q): ?>
  <div class="question-card" id="qcard-<?= $i ?>" style="<?= $i>0?'display:none;':'' ?>margin-bottom:20px;">

    <?php if ($q['passage_text']): ?>
    <div class="passage-box">
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#94A3B8;letter-spacing:0.08em;margin-bottom:10px;">📄 Bacaan</div>
      <?= nl2br(sanitize($q['passage_text'])) ?>
    </div>
    <?php endif; ?>

    <div class="question-number">
      <?= ['listening'=>'🎧 Listening','structure'=>'📝 Structure','reading'=>'📖 Reading'][$q['section']] ?>
      · Soal <?= $i+1 ?> dari <?= count($questions) ?>
    </div>
    <div class="question-text"><?= nl2br(sanitize($q['question_text'])) ?></div>

    <?php if ($q['audio_file']): ?>
    <div style="margin-bottom:20px;">
      <audio controls style="width:100%;border-radius:8px;">
        <source src="<?= SITE_URL ?>/uploads/audio/<?= sanitize($q['audio_file']) ?>" type="audio/mpeg">
      </audio>
    </div>
    <?php endif; ?>

    <div class="options-list">
      <?php foreach(['A','B','C','D'] as $opt):
        $key = 'option_'.strtolower($opt);
      ?>
      <label class="option-item" id="opt-<?= $i ?>-<?= $opt ?>" onclick="selectOption(<?= $i ?>,'<?= $q['id'] ?>','<?= $opt ?>')">
        <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt ?>" style="display:none;">
        <div class="option-key"><?= $opt ?></div>
        <div class="option-text"><?= sanitize($q[$key]) ?></div>
      </label>
      <?php endforeach; ?>
    </div>

    <!-- Navigation buttons -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:28px;">
      <button type="button" class="btn btn-secondary" onclick="goTo(<?= $i-1 ?>)" <?= $i===0?'disabled':'' ?>>
        <i class="fas fa-arrow-left"></i> Sebelumnya
      </button>
      <div style="font-size:0.82rem;color:#94A3B8;"><?= $i+1 ?>/<?= count($questions) ?></div>
      <?php if ($i < count($questions)-1): ?>
      <button type="button" class="btn btn-primary" onclick="goTo(<?= $i+1 ?>)">
        Selanjutnya <i class="fas fa-arrow-right"></i>
      </button>
      <?php else: ?>
      <button type="button" class="btn btn-success" onclick="submitTest()">
        <i class="fas fa-check"></i> Selesai & Submit
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
</form>

<!-- Confirm exit modal -->
<div id="submitModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;display:none;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:20px;padding:40px;max-width:420px;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,0.3);">
    <div style="font-size:2.5rem;margin-bottom:16px;">⚠️</div>
    <h3 style="margin-bottom:8px;">Submit Test?</h3>
    <p style="color:#64748B;margin-bottom:8px;" id="unansweredMsg"></p>
    <p style="color:#94A3B8;font-size:0.85rem;margin-bottom:24px;">Setelah submit, jawaban tidak dapat diubah.</p>
    <div style="display:flex;gap:12px;justify-content:center;">
      <button onclick="document.getElementById('submitModal').style.display='none'" class="btn btn-secondary">Kembali</button>
      <button onclick="document.getElementById('testForm').submit()" class="btn btn-success">Ya, Submit!</button>
    </div>
  </div>
</div>

<script>
const TOTAL     = <?= count($questions) ?>;
const TIME_SEC  = <?= $test['time_limit'] * 60 ?>;
let current     = 0;
let answered    = {};
let elapsed     = 0;
let timerInt;

// Timer
function startTimer() {
  let remaining = TIME_SEC;
  timerInt = setInterval(() => {
    elapsed++;
    document.getElementById('timeTakenField').value = elapsed;
    remaining--;
    if (remaining <= 0) { clearInterval(timerInt); autoSubmit(); return; }
    const m = Math.floor(remaining/60), s = remaining%60;
    document.getElementById('timerDisplay').textContent = String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    if (remaining <= 300) document.getElementById('timer').classList.add('warning');
  }, 1000);
}

function autoSubmit() {
  alert('Waktu habis! Test akan disubmit otomatis.');
  document.getElementById('testForm').submit();
}

function goTo(idx) {
  if (idx < 0 || idx >= TOTAL) return;
  document.getElementById('qcard-'+current).style.display = 'none';
  document.getElementById('dot-'+current).classList.remove('current');
  current = idx;
  document.getElementById('qcard-'+current).style.display = 'block';
  document.getElementById('dot-'+current).classList.add('current');
  window.scrollTo({top:0,behavior:'smooth'});
}

function selectOption(qIdx, qId, opt) {
  // Remove selected from all options in this question
  ['A','B','C','D'].forEach(o => {
    document.getElementById('opt-'+qIdx+'-'+o)?.classList.remove('selected');
  });
  document.getElementById('opt-'+qIdx+'-'+opt)?.classList.add('selected');
  document.querySelector(`input[name="answers[${qId}]"][value="${opt}"]`).checked = true;
  answered[qIdx] = true;
  updateProgress();
  // Mark dot
  const dot = document.getElementById('dot-'+qIdx);
  if (dot) { dot.classList.add('answered'); }
}

function updateProgress() {
  const cnt = Object.keys(answered).length;
  const pct = (cnt/TOTAL)*100;
  document.getElementById('progressBar').style.width = pct+'%';
  document.getElementById('progressLabel').textContent = cnt+' / '+TOTAL+' dijawab';
}

function submitTest() {
  const unanswered = TOTAL - Object.keys(answered).length;
  document.getElementById('unansweredMsg').textContent = unanswered > 0
    ? `⚠️ Masih ada ${unanswered} soal yang belum dijawab.`
    : '✅ Semua soal sudah dijawab!';
  const modal = document.getElementById('submitModal');
  modal.style.display = 'flex';
}

// Prevent accidental page leave
window.addEventListener('beforeunload', (e) => {
  e.preventDefault(); e.returnValue = '';
});

document.getElementById('testForm').addEventListener('submit', () => {
  window.removeEventListener('beforeunload', ()=>{});
  clearInterval(timerInt);
});

startTimer();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>