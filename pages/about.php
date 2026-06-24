<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tentang TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:70px;">
  <!-- Hero -->
  <div style="background:linear-gradient(135deg,#0F172A,#1E293B);padding:80px 5%;text-align:center;" data-aos="fade-down">
    <div style="width:80px;height:80px;background:linear-gradient(135deg,#2563EB,#7C3AED);border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;color:white;margin:0 auto 20px;">T</div>
    <h1 style="color:white;font-size:2rem;margin-bottom:12px;">Tentang TOEFLMaster</h1>
    <p style="color:#94A3B8;max-width:580px;margin:0 auto;font-size:0.95rem;line-height:1.7;">
      Platform belajar TOEFL berbasis web yang dirancang untuk membantu mahasiswa mempersiapkan ujian TOEFL ITP secara mandiri, terstruktur, dan efektif.
    </p>
  </div>

  <div style="max-width:900px;margin:0 auto;padding:60px 5% 80px;">

    <!-- About Platform -->
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;padding:36px;margin-bottom:24px;" data-aos="fade-up">
      <h2 style="font-size:1.2rem;margin-bottom:16px;">🎯 Tentang Platform</h2>
      <p style="color:#475569;line-height:1.8;font-size:0.95rem;margin-bottom:14px;">
        <strong>TOEFLMaster</strong> adalah platform e-learning yang dikembangkan sebagai proyek tugas akhir mata kuliah Pemrograman Web. Platform ini dirancang untuk membantu mahasiswa mempersiapkan ujian <strong>TOEFL ITP (Institutional Testing Program)</strong> dengan pendekatan belajar yang terstruktur dan interaktif.
      </p>
      <p style="color:#475569;line-height:1.8;font-size:0.95rem;">
        Website ini menyediakan materi pembelajaran yang lengkap, latihan soal per materi, mini test, serta simulasi full test 100 soal yang mendekati kondisi ujian TOEFL sesungguhnya. Seluruh konten dikelola oleh admin melalui panel manajemen yang mudah digunakan.
      </p>
    </div>
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;padding:36px;margin-bottom:24px;" data-aos="fade-up">
      <h2 style="font-size:1.2rem;margin-bottom:16px;">👥 Kelompok Kami</h2>
      <ul style="list-style:none;padding-left:0;color:#475569;font-size:0.95rem;line-height:1.6;">
        <li>1. Chelsea Germany</li>
        <li>2. Yana</li>
        <li>3. Rizky Kamelia</li>
        <li>4. Sena</li>
        <li>5. Venia</li>
        <li>6. Fedriko</li>
      </ul>
    </div>

    <!-- Features Summary -->
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;padding:36px;margin-bottom:24px;" data-aos="fade-up">
      <h2 style="font-size:1.2rem;margin-bottom:20px;">✨ Fitur Platform</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <?php $features = [
          ['📚','12 Materi Terstruktur','Mencakup 3 section TOEFL ITP: Listening (4), Structure (4), Reading (4)'],
          ['✏️','Latihan Per-Materi','Setiap materi dilengkapi latihan soal yang relevan dengan pembahasan'],
          ['⚡','Mini Test','Latihan cepat 10–15 soal terfokus per bagian TOEFL'],
          ['🏆','Full Test 100 Soal','Simulasi TOEFL sesungguhnya dengan timer dan skor ITP (200–677)'],
          ['💡','Pembahasan Lengkap','Setiap jawaban memiliki penjelasan mengapa benar atau salah'],
          ['📊','Dashboard Progres','Pantau riwayat test, skor, dan perkembangan belajar'],
          ['👨‍💼','Admin Panel','Admin dapat menambah, mengedit, dan menghapus materi & soal'],
          ['🔊','Audio Listening','Dukungan file audio untuk soal Listening Comprehension'],
        ];
        foreach($features as $index => [$icon,$title,$desc]): ?>
        <div style="display:flex;gap:12px;padding:16px;background:#F8FAFC;border-radius:12px;" data-aos="zoom-in" data-aos-delay="<?= $index * 50 ?>">
          <span style="font-size:1.3rem;flex-shrink:0;"><?= $icon ?></span>
          <div>
            <div style="font-weight:700;font-size:0.875rem;color:#0F172A;margin-bottom:4px;"><?= $title ?></div>
            <div style="font-size:0.8rem;color:#64748B;line-height:1.5;"><?= $desc ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- TOEFL ITP Info -->
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;padding:36px;margin-bottom:24px;" data-aos="fade-up">
      <h2 style="font-size:1.2rem;margin-bottom:20px;">📋 Tentang TOEFL ITP</h2>
      <p style="color:#475569;line-height:1.8;font-size:0.9rem;margin-bottom:20px;">
        TOEFL ITP (Institutional Testing Program) adalah tes kemampuan bahasa Inggris yang diakui secara internasional. Tes ini sering digunakan sebagai syarat kelulusan, beasiswa, atau penerimaan di perguruan tinggi.
      </p>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
        <?php $sections=[
          ['🎧','Section 1','Listening Comprehension','50 soal','35 menit','Percakapan & ceramah','#DBEAFE','#1D4ED8'],
          ['📝','Section 2','Structure & Written Expression','40 soal','25 menit','Tata bahasa & ekspresi','#EDE9FE','#6D28D9'],
          ['📖','Section 3','Reading Comprehension','50 soal','55 menit','Teks akademik','#D1FAE5','#065F46'],
        ];
        foreach($sections as [$icon,$num,$title,$q,$time,$desc,$bg,$color]): ?>
        <div style="background:<?= $bg ?>;border-radius:14px;padding:20px;text-align:center;">
          <div style="font-size:1.6rem;margin-bottom:6px;"><?= $icon ?></div>
          <div style="font-size:0.7rem;font-weight:700;color:<?= $color ?>;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;"><?= $num ?></div>
          <div style="font-size:0.9rem;font-weight:700;color:#0F172A;margin-bottom:6px;"><?= $title ?></div>
          <div style="font-size:0.78rem;color:#64748B;margin-bottom:4px;"><?= $q ?> · <?= $time ?></div>
          <div style="font-size:0.75rem;color:<?= $color ?>;font-weight:600;"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:16px;background:#F8FAFC;border-radius:12px;padding:16px 20px;font-size:0.875rem;color:#475569;text-align:center;">
        <strong>Total:</strong> 140 soal · 115 menit · Skor 200–677
      </div>
    </div>

    <!-- Tech Stack -->
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;padding:36px;margin-bottom:24px;" data-aos="fade-up">
      <h2 style="font-size:1.2rem;margin-bottom:16px;">🛠️ Teknologi yang Digunakan</h2>
      <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php $tech=[
          ['PHP 8+','#F97316'],['MySQL','#06B6D4'],['HTML5','#EF4444'],
          ['CSS3','#3B82F6'],['JavaScript ES6+','#EAB308'],['Font: Poppins','#8B5CF6'],
          ['Font Awesome','#10B981'],['PDO Database','#64748B'],['Responsive Design','#EC4899'],
        ];
        foreach($tech as $index => [$name,$color]): ?>
        <span style="background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>40;padding:6px 14px;border-radius:100px;font-size:0.8rem;font-weight:600;" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>"><?= $name ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- CTA -->
    <div style="background:linear-gradient(135deg,#EFF6FF,#F5F3FF);border-radius:20px;padding:36px;text-align:center;border:1px solid #BFDBFE;" data-aos="fade-up">
      <h3 style="font-size:1.1rem;margin-bottom:8px;">🚀 Siap Mulai Belajar?</h3>
      <p style="color:#64748B;font-size:0.9rem;margin-bottom:20px;">Daftar sekarang dan akses semua materi serta latihan soal secara gratis.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/pages/register.php" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Daftar Gratis</a>
        <a href="<?= SITE_URL ?>/index.php" class="btn btn-secondary btn-lg"><i class="fas fa-home"></i> Ke Beranda</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>