<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    flashMessage('success','Pengguna berhasil dihapus.');
    redirect(SITE_URL.'/admin/pages/users.php');
}

$search = trim($_GET['q'] ?? '');
$where  = $search ? "WHERE full_name LIKE ? OR username LIKE ? OR email LIKE ?" : "";
$params = $search ? ["%$search%","%$search%","%$search%"] : [];
$stmt   = $db->prepare("
    SELECT u.*,
      (SELECT COUNT(*) FROM user_test_results r WHERE r.user_id=u.id) as total_tests,
      (SELECT MAX(toefl_score) FROM user_test_results r WHERE r.user_id=u.id) as best_score
    FROM users u $where ORDER BY u.created_at DESC
");
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manajemen Pengguna';
include '../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
  <form method="GET" style="display:flex;gap:8px;">
    <input type="text" name="q" class="form-control" placeholder="Cari nama / username / email..." value="<?= sanitize($search) ?>" style="width:280px;padding:9px 14px;font-size:0.875rem;">
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Cari</button>
    <?php if($search): ?><a href="?" class="btn btn-secondary btn-sm">Reset</a><?php endif; ?>
  </form>
  <div style="font-size:0.875rem;color:#64748B;">Total: <strong><?= count($users) ?></strong> pengguna</div>
</div>

<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">👥 Daftar Pengguna</div>
  </div>
  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead><tr>
        <th>#</th><th style="min-width: 200px;">Nama</th><th style="min-width: 150px;">Username</th><th style="min-width: 200px;">Email</th><th>Total Test</th><th>Skor Terbaik</th><th style="min-width: 100px;">Bergabung</th><th>Aksi</th>
      </tr></thead>
      <tbody>
      <?php if(empty($users)): ?>
      <tr><td colspan="8" style="text-align:center;padding:40px;color:#94A3B8;">
        <?= $search ? 'Pengguna tidak ditemukan.' : 'Belum ada pengguna terdaftar.' ?>
      </td></tr>
      <?php else: foreach($users as $i=>$u): ?>
      <tr>
        <td style="color:#94A3B8;"><?= $i+1 ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#2563EB,#7C3AED);color:white;font-weight:700;font-size:0.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <?= strtoupper(substr($u['full_name']?:$u['username'],0,1)) ?>
            </div>
            <strong><?= sanitize($u['full_name'] ?: '-') ?></strong>
          </div>
        </td>
        <td style="color:#64748B; word-break: break-word;">@<?= sanitize($u['username']) ?></td>
        <td style="color:#64748B;font-size:0.85rem; word-break: break-all;"><?= sanitize($u['email']) ?></td>
        <td style="text-align:center;"><strong style="color:#2563EB;"><?= $u['total_tests'] ?></strong></td>
        <td style="text-align:center;">
          <?php if($u['best_score']): ?>
          <span style="font-weight:700;color:<?= $u['best_score']>=500?'#10B981':'#F59E0B' ?>;"><?= $u['best_score'] ?></span>
          <?php else: ?><span style="color:#94A3B8;">-</span><?php endif; ?>
        </td>
        <td style="color:#94A3B8;font-size:0.8rem;"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
        <td>
          <div style="display:flex;gap:6px;">
            <a href="<?= SITE_URL ?>/admin/pages/results.php?user_id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm" title="Lihat Hasil Test"><i class="fas fa-chart-bar"></i></a>
            <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pengguna <?= sanitize($u['username']) ?>? Semua data hasil testnya juga akan terhapus.')"><i class="fas fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>