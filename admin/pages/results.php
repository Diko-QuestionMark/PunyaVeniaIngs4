<?php
require_once '../../includes/config.php';
requireAdminLogin();
$db = getDB();

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$testId = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;

$where  = []; $params = [];
if ($userId) { $where[] = "r.user_id=?"; $params[] = $userId; }
if ($testId) { $where[] = "r.test_id=?"; $params[] = $testId; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

$results = $db->prepare("
    SELECT r.*,u.full_name,u.username,u.email,t.title as test_title,t.test_type
    FROM user_test_results r
    JOIN users u ON r.user_id=u.id
    JOIN tests t ON r.test_id=t.id
    $whereSQL ORDER BY r.completed_at DESC LIMIT 100
");
$results->execute($params); $results = $results->fetchAll();

// Stats
$avgScore  = $db->query("SELECT AVG(toefl_score) FROM user_test_results")->fetchColumn();
$maxScore  = $db->query("SELECT MAX(toefl_score) FROM user_test_results")->fetchColumn();
$totalAttempts = $db->query("SELECT COUNT(*) FROM user_test_results")->fetchColumn();
$above500  = $db->query("SELECT COUNT(*) FROM user_test_results WHERE toefl_score>=500")->fetchColumn();

$users = $db->query("SELECT id,full_name,username FROM users ORDER BY full_name")->fetchAll();
$tests = $db->query("SELECT id,title FROM tests WHERE is_published=1 ORDER BY test_type,title")->fetchAll();

$filterUser = $userId ? $db->prepare("SELECT full_name,username FROM users WHERE id=?") : null;
if ($filterUser) { $filterUser->execute([$userId]); $filterUser = $filterUser->fetch(); }

$pageTitle = 'Hasil & Analitik Test';
include '../includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-icon" style="background:#DBEAFE;">📊</div>
    <div class="stat-info">
      <div class="stat-label">Total Percobaan</div>
      <div class="stat-value"><?= number_format($totalAttempts) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#D1FAE5;">⭐</div>
    <div class="stat-info">
      <div class="stat-label">Rata-rata Skor</div>
      <div class="stat-value"><?= round($avgScore ?? 0) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#FEF3C7;">🏆</div>
    <div class="stat-info">
      <div class="stat-label">Skor Tertinggi</div>
      <div class="stat-value"><?= $maxScore ?? '-' ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#EDE9FE;">✅</div>
    <div class="stat-info">
      <div class="stat-label">Skor ≥ 500</div>
      <div class="stat-value"><?= $above500 ?></div>
      <div class="stat-change"><?= $totalAttempts>0 ? round($above500/$totalAttempts*100).'%' : '0%' ?> dari total</div>
    </div>
  </div>
</div>

<!-- Filter -->
<div class="admin-table-card" style="margin-bottom:20px;">
  <div class="admin-table-header">
    <div class="admin-table-title">🔍 Filter Hasil</div>
    <?php if($userId || $testId): ?>
    <a href="<?= SITE_URL ?>/admin/pages/results.php" class="btn btn-secondary btn-sm">Reset Filter</a>
    <?php endif; ?>
  </div>
  <div style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
      <div>
        <label style="font-size:0.78rem;font-weight:600;color:#64748B;display:block;margin-bottom:4px;">Filter Pengguna</label>
        <select name="user_id" class="admin-select" style="width:200px;padding:8px 12px;">
          <option value="">Semua Pengguna</option>
          <?php foreach($users as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $userId==$u['id']?'selected':'' ?>><?= sanitize($u['full_name']?:$u['username']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="font-size:0.78rem;font-weight:600;color:#64748B;display:block;margin-bottom:4px;">Filter Test</label>
        <select name="test_id" class="admin-select" style="width:220px;padding:8px 12px;">
          <option value="">Semua Test</option>
          <?php foreach($tests as $t): ?>
          <option value="<?= $t['id'] ?>" <?= $testId==$t['id']?'selected':'' ?>><?= sanitize($t['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;align-items:flex-end;">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Terapkan</button>
      </div>
    </form>
  </div>
</div>

<!-- Results Table -->
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">
      📋 Riwayat Hasil Test
      <?php if($filterUser): ?> — <?= sanitize($filterUser['full_name']?:$filterUser['username']) ?><?php endif; ?>
      (<?= count($results) ?>)
    </div>
  </div>
  <table class="admin-table">
    <thead><tr>
      <th>#</th><th>Pengguna</th><th>Test</th><th>Skor TOEFL</th><th>Benar</th><th>Waktu</th><th>Tanggal</th><th>Aksi</th>
    </tr></thead>
    <tbody>
    <?php if(empty($results)): ?>
    <tr><td colspan="8" style="text-align:center;padding:40px;color:#94A3B8;">Belum ada hasil test.</td></tr>
    <?php else: foreach($results as $i=>$r): ?>
    <tr>
      <td style="color:#94A3B8;"><?= $i+1 ?></td>
      <td>
        <div style="font-weight:600;font-size:0.875rem;"><?= sanitize($r['full_name']?:$r['username']) ?></div>
        <div style="font-size:0.75rem;color:#94A3B8;"><?= sanitize($r['email']) ?></div>
      </td>
      <td>
        <div style="font-size:0.875rem;"><?= sanitize($r['test_title']) ?></div>
        <span class="test-type-badge <?= $r['test_type']==='mini'?'badge-mini':'badge-full' ?>" style="font-size:0.68rem;padding:2px 8px;"><?= $r['test_type']==='mini'?'Mini':'Full' ?></span>
      </td>
      <td>
        <span style="font-size:1.1rem;font-weight:800;color:<?= $r['toefl_score']>=550?'#10B981':($r['toefl_score']>=450?'#F59E0B':'#EF4444') ?>;">
          <?= $r['toefl_score'] ?>
        </span>
        <span style="color:#94A3B8;font-size:0.75rem;">/677</span>
      </td>
      <td><?= $r['total_correct'] ?>/<?= $r['total_questions'] ?> <span style="color:#94A3B8;font-size:0.78rem;">(<?= $r['score'] ?>%)</span></td>
      <td style="color:#64748B;font-size:0.82rem;"><?= $r['time_taken'] ? floor($r['time_taken']/60).'m '.($r['time_taken']%60).'d' : '-' ?></td>
      <td style="color:#94A3B8;font-size:0.8rem;"><?= date('d M Y H:i',strtotime($r['completed_at'])) ?></td>
      <td>
        <a href="<?= SITE_URL ?>/pages/result.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Detail</a>
      </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php include '../includes/footer.php'; ?>