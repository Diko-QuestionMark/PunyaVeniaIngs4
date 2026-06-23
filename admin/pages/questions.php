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

    if ($id) {
        $db->prepare("UPDATE questions SET section=?,category_id=?,material_id=?,question_text=?,passage_text=?,option_a=?,option_b=?,option_c=?,option_d=?,correct_answer=?,explanation=?,difficulty=? WHERE id=?")
           ->execute([$section,$category_id,$material_id,$question_text,$passage_text,$option_a,$option_b,$option_c,$option_d,$correct_answer,$explanation,$difficulty,$id]);
        
        // Handle deletion of audio files
        if (isset($_POST['delete_audios']) && is_array($_POST['delete_audios'])) {
            foreach ($_POST['delete_audios'] as $audioId) {
                $audioId = (int)$audioId;
                $stmtFile = $db->prepare("SELECT audio_file FROM question_audios WHERE id=?");
                $stmtFile->execute([$audioId]);
                $fileName = $stmtFile->fetchColumn();
                if ($fileName && file_exists(UPLOAD_PATH . $fileName)) {
                    @unlink(UPLOAD_PATH . $fileName);
                }
                $db->prepare("DELETE FROM question_audios WHERE id=?")->execute([$audioId]);
            }
        }

        // Handle updating sort order of existing files
        if (isset($_POST['existing_sort']) && is_array($_POST['existing_sort'])) {
            foreach ($_POST['existing_sort'] as $audioId => $sortVal) {
                $db->prepare("UPDATE question_audios SET sort_order=? WHERE id=?")
                   ->execute([(int)$sortVal, (int)$audioId]);
            }
        }

        flashMessage('success','Soal berhasil diperbarui.');
    } else {
        $db->prepare("INSERT INTO questions (section,category_id,material_id,question_text,passage_text,option_a,option_b,option_c,option_d,correct_answer,explanation,difficulty) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([$section,$category_id,$material_id,$question_text,$passage_text,$option_a,$option_b,$option_c,$option_d,$correct_answer,$explanation,$difficulty]);
        $id = $db->lastInsertId();
        flashMessage('success','Soal berhasil ditambahkan ke bank soal.');
    }

    // Handle uploading new audio files
    if (isset($_FILES['audio_files']) && is_array($_FILES['audio_files']['name'])) {
        $uploadedCount = count($_FILES['audio_files']['name']);
        for ($i = 0; $i < $uploadedCount; $i++) {
            if ($_FILES['audio_files']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['audio_files']['name'][$i], PATHINFO_EXTENSION);
                $newname = 'audio_' . time() . '_' . rand(1000,9999) . '_' . $i . '.' . $ext;
                if (!is_dir(UPLOAD_PATH . 'audio')) {
                    mkdir(UPLOAD_PATH . 'audio', 0777, true);
                }
                if (move_uploaded_file($_FILES['audio_files']['tmp_name'][$i], UPLOAD_PATH . 'audio/' . $newname)) {
                    $audioPath = 'audio/' . $newname;
                    $maxSort = $db->prepare("SELECT MAX(sort_order) FROM question_audios WHERE question_id=?");
                    $maxSort->execute([$id]);
                    $sortOrder = (int)$maxSort->fetchColumn() + 1;
                    $db->prepare("INSERT INTO question_audios (question_id, audio_file, sort_order) VALUES (?, ?, ?)")
                       ->execute([$id, $audioPath, $sortOrder]);
                }
            }
        }
    }

    redirect(SITE_URL.'/admin/pages/questions.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$materials  = $db->query("SELECT id,title,category_id FROM materials WHERE is_published=1 ORDER BY title")->fetchAll();

if ($action === 'add') {
    $q = ['id'=>0,'section'=>'structure','category_id'=>null,'material_id'=>null,'question_text'=>'','passage_text'=>'','option_a'=>'','option_b'=>'','option_c'=>'','option_d'=>'','correct_answer'=>'A','explanation'=>'','difficulty'=>'medium'];
    $pageTitle = 'Tambah Soal Baru';
    $audios = [];
} elseif ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM questions WHERE id=?"); $stmt->execute([$id]);
    $q = $stmt->fetch();
    if (!$q) { flashMessage('danger','Soal tidak ditemukan.'); redirect(SITE_URL.'/admin/pages/questions.php'); }
    $pageTitle = 'Edit Soal';
    $stmtAudios = $db->prepare("SELECT * FROM question_audios WHERE question_id=? ORDER BY sort_order ASC");
    $stmtAudios->execute([$id]);
    $audios = $stmtAudios->fetchAll();
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

        <div class="form-group" id="audioUploadGroup" style="<?= $q['section'] !== 'listening' && empty($audios) ? 'display:none;' : '' ?>">
          <label class="form-label" style="font-weight: 600;">File Audio (Untuk soal Listening)</label>
          
          <?php if(!empty($audios)): ?>
            <div style="margin-bottom:16px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px;">
              <label class="form-label" style="font-size:0.8rem;color:#64748B;margin-bottom:8px;">Audio Terunggah:</label>
              <div style="display:flex; flex-direction:column; gap:8px;">
                <?php foreach($audios as $audio): ?>
                  <div style="display:flex; align-items:center; gap:12px; background:white; padding:8px; border:1px solid #E2E8F0; border-radius:6px;">
                    <div style="font-size:0.75rem; color:#64748B; min-width:85px; display:flex; align-items:center; gap:4px;">
                      Urutan: <input type="number" name="existing_sort[<?= $audio['id'] ?>]" value="<?= $audio['sort_order'] ?>" style="width:45px; padding:2px 4px; border:1px solid #CBD5E1; border-radius:4px; font-size:0.75rem; text-align:center;">
                    </div>
                    <div style="flex:1;">
                      <audio controls style="height:28px; width:100%;"><source src="<?= UPLOAD_URL . $audio['audio_file'] ?>"></audio>
                    </div>
                    <div>
                      <label style="font-size:0.75rem; color:#EF4444; cursor:pointer; display:flex; align-items:center; gap:4px; margin:0;">
                        <input type="checkbox" name="delete_audios[]" value="<?= $audio['id'] ?>"> Hapus
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          
          <label class="form-label" style="font-size:0.8rem;color:#64748B;">Upload Audio Baru (Bisa pilih banyak):</label>
          <input type="file" name="audio_files[]" class="form-control" accept="audio/*" multiple>
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
    <?php if(empty($audios)): ?>
    audioGroup.style.display = 'none';
    <?php endif; ?>
  }
});
</script>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>