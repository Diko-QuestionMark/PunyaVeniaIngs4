<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ── DELETE ── */
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM learning_videos WHERE id=?")->execute([$id]);
    flashMessage('success','Video berhasil dihapus.');
    redirect(SITE_URL.'/admin/pages/videos.php');
}

/* ── SAVE (ADD / EDIT) ── */
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $section     = $_POST['section'] ?? 'listening';
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $is_published= isset($_POST['is_published']) ? 1 : 0;

    if ($id) {
        $db->prepare("UPDATE learning_videos SET title=?,description=?,youtube_url=?,section=?,sort_order=?,is_published=? WHERE id=?")
           ->execute([$title,$description,$youtube_url,$section,$sort_order,$is_published,$id]);
        flashMessage('success','Video berhasil diperbarui.');
    } else {
        $db->prepare("INSERT INTO learning_videos (title,description,youtube_url,section,sort_order,is_published) VALUES (?,?,?,?,?,?)")
           ->execute([$title,$description,$youtube_url,$section,$sort_order,$is_published]);
        flashMessage('success','Video berhasil ditambahkan.');
    }
    redirect(SITE_URL.'/admin/pages/videos.php');
}

if ($action==='add') {
    $video = ['id'=>0,'title'=>'','description'=>'','youtube_url'=>'','section'=>'listening','sort_order'=>0,'is_published'=>1];
    $pageTitle = 'Tambah Video Belajar';
} elseif ($action==='edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM learning_videos WHERE id=?");
    $stmt->execute([$id]);
    $video = $stmt->fetch();
    if (!$video) { flashMessage('danger','Video tidak ditemukan.'); redirect(SITE_URL.'/admin/pages/videos.php'); }
    $pageTitle = 'Edit Video Belajar';
} else {
    $pageTitle = 'Manajemen Video Belajar';
    $search = trim($_GET['q'] ?? '');
    $section= $_GET['section'] ?? '';
    $where  = []; $params = [];
    if ($search) { $where[] = "(title LIKE ? OR description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    if ($section) { $where[] = "section=?"; $params[] = $section; }
    $whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
    $stmt = $db->prepare("SELECT * FROM learning_videos $whereSQL ORDER BY section, sort_order");
    $stmt->execute($params);
    $videos = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($action==='list'): ?>
<!-- ── LIST ── -->
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">📺 Daftar Video Belajar (<?= count($videos) ?>)</div>
    <div style="display:flex;gap:10px;align-items:center;">
      <form method="GET" style="display:flex;gap:8px;">
        <input type="hidden" name="action" value="list">
        <input type="text" name="q" class="form-control" placeholder="Cari video..." value="<?= sanitize($search) ?>" style="width:200px;padding:8px 12px;font-size:0.85rem;">
        <select name="section" class="admin-select" style="width:140px;padding:8px 12px;font-size:0.85rem;">
          <option value="">Semua Seksi</option>
          <option value="listening" <?= $section==='listening'?'selected':'' ?>>Listening</option>
          <option value="structure" <?= $section==='structure'?'selected':'' ?>>Structure</option>
          <option value="reading"   <?= $section==='reading'  ?'selected':'' ?>>Reading</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
      </form>
      <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Video</a>
    </div>
  </div>
  <table class="admin-table">
    <thead><tr>
      <th>#</th><th>Judul Video</th><th>Seksi</th><th>Link YouTube</th><th>Urutan</th><th>Status</th><th>Aksi</th>
    </tr></thead>
    <tbody>
    <?php if (empty($videos)): ?>
      <tr><td colspan="7" style="text-align:center;padding:40px;color:#94A3B8;">Belum ada video. <a href="?action=add" style="color:#2563EB;">Tambah sekarang</a></td></tr>
    <?php else: foreach($videos as $i=>$v): ?>
    <tr>
      <td style="color:#94A3B8;"><?= $i+1 ?></td>
      <td>
        <strong><?= sanitize($v['title']) ?></strong>
        <div style="font-size:0.76rem;color:#94A3B8;margin-top:2px;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= sanitize($v['description']) ?></div>
      </td>
      <td><span class="sec-badge sec-<?= $v['section'] ?>"><?= ucfirst($v['section']) ?></span></td>
      <td>
        <a href="<?= sanitize($v['youtube_url']) ?>" target="_blank" style="color:#2563EB;font-size:0.85rem;">Lihat Video</a>
      </td>
      <td style="text-align:center;"><?= $v['sort_order'] ?></td>
      <td><span class="status-badge <?= $v['is_published']?'status-published':'status-draft' ?>"><?= $v['is_published']?'Publik':'Draft' ?></span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <a href="?action=edit&id=<?= $v['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
          <a href="?action=delete&id=<?= $v['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus video ini?')"><i class="fas fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<!-- ── ADD / EDIT FORM ── -->
<div style="margin-bottom:16px;">
  <a href="<?= SITE_URL ?>/admin/pages/videos.php" style="color:#64748B;font-size:0.85rem;display:inline-flex;align-items:center;gap:6px;">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Video
  </a>
</div>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">
  <!-- Main -->
  <div style="display:flex;flex-direction:column;gap:20px;">
    <div class="admin-form-card">
      <div class="admin-form-header">
        <span style="font-size:1.2rem;">📺</span>
        <h3><?= $action==='add'?'Tambah Video Baru':'Edit Video' ?></h3>
      </div>
      <div class="admin-form-body">
        <div class="form-group">
          <label class="form-label">Judul Video <span style="color:#EF4444;">*</span></label>
          <input type="text" name="title" class="form-control" placeholder="Contoh: Strategi Listening Jitu" value="<?= sanitize($video['title']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Link YouTube <span style="color:#EF4444;">*</span></label>
          <input type="url" name="youtube_url" class="form-control" placeholder="Contoh: https://www.youtube.com/embed/xxxxxx" value="<?= sanitize($video['youtube_url']) ?>" required>
          <div style="font-size:0.78rem;color:#94A3B8;margin-top:6px;">Gunakan link embed YouTube. (Contoh: https://www.youtube.com/embed/ID_VIDEO)</div>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Singkat</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Penjelasan singkat mengenai video ini..."><?= sanitize($video['description']) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px;">
    <div class="admin-form-card">
      <div class="admin-form-header"><span>⚙️</span><h3>Pengaturan</h3></div>
      <div class="admin-form-body">
        <div class="form-group">
          <label class="form-label">Seksi <span style="color:#EF4444;">*</span></label>
          <select name="section" class="admin-select" required>
            <option value="listening" <?= $video['section']==='listening'?'selected':'' ?>>🎧 Listening</option>
            <option value="structure" <?= $video['section']==='structure'?'selected':'' ?>>📝 Structure</option>
            <option value="reading"   <?= $video['section']==='reading'?'selected':'' ?>>📖 Reading</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Urutan Tampil</label>
          <input type="number" name="sort_order" class="form-control" value="<?= $video['sort_order'] ?>" min="0" placeholder="0">
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="is_published" value="1" <?= $video['is_published']?'checked':'' ?> style="width:18px;height:18px;accent-color:#2563EB;">
            <span class="form-label" style="margin:0;">Publikasikan Video</span>
          </label>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">
      <i class="fas fa-save"></i> <?= $action==='add'?'Simpan Video':'Perbarui Video' ?>
    </button>
    <a href="<?= SITE_URL ?>/admin/pages/videos.php" class="btn btn-secondary btn-block">Batal</a>
  </div>
</div>
</form>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
