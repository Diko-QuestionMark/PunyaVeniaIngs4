<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ── DELETE ── */
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM questions WHERE id=?")->execute([$id]);
    flashMessage('success','Soal berhasil dihapus.');
    redirect(SITE_URL.'/admin/pages/questions.php');
}

/* ── SAVE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section        = $_POST['section'] ?? 'structure';
    $category_id    = (int)($_POST['category_id'] ?? 0) ?: null;
    $material_id    = (int)($_POST['material_id'] ?? 0) ?: null;
    $question_text  = trim($_POST['question_text'] ?? '');
    $passage_text   = trim($_POST['passage_text'] ?? '') ?: null;
    $option_a       = trim($_POST['option_a'] ?? '');
    $option_b       = trim($_POST['option_b'] ?? '');
    $option_c       = trim($_POST['option_c'] ?? '');
    $option_d       = trim($_POST['option_d'] ?? '');
    $correct_answer = $_POST['correct_answer'] ?? 'A';
    $explanation    = trim($_POST['explanation'] ?? '') ?: null;
    $difficulty     = $_POST['difficulty'] ?? 'medium';

    $audio_file = null;
    if ($id) {
        $stmtAudio = $db->prepare("SELECT audio_file FROM questions WHERE id=?");
        $stmtAudio->execute([$id]);
        $existing = $stmtAudio->fetch();
        $audio_file = $existing['audio_file'] ?? null;
    }

    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
        $newname = 'audio_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        if (!is_dir(UPLOAD_PATH . 'audio')) mkdir(UPLOAD_PATH . 'audio', 0777, true);
        move_uploaded_file($_FILES['audio_file']['tmp_name'], UPLOAD_PATH . 'audio/' . $newname);
        $audio_file = 'audio/' . $newname;
    }

    if ($id) {
        $db->prepare("UPDATE questions SET section=?,category_id=?,material_id=?,question_text=?,passage_text=?,option_a=?,option_b=?,option_c=?,option_d=?,correct_answer=?,explanation=?,difficulty=?,audio_file=? WHERE id=?")
           ->execute([$section,$category_id,$material_id,$question_text,$passage_text,$option_a,$option_b,$option_c,$option_d,$correct_answer,$explanation,$difficulty,$audio_file,$id]);
        flashMessage('success','Soal berhasil diperbarui.');
    } else {
        $db->prepare("INSERT INTO questions (section,category_id,material_id,question_text,passage_text,option_a,option_b,option_c,option_d,correct_answer,explanation,difficulty,audio_file) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([$section,$category_id,$material_id,$question_text,$passage_text,$option_a,$option_b,$option_c,$option_d,$correct_answer,$explanation,$difficulty,$audio_file]);
        flashMessage('success','Soal berhasil ditambahkan ke bank soal.');
    }
    redirect(SITE_URL.'/admin/pages/questions.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$materials  = $db->query("SELECT id,title,category_id FROM materials WHERE is_published=1 ORDER BY title")->fetchAll();

if ($action === 'add') {
    $q = ['id'=>0,'section'=>'structure','category_id'=>null,'material_id'=>null,'question_text'=>'','passage_text'=>'','option_a'=>'','option_b'=>'','option_c'=>'','option_d'=>'','correct_answer'=>'A','explanation'=>'','difficulty'=>'medium'];
    $pageTitle = 'Tambah Soal Baru';
} elseif ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM questions WHERE id=?"); $stmt->execute([$id]);
    $q = $stmt->fetch();
    if (!$q) { flashMessage('danger','Soal tidak ditemukan.'); redirect(SITE_URL.'/admin/pages/questions.php'); }
    $pageTitle = 'Edit Soal';
} else {
    $pageTitle = 'Bank Soal';
    $section = $_GET['section'] ?? '';
    $diff    = $_GET['diff'] ?? '';
    $search  = trim($_GET['q'] ?? '');
    $where   = []; $params = [];
    if ($section) { $where[] = "q.section=?"; $params[] = $section; }
    if ($diff)    { $where[] = "q.difficulty=?"; $params[] = $diff; }
    if ($search)  { $where[] = "q.question_text LIKE ?"; $params[] = "%$search%"; }
    $whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
    $stmt = $db->prepare("SELECT q.*,c.name as cat_name FROM questions q LEFT JOIN categories c ON q.category_id=c.id $whereSQL ORDER BY q.id DESC");
    $stmt->execute($params);
    $questions = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($action==='list'): ?>
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">❓ Bank Soal (<?= count($questions) ?>)</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <form method="GET" style="display:flex;gap:8px;">
        <input type="text" name="q" class="form-control" placeholder="Cari soal..." value="<?= sanitize($search) ?>" style="width:180px;padding:8px 12px;font-size:0.85rem;">
        <select name="section" class="admin-select" style="width:130px;padding:8px 12px;font-size:0.85rem;">
          <option value="">Semua Seksi</option>
          <option value="listening" <?= $section==='listening'?'selected':'' ?>>Listening</option>
          <option value="structure" <?= $section==='structure'?'selected':'' ?>>Structure</option>
          <option value="reading"   <?= $section==='reading'  ?'selected':'' ?>>Reading</option>
        </select>
        <select name="diff" class="admin-select" style="width:130px;padding:8px 12px;font-size:0.85rem;">
          <option value="">Semua Level</option>
          <option value="easy"   <?= $diff==='easy'  ?'selected':'' ?>>Mudah</option>
          <option value="medium" <?= $diff==='medium'?'selected':'' ?>>Sedang</option>
          <option value="hard"   <?= $diff==='hard'  ?'selected':'' ?>>Sulit</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
      </form>
      <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Soal</a>
    </div>
  </div>
  <table class="admin-table">
    <thead><tr>
      <th>#</th><th style="min-width:300px;">Pertanyaan</th><th>Seksi</th><th>Kategori</th><th>Jawaban</th><th>Level</th><th>Aksi</th>
    </tr></thead>
    <tbody>
    <?php if (empty($questions)): ?>
      <tr><td colspan="7" style="text-align:center;padding:40px;color:#94A3B8;">Belum ada soal. <a href="?action=add" style="color:#2563EB;">Tambah sekarang</a></td></tr>
    <?php else: foreach($questions as $i=>$q): ?>
    <tr>
      <td style="color:#94A3B8;"><?= $i+1 ?></td>
      <td>
        <div style="font-size:0.875rem;line-height:1.5;max-width:360px;">
          <?= sanitize(mb_strimwidth($q['question_text'],0,100,'...')) ?>
        </div>
      </td>
      <td><span class="sec-badge sec-<?= $q['section'] ?>"><?= ucfirst($q['section']) ?></span></td>
      <td style="font-size:0.82rem;color:#64748B;"><?= sanitize($q['cat_name'] ?? '-') ?></td>
      <td>
        <span style="width:28px;height:28px;border-radius:50%;background:#D1FAE5;color:#065F46;font-weight:700;display:inline-flex;align-items:center;justify-content:center;font-size:0.82rem;">
          <?= $q['correct_answer'] ?>
        </span>
      </td>
      <td>
        <?php $dColors=['easy'=>'#D1FAE5:#065F46','medium'=>'#FEF3C7:#92400E','hard'=>'#FEE2E2:#991B1B'];
              $dc = explode(':',$dColors[$q['difficulty']]);
              $dLabel=['easy'=>'Mudah','medium'=>'Sedang','hard'=>'Sulit'][$q['difficulty']];
        ?>
        <span style="background:<?= $dc[0] ?>;color:<?= $dc[1] ?>;padding:3px 10px;border-radius:100px;font-size:0.72rem;font-weight:700;"><?= $dLabel ?></span>
      </td>
      <td>
        <div style="display:flex;gap:6px;">
          <a href="?action=edit&id=<?= $q['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
          <a href="?action=delete&id=<?= $q['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus soal ini?')"><i class="fas fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<!-- FORM ADD/EDIT -->
<div style="margin-bottom:16px;">
  <a href="<?= SITE_URL ?>/admin/pages/questions.php" style="color:#64748B;font-size:0.85rem;display:inline-flex;align-items:center;gap:6px;">
    <i class="fas fa-arrow-left"></i> Kembali ke Bank Soal
  </a>
</div>
<form method="POST" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
  <div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Question -->
    <div class="admin-form-card">
      <div class="admin-form-header"><span>❓</span><h3>Detail Soal</h3></div>
      <div class="admin-form-body">
        <div class="form-group">
          <label class="form-label">Teks Passage / Konteks (Opsional - untuk soal Reading)</label>
          <textarea name="passage_text" class="form-control" rows="4" placeholder="Tuliskan passage atau konteks bacaan di sini jika soal membutuhkan teks bacaan..."><?= sanitize($q['passage_text']) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Teks Pertanyaan <span style="color:#EF4444;">*</span></label>
          <textarea name="question_text" class="form-control" rows="4" placeholder="Tulis pertanyaan dengan jelas dan lengkap..." required><?= sanitize($q['question_text']) ?></textarea>
        </div>

        <div class="form-group" id="audioUploadGroup" style="<?= $q['section'] !== 'listening' && empty($q['audio_file']) ? 'display:none;' : '' ?>">
          <label class="form-label">File Audio (Untuk soal Listening)</label>
          <?php if(!empty($q['audio_file'])): ?>
            <div style="margin-bottom:8px;">
              <audio controls style="height:36px;width:100%;max-width:300px;"><source src="<?= UPLOAD_URL . $q['audio_file'] ?>"></audio>
            </div>
          <?php endif; ?>
          <input type="file" name="audio_file" class="form-control" accept="audio/*">
        </div>

        <label class="form-label" style="margin-bottom:12px;">Pilihan Jawaban <span style="color:#EF4444;">*</span></label>
        <div class="options-builder">
          <?php foreach(['A','B','C','D'] as $opt):
            $key = 'option_'.strtolower($opt);
            $isCorrect = $q['correct_answer'] === $opt;
          ?>
          <div class="option-row <?= $isCorrect?'correct':'' ?>" id="row-<?= $opt ?>">
            <div class="option-label"><?= $opt ?></div>
            <input type="text" name="<?= $key ?>" class="form-control" placeholder="Pilihan <?= $opt ?>..." value="<?= sanitize($q[$key] ?? '') ?>" required>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;white-space:nowrap;">
              <input type="radio" name="correct_answer" value="<?= $opt ?>" <?= $isCorrect?'checked':'' ?> onchange="highlightCorrect()">
              <span style="font-size:0.8rem;font-weight:600;color:#10B981;">Benar</span>
            </label>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="form-group" style="margin-top:20px;">
          <label class="form-label">Penjelasan Jawaban</label>
          <textarea name="explanation" class="form-control" rows="3" placeholder="Jelaskan mengapa jawaban ini benar. Akan ditampilkan setelah siswa menjawab..."><?= sanitize($q['explanation']) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar Settings -->
  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px;">
    <div class="admin-form-card">
      <div class="admin-form-header"><span>⚙️</span><h3>Pengaturan Soal</h3></div>
      <div class="admin-form-body">
        <div class="form-group">
          <label class="form-label">Seksi TOEFL <span style="color:#EF4444;">*</span></label>
          <select name="section" class="admin-select" id="sectionSel" onchange="filterMaterials(this.value)">
            <option value="listening" <?= ($q['section']==='listening')?'selected':'' ?>>🎧 Listening</option>
            <option value="structure" <?= ($q['section']==='structure')?'selected':'' ?>>📝 Structure</option>
            <option value="reading"   <?= ($q['section']==='reading')  ?'selected':'' ?>>📖 Reading</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <select name="category_id" class="admin-select">
            <option value="">-- Pilih Kategori --</option>
            <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>" data-section="<?= $c['section'] ?>" <?= $q['category_id']==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Terkait Materi (Opsional)</label>
          <select name="material_id" class="admin-select">
            <option value="">-- Tidak Terkait Materi --</option>
            <?php foreach($materials as $m): ?>
            <option value="<?= $m['id'] ?>" <?= $q['material_id']==$m['id']?'selected':'' ?>><?= sanitize($m['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Tingkat Kesulitan</label>
          <select name="difficulty" class="admin-select">
            <option value="easy"   <?= $q['difficulty']==='easy'  ?'selected':'' ?>>😊 Mudah</option>
            <option value="medium" <?= $q['difficulty']==='medium'?'selected':'' ?>>😐 Sedang</option>
            <option value="hard"   <?= $q['difficulty']==='hard'  ?'selected':'' ?>>😤 Sulit</option>
          </select>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg">
      <i class="fas fa-save"></i> <?= $id?'Perbarui Soal':'Simpan Soal' ?>
    </button>
    <a href="<?= SITE_URL ?>/admin/pages/questions.php" class="btn btn-secondary btn-block">Batal</a>
  </div>
</div>
</form>

<script>
function highlightCorrect() {
  document.querySelectorAll('.option-row').forEach(r => r.classList.remove('correct'));
  const checked = document.querySelector('input[name="correct_answer"]:checked');
  if (checked) document.getElementById('row-'+checked.value)?.classList.add('correct');
}
highlightCorrect();

// Show/hide audio upload based on section
document.getElementById('sectionSel')?.addEventListener('change', function(e) {
  const audioGroup = document.getElementById('audioUploadGroup');
  if(e.target.value === 'listening') {
    audioGroup.style.display = 'block';
  } else {
    // Only hide if there's no existing audio file, otherwise they might get confused why it disappeared
    <?php if(empty($q['audio_file'])): ?>
    audioGroup.style.display = 'none';
    <?php endif; ?>
  }
});
</script>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>