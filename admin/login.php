<?php
require_once '../includes/config.php';
if (isAdminLoggedIn()) redirect(SITE_URL . '/admin/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();
        if ($admin && $password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.login-card { background: white; border-radius: 24px; padding: 48px 44px; width: 100%; max-width: 420px; box-shadow: 0 24px 80px rgba(0,0,0,0.4); }
.login-logo { text-align: center; margin-bottom: 32px; }
.login-logo-icon { width: 64px; height: 64px; background: linear-gradient(135deg, #2563EB, #7C3AED); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 900; color: white; margin: 0 auto 12px; }
.login-logo h2 { font-size: 1.3rem; color: #0F172A; }
.login-logo p { font-size: 0.85rem; color: #94A3B8; margin-top: 4px; }
</style>
</head>
<body>
<div class="login-card">
  <div class="login-logo">
    <div class="login-logo-icon">T</div>
    <h2>TOEFLMaster</h2>
    <p>Admin Panel — Masuk untuk mengelola konten</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger"><?= sanitize($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label class="form-label">Username / Email</label>
      <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" value="<?= sanitize($_POST['username'] ?? '') ?>" required autofocus>
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <div style="position:relative;">
        <input type="password" name="password" class="form-control" placeholder="Masukkan password" id="pwdField" required style="padding-right:44px;">
        <button type="button" onclick="togglePwd()" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94A3B8;font-size:1rem;" id="pwdToggle"><i class="fas fa-eye"></i></button>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">
      <i class="fas fa-sign-in-alt"></i> Masuk ke Admin Panel
    </button>
  </form>

  <div style="text-align:center;margin-top:24px;font-size:0.82rem;color:#94A3B8;">
    <p>Default: <code>admin</code> / <code>admin123</code></p>
    <a href="<?= SITE_URL ?>/index.php" style="color:#2563EB;">← Kembali ke Website</a>
  </div>
</div>
<script>
function togglePwd() {
  const f = document.getElementById('pwdField');
  const i = document.querySelector('#pwdToggle i');
  if (f.type === 'password') { f.type = 'text'; i.className = 'fas fa-eye-slash'; }
  else { f.type = 'password'; i.className = 'fas fa-eye'; }
}
</script>
</body>
</html>