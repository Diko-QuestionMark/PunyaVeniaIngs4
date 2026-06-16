<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
    flashMessage('success','Kategori berhasil dihapus.');
    redirect(SITE_URL.'/admin/pages/categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $section     = $_POST['section'] ?? 'listening';
    $description = trim($_POST['description'] ?? '');
    $icon        = trim($_POST['icon'] ?? '');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $slug        = generateSlug($name);

    if ($id) {
        $check = $db->prepare("SELECT id FROM categories WHERE slug=? AND id!=?");
        $check->execute([$slug,$id]);
        if ($check->fetch()) $slug .= '-'.$id;
        $db->prepare("UPDATE categories SET name=?,section=?,description=?,icon=?,sort_order=?,slug=? WHERE id=?")
           ->execute([$name,$section,$description,$icon,$sort_order,$slug,$id]);
        flashMessage('success','Kategori berhasil diperbarui.');
    } else {
        $check = $db->prepare("SELECT id FROM categories WHERE slug=?");
        $check->execute([$slug]);
        if ($check->fetch()) $slug .= '-'.time();
        $db->prepare("INSERT INTO categories (name,section,description,icon,sort_order,slug) VALUES (?,?,?,?,?,?)")
           ->execute([$name,$section,$description,$icon,$sort_order,$slug]);
        flashMessage('success','Kategori berhasil ditambahkan.');
    }
    redirect(SITE_URL.'/admin/pages/categories.php');
}

if ($action === 'add') {
    $cat = ['id'=>0,'name'=>'','section'=>'listening','description'=>'','icon'=>'','sort_order'=>0];
    $pageTitle = 'Tambah Kategori';
} elseif ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id=?"); $stmt->execute([$id]);
    $cat = $stmt->fetch();
    if (!$cat) { flashMessage('danger','Kategori tidak ditemukan.'); redirect(SITE_URL.'/admin/pages/categories.php'); }
    $pageTitle = 'Edit Kategori';
} else {
    $pageTitle = 'Manajemen Kategori';
    $categories = $db->query("SELECT c.*,(SELECT COUNT(*) FROM materials m WHERE m.category_id=c.id) as mat_count,(SELECT COUNT(*) FROM questions q WHERE q.category_id=c.id) as q_count FROM categories c ORDER BY c.sort_order")->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($action === 'list'): ?>
<div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
  <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Kategori</a>
</div>
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">🏷️ Daftar Kategori (<?= count($categories) ?>)</div>
  </div>
  <table class="admin-table">
    <thead><tr><th>#</th><th>Nama Kategori</th><th>Seksi</th><th>Deskripsi</th><th>Materi</th><th>Soal</th><th>Urutan</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if(empty($categories)): ?>
    <tr><td colspan="8" style="text-align:center;padding:40px;color:#94A3B8;">Belum ada kategori.</td></tr>
    <?php else: foreach($categories as $i=>$c): ?>
    <tr>
      <td style="color:#94A3B8;"><?= $i+1 ?></td>
      <td><strong><?= sanitize($c['name']) ?></strong><div style="font-size:0.75rem;color:#94A3B8;margin-top:2px;"><?= sanitize($c['slug']) ?></div></td>
      <td><span class="sec-badge sec-<?= $c['section'] ?>"><?= ucfirst($c['section']) ?></span></td>
      <td style="font-size:0.82rem;color:#64748B;max-width:200px;"><?= sanitize(mb_strimwidth($c['description']??'',0,60,'...')) ?></td>
      <td style="text-align:center;"><strong style="color:#2563EB;"><?= $c['mat_count'] ?></strong></td>
      <td style="text-align:center;"><strong style="color:#7C3AED;"><?= $c['q_count'] ?></strong></td>
      <td style="text-align:center;"><?= $c['sort_order'] ?></td>
      <td>
        <div style="display:flex;gap:6px;">
          <a href="?action=edit&id=<?= $c['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
          <a href="?action=delete&id=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori ini? Materi & soal yang terkait tidak akan terhapus.')"><i class="fas fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<div style="margin-bottom:16px;">
  <a href="<?= SITE_URL ?>/admin/pages/categories.php" style="color:#64748B;font-size:0.85rem;display:inline-flex;align-items:center;gap:6px;"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>
<div class="admin-form-card" style="max-width:600px;">
  <div class="admin-form-header"><span>🏷️</span><h3><?= $action==='add'?'Tambah Kategori':'Edit Kategori' ?></h3></div>
  <div class="admin-form-body">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Nama Kategori <span style="color:#EF4444;">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="Contoh: Short Conversations" value="<?= sanitize($cat['name']) ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Seksi TOEFL</label>
          <select name="section" class="admin-select">
            <option value="listening" <?= $cat['section']==='listening'?'selected':'' ?>>🎧 Listening</option>
            <option value="structure" <?= $cat['section']==='structure'?'selected':'' ?>>📝 Structure</option>
            <option value="reading"   <?= $cat['section']==='reading'  ?'selected':'' ?>>📖 Reading</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Urutan Tampil</label>
          <input type="number" name="sort_order" class="form-control" value="<?= $cat['sort_order'] ?>" min="0">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Icon (emoji)</label>
        <input type="text" name="icon" class="form-control" placeholder="Contoh: 🎧" value="<?= sanitize($cat['icon']??'') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat kategori ini..."><?= sanitize($cat['description']??'') ?></textarea>
      </div>
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="<?= SITE_URL ?>/admin/pages/categories.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>