<?php
require_once '../includes/config.php';
// if(!isUserLoggedIn()) {
//     header("Location: login.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Tutor — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background:#F8FAFC;">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:70px;">
  <!-- Hero / Header AI -->
  <div style="background:linear-gradient(135deg,#0F172A,#1E293B);padding:60px 5% 40px;text-align:center;" data-aos="fade-down">
    <div style="width:70px;height:70px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:white;margin-bottom:16px;">
      <i class="fas fa-robot"></i>
    </div>
    <h1 style="color:white;font-size:2rem;margin-bottom:10px;">TOEFL AI Tutor</h1>
    <p style="color:#94A3B8;max-width:500px;margin:0 auto;font-size:0.95rem;">Asisten cerdas Anda untuk belajar TOEFL. Tanyakan materi, minta penjelasan grammar, atau latih pemahaman reading Anda di sini.</p>
  </div>

  <div style="max-width:900px;margin:40px auto;padding:0 5% 80px;" data-aos="fade-up">
    <!-- Chat Interface Container (To be integrated) -->
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;min-height:500px;display:flex;flex-direction:column;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
      
      <!-- Chat Header -->
      <div style="padding:20px 24px;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;background:#EFF6FF;color:#2563EB;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
          <i class="fas fa-robot"></i>
        </div>
        <div>
          <h2 style="font-size:1rem;margin:0;color:#0F172A;">AI Tutor Assistant</h2>
          <div style="font-size:0.8rem;color:#10B981;font-weight:600;"><i class="fas fa-circle" style="font-size:0.5rem;margin-right:4px;"></i>Online</div>
        </div>
      </div>

      <!-- Chat Body (Placeholder) -->
      <div style="flex:1;padding:24px;background:#F8FAFC;overflow-y:auto;display:flex;flex-direction:column;gap:16px;">
        
        <!-- AI Message -->
        <div style="display:flex;gap:12px;align-items:flex-start;">
          <div style="width:36px;height:36px;background:#2563EB;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
            <i class="fas fa-robot"></i>
          </div>
          <div style="background:white;padding:14px 18px;border-radius:0 16px 16px 16px;border:1px solid #E2E8F0;max-width:80%;box-shadow:0 2px 4px rgba(0,0,0,0.02);">
            <p style="margin:0;font-size:0.9rem;color:#334155;line-height:1.6;">Halo! Saya adalah AI Tutor Anda. Ada pertanyaan spesifik tentang soal TOEFL yang menjebak? Atau butuh penjelasan ulang tentang <em>Subject-Verb Agreement</em>?</p>
          </div>
        </div>

      </div>

      <!-- Chat Input (Placeholder) -->
      <div style="padding:20px 24px;border-top:1px solid #E2E8F0;background:white;border-radius:0 0 20px 20px;">
        <form style="display:flex;gap:12px;">
          <input type="text" class="form-control" placeholder="Tanya sesuatu tentang TOEFL..." style="flex:1;background:#F1F5F9;border:1px solid #E2E8F0;border-radius:100px;padding:12px 20px;font-size:0.9rem;" disabled>
          <button type="button" class="btn btn-primary" style="width:46px;height:46px;border-radius:50%;padding:0;display:flex;align-items:center;justify-content:center;" disabled>
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
        <div style="text-align:center;margin-top:10px;font-size:0.75rem;color:#94A3B8;">
          Sedang dalam tahap pengembangan. Fitur chat akan segera aktif!
        </div>
      </div>
      
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
