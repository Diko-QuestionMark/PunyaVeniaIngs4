<?php
require_once '../includes/config.php';
requireAdminLogin();
$db = getDB();

$totalMaterials = $db->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$totalQuestions = $db->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$totalTests     = $db->query("SELECT COUNT(*) FROM tests")->fetchColumn();
$totalUsers     = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalResults   = $db->query("SELECT COUNT(*) FROM user_test_results")->fetchColumn();

$recentUsers = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentResults = $db->query("
    SELECT r.*, u.full_name, u.username, t.title as test_title
    FROM user_test_results r
    JOIN users u ON r.user_id = u.id
    JOIN tests t ON r.test_id = t.id
    ORDER BY r.completed_at DESC LIMIT 5
")->fetchAll();

$admin = $db->prepare("SELECT * FROM admins WHERE id = ?");
$admin->execute([$_SESSION['admin_id']]);
$admin = $admin->fetch();

$pageTitle = 'Dashboard Admin';
include 'includes/header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:#DBEAFE;">📚</div>
    <div class="stat-info">
      <div class="stat-label">Total Materi</div>
      <div class="stat-value"><?= $totalMaterials ?></div>
      <div class="stat-change up">↑ Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#EDE9FE;">❓</div>
    <div class="stat-info">
      <div class="stat-label">Total Soal</div>
      <div class="stat-value"><?= $totalQuestions ?></div>
      <div class="stat-change up">Bank soal</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#D1FAE5;">🏆</div>
    <div class="stat-info">
      <div class="stat-label">Total Test</div>
      <div class="stat-value"><?= $totalTests ?></div>
      <div class="stat-change">Mini + Full</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#FEF3C7;">👥</div>
    <div class="stat-info">
      <div class="stat-label">Pengguna</div>
      <div class="stat-value"><?= $totalUsers ?></div>
      <div class="stat-change up">↑ Terdaftar</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#E0F2FE;">📊</div>
    <div class="stat-info">
      <div class="stat-label">Hasil Test</div>
      <div class="stat-value"><?= $totalResults ?></div>
      <div class="stat-change">Percobaan</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <!-- Recent Users -->
  <div class="admin-table-card">
    <div class="admin-table-header">
      <div class="admin-table-title">👥 Pengguna Terbaru</div>
      <a href="pages/users.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>
    <table class="admin-table">
      <thead><tr>
        <th>Nama</th>
        <th>Email</th>
        <th>Bergabung</th>
      </tr></thead>
      <tbody>
        <?php if (empty($recentUsers)): ?>
        <tr><td colspan="3" style="text-align:center;color:#94A3B8;padding:30px;">Belum ada pengguna</td></tr>
        <?php else: ?>
        <?php foreach($recentUsers as $u): ?>
        <tr>
          <td><strong><?= sanitize($u['full_name'] ?: $u['username']) ?></strong></td>
          <td style="color:#64748B;"><?= sanitize($u['email']) ?></td>
          <td style="color:#94A3B8;font-size:0.8rem;"><?= timeAgo($u['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Recent Test Results -->
  <div class="admin-table-card">
    <div class="admin-table-header">
      <div class="admin-table-title">📊 Hasil Test Terbaru</div>
      <a href="pages/results.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>
    <table class="admin-table">
      <thead><tr>
        <th>Pengguna</th>
        <th>Test</th>
        <th>Skor</th>
        <th>Waktu</th>
      </tr></thead>
      <tbody>
        <?php if (empty($recentResults)): ?>
        <tr><td colspan="4" style="text-align:center;color:#94A3B8;padding:30px;">Belum ada hasil test</td></tr>
        <?php else: ?>
        <?php foreach($recentResults as $r): ?>
        <tr>
          <td><strong><?= sanitize($r['full_name'] ?: $r['username']) ?></strong></td>
          <td style="font-size:0.82rem;color:#64748B;"><?= sanitize($r['test_title']) ?></td>
          <td>
            <span style="font-weight:700;color:<?= $r['toefl_score'] >= 500 ? '#10B981' : '#F59E0B' ?>;">
              <?= $r['toefl_score'] ?>
            </span>
          </td>
          <td style="color:#94A3B8;font-size:0.8rem;"><?= timeAgo($r['completed_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Quick Actions -->
<div style="margin-top:24px;">
  <h3 style="font-size:1rem;color:#0F172A;margin-bottom:16px;font-weight:700;">⚡ Aksi Cepat</h3>
  <div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="pages/materials.php?action=add" class="btn btn-primary">
      <i class="fas fa-plus"></i> Tambah Materi
    </a>
    <a href="pages/questions.php?action=add" class="btn btn-success">
      <i class="fas fa-plus"></i> Tambah Soal
    </a>
    <a href="pages/tests.php?action=add" class="btn btn-secondary" style="border-color:#7C3AED;color:#7C3AED;">
      <i class="fas fa-plus"></i> Buat Test Baru
    </a>
    <a href="../index.php" target="_blank" class="btn btn-secondary">
      <i class="fas fa-external-link-alt"></i> Lihat Website
    </a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>