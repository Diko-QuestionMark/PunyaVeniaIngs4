<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ── DELETE ── */
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM tests WHERE id=?")->execute([$id]);
    flashMessage('success','Test berhasil dihapus.');
    redirect(SITE_URL.'/admin/pages/tests.php');
}

/* ── SAVE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $test_type      = $_POST['test_type'] ?? 'mini';
    $time_limit     = (int)($_POST['time_limit'] ?? 15);
    $is_published   = isset($_POST['is_published']) ? 1 : 0;
    $question_ids   = $_POST['question_ids'] ?? [];

    if ($id) {
        $db->prepare("UPDATE tests SET title=?,description=?,test_type=?,time_limit=?,total_questions=?,is_published=? WHERE id=?")
           ->execute([$title,$description,$test_type,$time_limit,count($question_ids),$is_published,$id]);
        $db->prepare("DELETE FROM test_questions WHERE test_id=?")->execute([$id]);
    } else {
        $db->prepare("INSERT INTO tests (title,description,test_type,time_limit,total_questions,is_published) VALUES (?,?,?,?,?,?)")
           ->execute([$title,$description,$test_type,$time_limit,count($question_ids),$is_published]);
        $id = $db->lastInsertId();
    }
    foreach($question_ids as $ord=>$qid) {
        $db->prepare("INSERT INTO test_questions (test_id,question_id,sort_order) VALUES (?,?,?)")->execute([$id,(int)$qid,$ord+1]);
    }
    flashMessage('success','Test berhasil disimpan dengan '.count($question_ids).' soal.');
    redirect(SITE_URL.'/admin/pages/tests.php');
}

/* ── LOAD FOR ADD/EDIT ── */
$allQuestions = $db->query("SELECT q.*,c.name as cat_name FROM questions q LEFT JOIN categories c ON q.category_id=c.id ORDER BY q.section,q.id")->fetchAll();

if ($action === 'add') {
    $test = ['id'=>0,'title'=>'','description'=>'','test_type'=>'mini','time_limit'=>15,'is_published'=>1];
    $selectedQIds = [];
    $pageTitle = 'Buat Test Baru';
} elseif ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM tests WHERE id=?"); $stmt->execute([$id]);
    $test = $stmt->fetch();
    if (!$test) { flashMessage('danger','Test tidak ditemukan.'); redirect(SITE_URL.'/admin/pages/tests.php'); }
    $selStmt = $db->prepare("SELECT question_id FROM test_questions WHERE test_id=? ORDER BY sort_order");
    $selStmt->execute([$id]);
    $selectedQIds = array_column($selStmt->fetchAll(), 'question_id');
    $pageTitle = 'Edit Test';
} else {
    $pageTitle = 'Manajemen Test';
    $tests = $db->query("SELECT t.*,(SELECT COUNT(*) FROM test_questions tq WHERE tq.test_id=t.id) as q_count FROM tests t ORDER BY t.test_type,t.id")->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($action==='list'): ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
  <div></div>
  <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Test Baru</a>
</div>

<?php foreach(['full'=>'🏆 Full Test (100 Soal)','mini'=>'⚡ Mini Test'] as $type=>$label): ?>
<div class="admin-table-card" style="margin-bottom:24px;">
  <div class="admin-table-header">
    <div class="admin-table-title"><?= $label ?></div>
  </div>
  <table class="admin-table">
    <thead><tr><th>#</th><th>Judul Test</th><th>Tipe</th><th>Durasi</th><th>Soal</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php $found=false; foreach($tests as $i=>$t): if($t['test_type']!==$type) continue; $found=true; ?>
    <tr>
      <td style="color:#94A3B8;"><?= $i+1 ?></td>
      <td>
        <strong><?= sanitize($t['title']) ?></strong>
        <div style="font-size:0.78rem;color:#94A3B8;margin-top:2px;"><?= sanitize(mb_strimwidth($t['description'],0,80,'...')) ?></div>
      </td>
      <td><span class="test-type-badge <?= $t['test_type']==='mini'?'badge-mini':'badge-full' ?>"><?= $t['test_type']==='mini'?'Mini':'Full' ?></span></td>
      <td><?= $t['time_limit'] ?> menit</td>
      <td><strong style="color:#2563EB;"><?= $t['q_count'] ?></strong> soal</td>
      <td><span class="status-badge <?= $t['is_published']?'status-published':'status-draft' ?>"><?= $t['is_published']?'Publik':'Draft' ?></span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <a href="<?= SITE_URL ?>/pages/take-test.php?id=<?= $t['id'] ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
          <a href="?action=edit&id=<?= $t['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
          <a href="?action=delete&id=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus test ini?')"><i class="fas fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; if(!$found): ?>
    <tr><td colspan="7" style="text-align:center;padding:30px;color:#94A3B8;">Belum ada <?= strtolower($label) ?>.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php endforeach; ?>

<?php else: ?>
<div style="margin-bottom:16px;">
  <a href="<?= SITE_URL ?>/admin/pages/tests.php" style="color:#64748B;font-size:0.85rem;display:inline-flex;align-items:center;gap:6px;">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Test
  </a>
</div>
<form method="POST" id="testForm">
<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

  <div style="display:flex;flex-direction:column;gap:20px;">
    <div class="admin-form-card">
      <div class="admin-form-header"><span>🏆</span><h3>Informasi Test</h3></div>
      <div class="admin-form-body">
        <div class="form-group">
          <label class="form-label">Judul Test <span style="color:#EF4444;">*</span></label>
          <input type="text" name="title" class="form-control" placeholder="Contoh: TOEFL Full Test 1" value="<?= sanitize($test['title']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat test ini..."><?= sanitize($test['description']) ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tipe Test</label>
            <select name="test_type" class="admin-select" onchange="updateTimeLimit(this.value)">
              <option value="mini" <?= $test['test_type']==='mini'?'selected':'' ?>>⚡ Mini Test</option>
              <option value="full" <?= $test['test_type']==='full'?'selected':'' ?>>🏆 Full Test</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Durasi (menit)</label>
            <input type="number" name="time_limit" id="timeLimit" class="form-control" value="<?= $test['time_limit'] ?>" min="5" max="200">
          </div>
        </div>
      </div>
    </div>

    <!-- Question Selector -->
    <div class="admin-form-card">
      <div class="admin-form-header">
        <span>❓</span>
        <h3>Pilih Soal</h3>
        <div style="margin-left:auto;display:flex;gap:8px;">
          <span id="selectedCount" style="background:#DBEAFE;color:#1D4ED8;padding:4px 12px;border-radius:100px;font-size:0.78rem;font-weight:700;">0 soal dipilih</span>
          <select id="filterSection" onchange="filterQuestions()" class="admin-select" style="padding:6px 10px;font-size:0.82rem;width:130px;">
            <option value="">Semua Seksi</option>
            <option value="listening">Listening</option>
            <option value="structure">Structure</option>
            <option value="reading">Reading</option>
          </select>
        </div>
      </div>
      <div style="padding:12px 20px;border-bottom:1px solid #E2E8F0;display:flex;gap:8px;">
        <button type="button" onclick="selectAll()" class="btn btn-secondary btn-sm">✅ Pilih Semua</button>
        <button type="button" onclick="clearAll()" class="btn btn-secondary btn-sm">❌ Hapus Semua</button>
        <input type="text" id="qSearch" oninput="filterQuestions()" placeholder="Cari soal..." class="form-control" style="padding:6px 12px;font-size:0.85rem;flex:1;">
      </div>
      <div style="max-height:420px;overflow-y:auto;" id="questionList">
        <?php foreach($allQuestions as $q): ?>
        <label class="q-item" data-section="<?= $q['section'] ?>" data-text="<?= strtolower(sanitize($q['question_text'])) ?>"
               style="display:flex;align-items:flex-start;gap:12px;padding:12px 20px;border-bottom:1px solid #F1F5F9;cursor:pointer;transition:background 0.15s;"
               onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
          <input type="checkbox" name="question_ids[]" value="<?= $q['id'] ?>" <?= in_array($q['id'],$selectedQIds)?'checked':'' ?> onchange="updateCount()" style="margin-top:3px;accent-color:#2563EB;width:16px;height:16px;flex-shrink:0;">
          <div>
            <div style="display:flex;gap:6px;margin-bottom:4px;">
              <span class="sec-badge sec-<?= $q['section'] ?>"><?= ucfirst($q['section']) ?></span>
              <span style="background:#F1F5F9;color:#64748B;padding:2px 8px;border-radius:100px;font-size:0.68rem;font-weight:600;">
                <?= ['easy'=>'Mudah','medium'=>'Sedang','hard'=>'Sulit'][$q['difficulty']] ?>
              </span>
              <?php if ($q['cat_name']): ?><span style="font-size:0.72rem;color:#94A3B8;"><?= sanitize($q['cat_name']) ?></span><?php endif; ?>
            </div>
            <div style="font-size:0.875rem;color:#334155;line-height:1.5;"><?= sanitize(mb_strimwidth($q['question_text'],0,120,'...')) ?></div>
          </div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px;">
    <div class="admin-form-card">
      <div class="admin-form-header"><span>⚙️</span><h3>Pengaturan</h3></div>
      <div class="admin-form-body">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
          <input type="checkbox" name="is_published" value="1" <?= $test['is_published']?'checked':'' ?> style="width:18px;height:18px;accent-color:#2563EB;">
          <span class="form-label" style="margin:0;">Publikasikan Test</span>
        </label>
        <div style="margin-top:16px;padding:14px;background:#F8FAFC;border-radius:10px;font-size:0.82rem;color:#64748B;">
          <strong style="display:block;margin-bottom:6px;color:#334155;">💡 Panduan Soal:</strong>
          <div>• Mini Test: 10–15 soal</div>
          <div>• Full Test: 100 soal</div>
          <div style="margin-top:6px;">Full Test = 50 Listening + 40 Structure + 10 Reading</div>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg">
      <i class="fas fa-save"></i> Simpan Test
    </button>
    <a href="<?= SITE_URL ?>/admin/pages/tests.php" class="btn btn-secondary btn-block">Batal</a>
  </div>
</div>
</form>

<script>
const preselected = <?= json_encode(array_map('intval',$selectedQIds)) ?>;
function updateCount() {
  const c = document.querySelectorAll('input[name="question_ids[]"]:checked').length;
  document.getElementById('selectedCount').textContent = c + ' soal dipilih';
}
function selectAll() {
  document.querySelectorAll('#questionList .q-item:not([style*="none"]) input').forEach(c=>c.checked=true);
  updateCount();
}
function clearAll() {
  document.querySelectorAll('input[name="question_ids[]"]').forEach(c=>c.checked=false);
  updateCount();
}
function filterQuestions() {
  const sec = document.getElementById('filterSection').value;
  const q = document.getElementById('qSearch').value.toLowerCase();
  document.querySelectorAll('#questionList .q-item').forEach(item => {
    const secMatch = !sec || item.dataset.section === sec;
    const textMatch = !q || item.dataset.text.includes(q);
    item.style.display = (secMatch && textMatch) ? '' : 'none';
  });
}
function updateTimeLimit(type) {
  document.getElementById('timeLimit').value = type==='full' ? 115 : 15;
}
updateCount();
</script>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>