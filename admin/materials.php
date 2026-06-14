<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ── DELETE ── */
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM materials WHERE id=?")->execute([$id]);
    flashMessage('success','Materi berhasil dihapus.');
    redirect(SITE_URL.'/admin/pages/materials.php');
}

/* ── SAVE (ADD / EDIT) ── */
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title       = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $content     = $_POST['content'] ?? '';
    $summary     = trim($_POST['summary'] ?? '');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $is_published= isset($_POST['is_published']) ? 1 : 0;
    $slug        = generateSlug($title);

    if ($id) {
        // ensure slug unique
        $check = $db->prepare("SELECT id FROM materials WHERE slug=? AND id!=?");
        $check->execute([$slug, $id]);
        if ($check->fetch()) $slug .= '-'.$id;
        $db->prepare("UPDATE materials SET title=?,category_id=?,content=?,summary=?,slug=?,sort_order=?,is_published=?,updated_at=NOW() WHERE id=?")
           ->execute([$title,$category_id,$content,$summary,$slug,$sort_order,$is_published,$id]);
        flashMessage('success','Materi berhasil diperbarui.');
    } else {
        $check = $db->prepare("SELECT id FROM materials WHERE slug=?");
        $check->execute([$slug]);
        if ($check->fetch()) $slug .= '-'.time();
        $db->prepare("INSERT INTO materials (title,category_id,content,summary,slug,sort_order,is_published) VALUES (?,?,?,?,?,?,?)")
           ->execute([$title,$category_id,$content,$summary,$slug,$sort_order,$is_published]);
        flashMessage('success','Materi berhasil ditambahkan.');
    }
    redirect(SITE_URL.'/admin/pages/materials.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();

if ($action==='add') {
    $material = ['id'=>0,'title'=>'','category_id'=>0,'content'=>'','summary'=>'','sort_order'=>0,'is_published'=>1];
    $pageTitle = 'Tambah Materi';
} elseif ($action==='edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM materials WHERE id=?");
    $stmt->execute([$id]);
    $material = $stmt->fetch();
    if (!$material) { flashMessage('danger','Materi tidak ditemukan.'); redirect(SITE_URL.'/admin/pages/materials.php'); }
    $pageTitle = 'Edit Materi';
} else {
    $pageTitle = 'Manajemen Materi';
    $search = trim($_GET['q'] ?? '');
    $section= $_GET['section'] ?? '';
    $where  = []; $params = [];
    if ($search) { $where[] = "(m.title LIKE ? OR m.summary LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    if ($section) { $where[] = "c.section=?"; $params[] = $section; }
    $whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
    $stmt = $db->prepare("SELECT m.*,c.name as cat_name,c.section FROM materials m JOIN categories c ON m.category_id=c.id $whereSQL ORDER BY c.sort_order,m.sort_order");
    $stmt->execute($params);
    $materials = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($action==='list'): ?>
<!-- ── LIST ── -->
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">📚 Daftar Materi (<?= count($materials) ?>)</div>
    <div style="display:flex;gap:10px;align-items:center;">
      <form method="GET" style="display:flex;gap:8px;">
        <input type="hidden" name="action" value="list">
        <input type="text" name="q" class="form-control" placeholder="Cari materi..." value="<?= sanitize($search) ?>" style="width:200px;padding:8px 12px;font-size:0.85rem;">
        <select name="section" class="admin-select" style="width:140px;padding:8px 12px;font-size:0.85rem;">
          <option value="">Semua Seksi</option>
          <option value="listening" <?= $section==='listening'?'selected':'' ?>>Listening</option>
          <option value="structure" <?= $section==='structure'?'selected':'' ?>>Structure</option>
          <option value="reading"   <?= $section==='reading'  ?'selected':'' ?>>Reading</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
      </form>
      <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Materi</a>
    </div>
  </div>
  <table class="admin-table">
    <thead><tr>
      <th>#</th><th>Judul</th><th>Kategori</th><th>Seksi</th><th>Urutan</th><th>Status</th><th>Aksi</th>
    </tr></thead>
    <tbody>
    <?php if (empty($materials)): ?>
      <tr><td colspan="7" style="text-align:center;padding:40px;color:#94A3B8;">Belum ada materi. <a href="?action=add" style="color:#2563EB;">Tambah sekarang</a></td></tr>
    <?php else: foreach($materials as $i=>$m): ?>
    <tr>
      <td style="color:#94A3B8;"><?= $i+1 ?></td>
      <td>
        <strong><?= sanitize($m['title']) ?></strong>
        <div style="font-size:0.76rem;color:#94A3B8;margin-top:2px;"><?= sanitize($m['slug']) ?></div>
      </td>
      <td style="font-size:0.85rem;"><?= sanitize($m['cat_name']) ?></td>
      <td><span class="sec-badge sec-<?= $m['section'] ?>"><?= ucfirst($m['section']) ?></span></td>
      <td style="text-align:center;"><?= $m['sort_order'] ?></td>
      <td><span class="status-badge <?= $m['is_published']?'status-published':'status-draft' ?>"><?= $m['is_published']?'Publik':'Draft' ?></span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <a href="<?= SITE_URL ?>/pages/material.php?slug=<?= urlencode($m['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm" title="Preview"><i class="fas fa-eye"></i></a>
          <a href="?action=edit&id=<?= $m['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
          <a href="?action=delete&id=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus materi ini?')"><i class="fas fa-trash"></i></a>
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
  <a href="<?= SITE_URL ?>/admin/pages/materials.php" style="color:#64748B;font-size:0.85rem;display:inline-flex;align-items:center;gap:6px;">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Materi
  </a>
</div>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">
  <!-- Main -->
  <div style="display:flex;flex-direction:column;gap:20px;">
    <div class="admin-form-card">
      <div class="admin-form-header">
        <span style="font-size:1.2rem;">📝</span>
        <h3><?= $action==='add'?'Tambah Materi Baru':'Edit Materi' ?></h3>
      </div>
      <div class="admin-form-body">
        <div class="form-group">
          <label class="form-label">Judul Materi <span style="color:#EF4444;">*</span></label>
          <input type="text" name="title" class="form-control" placeholder="Contoh: Short Conversations - Level Dasar" value="<?= sanitize($material['title']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Ringkasan / Deskripsi Singkat</label>
          <textarea name="summary" class="form-control" rows="2" placeholder="Deskripsi singkat materi ini (ditampilkan di kartu materi)..."><?= sanitize($material['summary']) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Konten Materi <span style="color:#EF4444;">*</span></label>
          <div class="editor-toolbar">
            <button type="button" class="editor-btn" onclick="wrapTag('h2')"><b>H2</b></button>
            <button type="button" class="editor-btn" onclick="wrapTag('h3')">H3</button>
            <button type="button" class="editor-btn" onclick="wrapTag('strong')"><b>B</b></button>
            <button type="button" class="editor-btn" onclick="wrapTag('em')"><i>I</i></button>
            <button type="button" class="editor-btn" onclick="wrapTag('p')">P</button>
            <button type="button" class="editor-btn" onclick="insertList('ul')">UL</button>
            <button type="button" class="editor-btn" onclick="insertList('ol')">OL</button>
            <button type="button" class="editor-btn" onclick="insertTipBox()">💡 Tip</button>
            <button type="button" class="editor-btn" onclick="insertTable()">📊 Tabel</button>
          </div>
          <textarea name="content" id="contentEditor" class="editor-area form-control" rows="16" placeholder="Tulis konten materi dalam format HTML. Gunakan tombol di atas untuk memformat teks." required><?= htmlspecialchars($material['content']) ?></textarea>
          <div style="font-size:0.78rem;color:#94A3B8;margin-top:6px;">Konten mendukung HTML. Gunakan tag &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;strong&gt;, dll.</div>
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
          <label class="form-label">Kategori <span style="color:#EF4444;">*</span></label>
          <select name="category_id" class="admin-select" required>
            <option value="">-- Pilih Kategori --</option>
            <?php 
            $sections = ['listening'=>'🎧 Listening','structure'=>'📝 Structure','reading'=>'📖 Reading'];
            $grouped = [];
            foreach($categories as $c) $grouped[$c['section']][] = $c;
            foreach($sections as $sec=>$label): ?>
            <?php if (!empty($grouped[$sec])): ?>
            <optgroup label="<?= $label ?>">
              <?php foreach($grouped[$sec] as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $material['category_id']==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
              <?php endforeach; ?>
            </optgroup>
            <?php endif; endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Urutan Tampil</label>
          <input type="number" name="sort_order" class="form-control" value="<?= $material['sort_order'] ?>" min="0" placeholder="0">
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="is_published" value="1" <?= $material['is_published']?'checked':'' ?> style="width:18px;height:18px;accent-color:#2563EB;">
            <span class="form-label" style="margin:0;">Publikasikan Materi</span>
          </label>
          <div style="font-size:0.78rem;color:#94A3B8;margin-top:6px;">Jika tidak dicentang, materi disimpan sebagai Draft.</div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">
      <i class="fas fa-save"></i> <?= $action==='add'?'Simpan Materi':'Perbarui Materi' ?>
    </button>
    <a href="<?= SITE_URL ?>/admin/pages/materials.php" class="btn btn-secondary btn-block">Batal</a>
  </div>
</div>
</form>

<script>
function wrapTag(tag) {
  const ta = document.getElementById('contentEditor');
  const start = ta.selectionStart, end = ta.selectionEnd;
  const sel = ta.value.substring(start, end) || 'Teks di sini';
  const before = ta.value.substring(0, start);
  const after  = ta.value.substring(end);
  ta.value = before + `<${tag}>${sel}</${tag}>` + after;
  ta.focus();
}
function insertList(type) {
  const ta = document.getElementById('contentEditor');
  const pos = ta.selectionEnd;
  const snippet = `\n<${type}>\n  <li>Item pertama</li>\n  <li>Item kedua</li>\n  <li>Item ketiga</li>\n</${type}>\n`;
  ta.value = ta.value.substring(0,pos) + snippet + ta.value.substring(pos);
}
function insertTipBox() {
  const ta = document.getElementById('contentEditor');
  const pos = ta.selectionEnd;
  const snippet = `\n<div class="tip-box">\n  <h4>💡 Tips Penting</h4>\n  <p>Tulis tips atau catatan penting di sini.</p>\n</div>\n`;
  ta.value = ta.value.substring(0,pos) + snippet + ta.value.substring(pos);
}
function insertTable() {
  const ta = document.getElementById('contentEditor');
  const pos = ta.selectionEnd;
  const snippet = `\n<table class="content-table">\n  <tr><th>Kolom 1</th><th>Kolom 2</th></tr>\n  <tr><td>Data 1</td><td>Data 2</td></tr>\n  <tr><td>Data 3</td><td>Data 4</td></tr>\n</table>\n`;
  ta.value = ta.value.substring(0,pos) + snippet + ta.value.substring(pos);
}
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>