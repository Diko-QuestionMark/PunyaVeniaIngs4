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

// Fetch all audios for these questions
$qIds = array_column($questions, 'id');
$audios = [];
if (!empty($qIds)) {
    $inQuery = implode(',', array_fill(0, count($qIds), '?'));
    $aStmt = $db->prepare("SELECT * FROM question_audios WHERE question_id IN ($inQuery) ORDER BY sort_order ASC");
    $aStmt->execute($qIds);
    foreach ($aStmt->fetchAll() as $audio) {
        $audios[$audio['question_id']][] = $audio['audio_file'];
    }
}

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
.quiz-wrapper { max-width: 1100px; margin: 0 auto; padding: 30px 20px 80px; display: grid; grid-template-columns: 300px 1fr; gap: 30px; align-items: start; }
.quiz-nav-dots { display: grid; grid-template-columns: repeat(auto-fit, minmax(32px, 1fr)); gap: 6px; padding: 16px 20px; background: white; border-radius: 12px; border: 1px solid #E2E8F0; position: sticky; top: 90px; max-height: calc(100vh - 120px); overflow-y: auto; justify-items: center; }
.q-dot { width: 32px; height: 32px; border-radius: 8px; border: 2px solid #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all 0.2s; color: #64748B; background: white; }
.q-dot.answered { background: #DBEAFE; border-color: #2563EB; color: #1D4ED8; }
.q-dot.current  { background: #2563EB; border-color: #2563EB; color: white; }
.passage-box { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; max-height: 220px; overflow-y: auto; font-size: 0.9rem; line-height: 1.8; color: #334155; }
/* Resposive grid for mobile */
@media (max-width: 768px) {
  .quiz-wrapper { grid-template-columns: 1fr; }
  .quiz-nav-dots { position: static; max-height: 200px; }
}
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
  <div class="quiz-questions-container">
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
    <?php if ($q['section'] !== 'listening' || empty($audios[$q['id']])): ?>
    <div class="question-text"><?= nl2br(sanitize($q['question_text'])) ?></div>
    <?php endif; ?>

    <?php if (!empty($audios[$q['id']])): ?>
    <div style="margin-bottom:20px;">
      <div class="audio-playlist-container" data-audios='<?= json_encode($audios[$q['id']]) ?>'>
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
          <span style="background:#EFF6FF; color:#1E40AF; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;" class="audio-status-badge">
            Audio 1/<?= count($audios[$q['id']]) ?>
          </span>
          <span style="font-size:0.8rem; color:#64748B;" class="audio-status-text">
            Siap diputar
          </span>
        </div>
        <audio class="playlist-audio-player" style="display:none;">
          <source src="<?= UPLOAD_URL . $audios[$q['id']][0] ?>" type="audio/mpeg">
        </audio>
        <button type="button" class="btn btn-primary btn-play-audio" style="width:100%; border-radius:8px; padding:12px; font-weight:bold; margin-top:8px;">
          <i class="fas fa-play-circle" style="margin-right:6px; font-size:1.1rem;"></i> Putar Audio (Hanya 1 Kali)
        </button>
      </div>
    </div>
    <?php endif; ?>

    <div class="options-list">
      <?php 
        $opts = ['A','B','C','D'];
        shuffle($opts);
        $labels = ['A','B','C','D'];
        foreach($opts as $idx => $opt):
        $key = 'option_'.strtolower($opt);
        $display = $labels[$idx];
      ?>
      <label class="option-item" id="opt-<?= $i ?>-<?= $opt ?>" onclick="selectOption(<?= $i ?>,'<?= $q['id'] ?>','<?= $opt ?>')">
        <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt ?>" style="display:none;">
        <div class="option-key"><?= $display ?></div>
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

function pauseAllAudios() {
  document.querySelectorAll('audio').forEach(audio => {
    audio.pause();
  });
}

function goTo(idx) {
  if (idx < 0 || idx >= TOTAL) return;
  pauseAllAudios();
  document.getElementById('qcard-'+current).style.display = 'none';
  document.getElementById('dot-'+current).classList.remove('current');
  current = idx;
  document.getElementById('qcard-'+current).style.display = 'block';
  document.getElementById('dot-'+current).classList.add('current');
  window.scrollTo({top:0,behavior:'smooth'});
}

// Initialize playlists
document.querySelectorAll('.audio-playlist-container').forEach(container => {
  const player = container.querySelector('.playlist-audio-player');
  const badge = container.querySelector('.audio-status-badge');
  const text = container.querySelector('.audio-status-text');
  const playBtn = container.querySelector('.btn-play-audio');
  const playlist = JSON.parse(container.getAttribute('data-audios') || '[]');
  
  if (!playlist || playlist.length === 0) return;
  
  let currentIndex = 0;
  let hasFinished = false;
  const uploadUrl = '<?= UPLOAD_URL ?>';

  playBtn.addEventListener('click', () => {
    if (hasFinished) return;
    playBtn.disabled = true;
    playBtn.innerHTML = '<i class="fas fa-volume-up"></i> Sedang Memutar...';
    player.play().catch(err => {
      console.error(err);
      playBtn.disabled = false;
      playBtn.innerHTML = '<i class="fas fa-play-circle"></i> Gagal Memutar. Coba Lagi';
    });
  });
  
  player.addEventListener('play', () => {
    text.textContent = 'Memutar...';
  });
  
  player.addEventListener('pause', () => {
    if (player.currentTime < player.duration && !hasFinished) {
      text.textContent = 'Dijeda (Otomatis)';
      // Allow resume if paused by navigation
      playBtn.disabled = false;
      playBtn.innerHTML = '<i class="fas fa-play-circle"></i> Lanjutkan Audio';
    }
  });
  
  player.addEventListener('ended', () => {
    currentIndex++;
    if (currentIndex < playlist.length) {
      badge.textContent = `Audio ${currentIndex + 1}/${playlist.length}`;
      text.textContent = 'Memutar audio berikutnya...';
      player.src = uploadUrl + playlist[currentIndex];
      player.play().catch(err => {
        console.log("Auto-play blocked or error: ", err);
        text.textContent = 'Klik Lanjutkan untuk memutar audio berikutnya';
        playBtn.disabled = false;
        playBtn.innerHTML = '<i class="fas fa-play-circle"></i> Lanjutkan Audio';
      });
    } else {
      hasFinished = true;
      badge.textContent = `Selesai`;
      badge.style.background = '#D1FAE5';
      badge.style.color = '#065F46';
      text.textContent = 'Seluruh percakapan selesai diputar.';
      playBtn.disabled = true;
      playBtn.innerHTML = '<i class="fas fa-check-circle"></i> Selesai Diputar';
    }
  });
});

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