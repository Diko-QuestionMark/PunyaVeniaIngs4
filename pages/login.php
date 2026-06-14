<?php
require_once '../includes/config.php';
if (isUserLoggedIn()) redirect(SITE_URL.'/pages/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $db = getDB();
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($login && $password) {
        $stmt = $db->prepare("SELECT * FROM users WHERE username=? OR email=?");
        $stmt->execute([$login,$login]);
        $user = $stmt->fetch();
        if ($user && $password === $user['password']) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $db->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
            $redir = $_GET['redirect'] ?? (SITE_URL.'/pages/dashboard.php');
            redirect($redir);
        } else {
            $error = 'Username/email atau password salah.';
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
<title>Masuk — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{background:linear-gradient(135deg,#0F172A 0%,#1E293B 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;}
.auth-card{background:white;border-radius:24px;padding:48px 44px;width:100%;max-width:420px;box-shadow:0 24px 80px rgba(0,0,0,0.4);}
.auth-logo{text-align:center;margin-bottom:28px;}
.auth-logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#2563EB,#7C3AED);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;color:white;margin:0 auto 10px;}
</style>
</head>
<body>
<div class="auth-card">
  <div class="auth-logo">
    <a href="<?= SITE_URL ?>"><div class="auth-logo-icon">T</div></a>
    <h2 style="font-size:1.25rem;color:#0F172A;">Selamat Datang Kembali!</h2>
    <p style="font-size:0.85rem;color:#94A3B8;margin-top:4px;">Masuk dan lanjutkan belajar TOEFL</p>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
  <?php $flash=getFlashMessage(); if($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div><?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label class="form-label">Username atau Email</label>
      <input type="text" name="login" class="form-control" placeholder="Masukkan username atau email" value="<?= sanitize($_POST['login'] ?? '') ?>" required autofocus>
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <div style="position:relative;">
        <input type="password" name="password" class="form-control" placeholder="Masukkan password" id="pwdF" required style="padding-right:44px;">
        <button type="button" onclick="const f=document.getElementById('pwdF');f.type=f.type==='password'?'text':'password';" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94A3B8;font-size:1rem;">👁</button>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg">
      <i class="fas fa-sign-in-alt"></i> Masuk
    </button>
  </form>

  <div style="text-align:center;margin-top:20px;font-size:0.85rem;color:#64748B;">
    Belum punya akun?
    <a href="<?= SITE_URL ?>/pages/register.php" style="color:#2563EB;font-weight:600;"> Daftar Gratis</a>
  </div>
  <div style="text-align:center;margin-top:10px;">
    <a href="<?= SITE_URL ?>" style="font-size:0.82rem;color:#94A3B8;">← Kembali ke Beranda</a>
  </div>
</div>
</body>
</html>