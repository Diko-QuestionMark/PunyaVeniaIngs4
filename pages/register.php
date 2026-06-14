<?php
require_once '../includes/config.php';
if (isUserLoggedIn()) redirect(SITE_URL.'/pages/dashboard.php');

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $db = getDB();
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if (!$full_name || !$username || !$email || !$password) {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $chk = $db->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $chk->execute([$username,$email]);
        if ($chk->fetch()) {
            $error = 'Username atau email sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO users (full_name,username,email,password) VALUES (?,?,?,?)")
               ->execute([$full_name,$username,$email,$hash]);
            $uid = $db->lastInsertId();
            $_SESSION['user_id'] = $uid;
            $_SESSION['username'] = $username;
            flashMessage('success','Selamat datang, '.$full_name.'! Akun berhasil dibuat.');
            redirect(SITE_URL.'/pages/dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{background:linear-gradient(135deg,#0F172A 0%,#1E293B 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;}
.auth-card{background:white;border-radius:24px;padding:48px 44px;width:100%;max-width:460px;box-shadow:0 24px 80px rgba(0,0,0,0.4);}
.auth-logo{text-align:center;margin-bottom:28px;}
.auth-logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#2563EB,#7C3AED);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;color:white;margin:0 auto 10px;}
.auth-logo h2{font-size:1.25rem;color:#0F172A;}
.auth-logo p{font-size:0.85rem;color:#94A3B8;margin-top:4px;}
.divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:#CBD5E1;font-size:0.82rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#E2E8F0;}
</style>
</head>
<body>
<div class="auth-card">
  <div class="auth-logo">
    <a href="<?= SITE_URL ?>"><div class="auth-logo-icon">T</div></a>
    <h2>Buat Akun Gratis</h2>
    <p>Mulai belajar TOEFL hari ini, tanpa biaya!</p>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label class="form-label">Nama Lengkap</label>
      <input type="text" name="full_name" class="form-control" placeholder="Nama lengkap kamu" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" placeholder="username unik" value="<?= sanitize($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="email@contoh.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
      </div>
      <div class="form-group">
        <label class="form-label">Konfirmasi Password</label>
        <input type="password" name="confirm" class="form-control" placeholder="Ulangi password" required>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:4px;">
      <i class="fas fa-user-plus"></i> Daftar Sekarang
    </button>
  </form>

  <div class="divider">sudah punya akun?</div>
  <a href="<?= SITE_URL ?>/pages/login.php" class="btn btn-secondary btn-block">
    <i class="fas fa-sign-in-alt"></i> Masuk ke Akun
  </a>
  <div style="text-align:center;margin-top:16px;">
    <a href="<?= SITE_URL ?>" style="font-size:0.82rem;color:#94A3B8;">← Kembali ke Beranda</a>
  </div>
</div>
</body>
</html>