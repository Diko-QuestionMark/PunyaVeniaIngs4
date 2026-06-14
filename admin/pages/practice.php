<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action==='delete' && $id) {
    $db->prepare("DELETE FROM practice_sets WHERE id=?")->execute([$id]);
    flashMessage('success','Set latihan berhasil dihapus.');
    redirect(SITE_URL.'/admin/pages/practice.php');
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $material_id  = (int)($_POST['material_id'] ?? 0);
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $time_limit   = (int)($_POST['time_limit'] ?? 15);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $question_ids = $_POST['question_ids'] ?? [];

    if ($id) {
        $db->prepare("UPDATE practice_sets SET material_id=?,title=?,description=?,time_limit=?,is_published=? WHERE id=?")
           ->execute([$material_id,$title,$description,$time_limit,$is_published,$id]);
        $db->prepare("DELETE FROM practice_questions WHERE practice_set_id=?")->execute([$id]);
    } else {
        $db->prepare("INSERT INTO practice_sets (material_id,title,description,time_limit,is_published) VALUES (?,?,?,?,?)")
           ->execute([$material_id,$title,$description,$time_limit,$is_published]);
        $id = $db->lastInsertId();
    }
    foreach($question_ids as $ord=>$qid) {
        $db->prepare("INSERT INTO practice_questions (practice_set_id,question_id,sort_order) VALUES (?,?,?)")->execute([$id,(int)$qid,$ord+1]);
    }
    flashMessage('success','Set latihan berhasil disimpan dengan '.count($question_ids).' soal.');
    redirect(SITE_URL.'/admin/pages/practice.php');
}

$materials = $db->query("SELECT m.*,c.section,c.name as cat_name FROM materials m JOIN categories c ON m.category_id=c.id WHERE m.is_published=1 ORDER BY c.sort_order,m.sort_order")->fetchAll();
$allQuestions = $db->query("SELECT q.*,c.name as cat_name FROM questions q LEFT JOIN categories c ON q.category_id=c.id ORDER BY q.section,q.id")->fetchAll();

if ($action==='add') {
    $ps = ['id'=>0,'material_id'=>0,'title'=>'','description'=>'','time_limit'=>15,'is_published'=>1];
    $selectedQIds = [];
    $pageTitle = 'Tambah Set Latihan';
} elseif ($action==='edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM practice_sets WHERE id=?"); $stmt->execute([$id]);
    $ps = $stmt->fetch();
    if (!$ps) { flashMessage('danger','Set latihan tidak ditemukan.'); redirect(SITE_URL.'/admin/pages/practice.php'); }
    $selStmt = $db->prepare("SELECT question_id FROM practice_questions WHERE practice_set_id=? ORDER BY sort_order");
    $selStmt->execute([$id]);
    $selectedQIds = array_column($selStmt->fetchAll(),'question_id');
    $pageTitle = 'Edit Set Latihan';
} else {
    $pageTitle = 'Manajemen Latihan Soal';
    $practiceSets = $db->query("
        SELECT ps.*,m.title as mat_title,c.section,
               (SELECT COUNT(*) FROM practice_questions pq WHERE pq.practice_set_id=ps.id) as q_count
        FROM practice_sets ps
        JOIN materials m ON ps.material_id=m.id
        JOIN categories c ON m.category_id=c.id
        ORDER BY c.sort_order,ps.id
    ")->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($action==='list'): ?>
<div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
  <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Set Latihan</a>
</div>
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">✏️ Daftar Set Latihan (<?= count($practiceSets) ?>)</div>
  </div>
  <table class="admin-table">
    <thead><tr><th>#</th><th>Judul Latihan</th><th>Materi Terkait</th><th>Seksi</th><th>Durasi</th><th>Soal</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (empty($practiceSets)): ?>
      <tr><td colspan="8" style="text-align:center;padding:40px;color:#94A3B8;">Belum ada set latihan.</td></tr>
    <?php else: foreach($practiceSets as $i=>$ps): ?>
    <tr>
      <td style="color:#94A3B8;"><?= $i+1 ?></td>
      <td><strong><?= sanitize($ps['title']) ?></strong></td>
      <td style="font-size:0.82rem;color:#64748B;"><?= sanitize($ps['mat_title']) ?></td>
      <td><span class="sec-badge sec-<?= $ps['section'] ?>"><?= ucfirst($ps['section']) ?></span></td>
      <td><?= $ps['time_limit'] ?> mnt</td>
      <td><strong style="color:#2563EB;"><?= $ps['q_count'] ?></strong></td>
      <td><span class="status-badge <?= $ps['is_published']?'status-published':'status-draft' ?>"><?= $ps['is_published']?'Publik':'Draft' ?></span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <a href="?action=edit&id=<?= $ps['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
          <a href="?action=delete&id=<?= $ps['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus set latihan ini?')"><i class="fas fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<div style="margin-bottom:16px;">
  <a href="<?= SITE_URL ?>/admin/pages/practice.php" style="color:#64748B;font-size:0.85rem;display:inline-flex;align-items:center;gap:6px;">
    <i class="fas fa-arrow-left"></i> Kembali
  </a>
</div>
<form method="POST">
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
  <div style="display:flex;flex-direction:column;gap:20px;">
    <div class="admin-form-card">
      <div class="admin-form-header"><span>✏️</span><h3>Detail Set Latihan</h3></div>
      <div class="admin-form-body">
        <div class="form-group">
          <label class="form-label">Terkait Materi <span style="color:#EF4444;">*</span></label>
          <select name="material_id" class="admin-select" required>
            <option value="">-- Pilih Materi --</option>
            <?php
            $grouped = [];
            foreach($materials as $m) $grouped[$m['section']][] = $m;
            $secLabels = ['listening'=>'🎧 Listening','structure'=>'📝 Structure','reading'=>'📖 Reading'];
            foreach($secLabels as $sec=>$slabel): if(empty($grouped[$sec])) continue; ?>
            <optgroup label="<?= $slabel ?>">
              <?php foreach($grouped[$sec] as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $ps['material_id']==$m['id']?'selected':'' ?>><?= sanitize($m['title']) ?></option>
              <?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Judul Set Latihan <span style="color:#EF4444;">*</span></label>
          <input type="text" name="title" class="form-control" placeholder="Contoh: Latihan Short Conversations #1" value="<?= sanitize($ps['title']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat set latihan..."><?= sanitize($ps['description']) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Durasi (menit)</label>
          <input type="number" name="time_limit" class="form-control" value="<?= $ps['time_limit'] ?>" min="5" max="120">
        </div>
      </div>
    </div>

    <div class="admin-form-card">
      <div class="admin-form-header">
        <span>❓</span><h3>Pilih Soal untuk Latihan</h3>
        <span id="selCount" style="margin-left:auto;background:#DBEAFE;color:#1D4ED8;padding:4px 12px;border-radius:100px;font-size:0.78rem;font-weight:700;">0 dipilih</span>
      </div>
      <div style="padding:10px 16px;border-bottom:1px solid #E2E8F0;display:flex;gap:8px;">
        <select id="fSec" onchange="filterQ()" class="admin-select" style="padding:6px 10px;font-size:0.82rem;width:130px;">
          <option value="">Semua</option>
          <option value="listening">Listening</option>
          <option value="structure">Structure</option>
          <option value="reading">Reading</option>
        </select>
        <input type="text" id="qSrch" oninput="filterQ()" placeholder="Cari soal..." class="form-control" style="padding:6px 12px;font-size:0.85rem;flex:1;">
      </div>
      <div style="max-height:380px;overflow-y:auto;" id="qList">
        <?php foreach($allQuestions as $q): ?>
        <label class="q-item" data-section="<?= $q['section'] ?>" data-text="<?= strtolower(htmlspecialchars($q['question_text'])) ?>"
               style="display:flex;align-items:flex-start;gap:10px;padding:10px 16px;border-bottom:1px solid #F1F5F9;cursor:pointer;"
               onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
          <input type="checkbox" name="question_ids[]" value="<?= $q['id'] ?>" <?= in_array($q['id'],$selectedQIds)?'checked':'' ?> onchange="updCount()" style="margin-top:3px;width:15px;height:15px;accent-color:#2563EB;flex-shrink:0;">
          <div>
            <span class="sec-badge sec-<?= $q['section'] ?>" style="font-size:0.68rem;"><?= ucfirst($q['section']) ?></span>
            <div style="font-size:0.85rem;color:#334155;margin-top:3px;"><?= sanitize(mb_strimwidth($q['question_text'],0,110,'...')) ?></div>
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
          <input type="checkbox" name="is_published" value="1" <?= $ps['is_published']?'checked':'' ?> style="width:18px;height:18px;accent-color:#2563EB;">
          <span class="form-label" style="margin:0;">Publikasikan</span>
        </label>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-save"></i> Simpan</button>
    <a href="<?= SITE_URL ?>/admin/pages/practice.php" class="btn btn-secondary btn-block">Batal</a>
  </div>
</div>
</form>
<script>
function updCount(){
  document.getElementById('selCount').textContent=document.querySelectorAll('input[name="question_ids[]"]:checked').length+' dipilih';
}
function filterQ(){
  const sec=document.getElementById('fSec').value;
  const q=document.getElementById('qSrch').value.toLowerCase();
  document.querySelectorAll('#qList .q-item').forEach(item=>{
    item.style.display=(!sec||item.dataset.section===sec)&&(!q||item.dataset.text.includes(q))?'':'none';
  });
}
updCount();
</script>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>