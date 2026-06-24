<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

// Get stats
$totalMaterials = $db->query("SELECT COUNT(*) FROM materials WHERE is_published=1")->fetchColumn();
$totalQuestions = $db->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$totalTests = $db->query("SELECT COUNT(*) FROM tests WHERE is_published=1")->fetchColumn();
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get categories
$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$listeningCats = array_filter($categories, fn($c) => $c['section'] === 'listening');
$structureCats = array_filter($categories, fn($c) => $c['section'] === 'structure');
$readingCats   = array_filter($categories, fn($c) => $c['section'] === 'reading');

// Get featured materials (latest)
$featuredMaterials = $db->query("
    SELECT m.*, c.name as cat_name, c.section 
    FROM materials m JOIN categories c ON m.category_id = c.id 
    WHERE m.is_published=1 ORDER BY m.id DESC LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TOEFLMaster - Platform Belajar TOEFL Terlengkap</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<?php 
$is_home = true;
include __DIR__ . '/includes/navbar.php'; 
?>

<!-- HERO SECTION -->
<section class="hero">
  <div class="hero-grid">
    <div class="hero-content">
      <div class="hero-eyebrow" data-aos="fade-down">
        <i class="fas fa-star" style="font-size:0.7rem;"></i>
        Platform TOEFL #1
      </div>
      <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">
        Raih Skor TOEFL <span>Impianmu</span> Bersama Kami
      </h1>
      <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="150">
        Tingkatkan skor TOEFL Anda dengan platform simulasi terlengkap. Nikmati puluhan materi terstruktur, latihan soal interaktif, serta simulasi ujian komprehensif 140 soal sesuai standar resmi TOEFL ITP!
      </p>
      <div class="hero-actions" data-aos="fade-up" data-aos-delay="200">
        <a href="pages/register.php" class="btn btn-primary btn-lg">
          <i class="fas fa-rocket"></i> Mulai Belajar Gratis
        </a>
      </div>
      <div class="hero-stats" data-aos="fade-up" data-aos-delay="300">
        <div class="hero-stat-item">
          <span class="hero-stat-num"><?= number_format($totalMaterials) ?></span>
          <span class="hero-stat-label">Materi</span>
        </div>
        <div class="hero-stat-item">
          <span class="hero-stat-num"><?= number_format($totalQuestions) ?></span>
          <span class="hero-stat-label">Soal Latihan</span>
        </div>
        <div class="hero-stat-item">
          <span class="hero-stat-num"><?= number_format($totalTests) ?></span>
          <span class="hero-stat-label">Test Tersedia</span>
        </div>
        <div class="hero-stat-item">
          <span class="hero-stat-num"><?= number_format($totalUsers) ?></span>
          <span class="hero-stat-label">Pengguna</span>
        </div>
      </div>
    </div>

    <!-- Hero Visual -->
    <div class="hero-visual" data-aos="fade-left" data-aos-delay="200">
      <div style="text-align: center; margin-bottom: 24px;">
        <img src="<?= SITE_URL ?>/assets/mascot.png" alt="TOEFLMaster Mascot" style="max-width: 100%;">
      </div>
    </div>
  </div>
</section>

<!-- TOEFL SECTIONS -->
<section class="section" style="background:white;">
  <div class="section-header" data-aos="fade-up">
    <div class="section-eyebrow">Bagian Materi</div>
    <h2 class="section-title">3 Bagian Utama TOEFL ITP</h2>
    <p class="section-subtitle">Kuasai semua bagian TOEFL dengan materi terstruktur dan latihan soal yang komprehensif.</p>
  </div>

  <div class="sections-grid">
    <!-- Listening -->
    <a href="pages/listening.php" class="section-card listening" data-aos="fade-up" data-aos-delay="100">
      <div class="section-card-icon">🎧</div>
      <div class="section-card-number">Section 1 · <?= count(iterator_to_array($listeningCats)) ?> Materi</div>
      <h3>Listening Comprehension</h3>
      <p>Tingkatkan kemampuan mendengarkan percakapan, ceramah, dan pengumuman dalam Bahasa Inggris dengan latihan intensif.</p>
      <div class="section-card-meta">
        <div class="meta-item">
          <span class="meta-label">Soal</span>
          <span class="meta-val">50 Items</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Durasi</span>
          <span class="meta-val">35 Menit</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Skor Max</span>
          <span class="meta-val">677</span>
        </div>
      </div>
      <div class="btn btn-primary btn-sm" style="width:fit-content;">
        Mulai Belajar <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
      </div>
    </a>

    <!-- Structure -->
    <a href="pages/structure.php" class="section-card structure" data-aos="fade-up" data-aos-delay="200">
      <div class="section-card-icon">📝</div>
      <div class="section-card-number">Section 2 · <?= count(iterator_to_array($structureCats)) ?> Materi</div>
      <h3>Structure & Written Expression</h3>
      <p>Kuasai tata bahasa Inggris yang benar melalui latihan sentence completion dan error identification yang menyeluruh.</p>
      <div class="section-card-meta">
        <div class="meta-item">
          <span class="meta-label">Soal</span>
          <span class="meta-val">40 Items</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Durasi</span>
          <span class="meta-val">25 Menit</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Skor Max</span>
          <span class="meta-val">677</span>
        </div>
      </div>
      <div class="btn btn-sm" style="background:rgba(139,92,246,0.15);color:#7C3AED;border:none;width:fit-content;padding:8px 18px;border-radius:100px;font-weight:600;font-size:0.85rem;">
        Mulai Belajar <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
      </div>
    </a>

    <!-- Reading -->
    <a href="pages/reading.php" class="section-card reading" data-aos="fade-up" data-aos-delay="300">
      <div class="section-card-icon">📖</div>
      <div class="section-card-number">Section 3 · <?= count(iterator_to_array($readingCats)) ?> Materi</div>
      <h3>Reading Comprehension</h3>
      <p>Tingkatkan pemahaman membaca teks akademis panjang dengan berbagai strategi efektif dan latihan soal beragam.</p>
      <div class="section-card-meta">
        <div class="meta-item">
          <span class="meta-label">Soal</span>
          <span class="meta-val">50 Items</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Durasi</span>
          <span class="meta-val">55 Menit</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Skor Max</span>
          <span class="meta-val">677</span>
        </div>
      </div>
      <div class="btn btn-sm" style="background:rgba(16,185,129,0.15);color:#059669;border:none;width:fit-content;padding:8px 18px;border-radius:100px;font-weight:600;font-size:0.85rem;">
        Mulai Belajar <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
      </div>
    </a>
  </div>
</section>

<!-- FEATURED MATERIALS -->
<?php if (!empty($featuredMaterials)): ?>
<section class="section">
  <div class="section-header" data-aos="fade-up">
    <div class="section-eyebrow">Materi Terbaru</div>
    <h2 class="section-title">Mulai Dari Materi Ini</h2>
    <p class="section-subtitle">Pilih materi yang ingin kamu pelajari hari ini dan tingkatkan kemampuan TOEFL-mu.</p>
  </div>

  <div class="materials-grid">
    <?php foreach($featuredMaterials as $index => $mat): ?>
    <a href="pages/material.php?slug=<?= urlencode($mat['slug']) ?>" class="material-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
      <div class="material-badge badge-<?= $mat['section'] ?>">
        <?= $mat['section'] === 'listening' ? '🎧' : ($mat['section'] === 'structure' ? '📝' : '📖') ?>
        <?= ucfirst($mat['section']) ?>
      </div>
      <h4><?= sanitize($mat['title']) ?></h4>
      <p><?= $mat['summary'] ? sanitize($mat['summary']) : 'Materi lengkap dengan penjelasan detail dan contoh soal.' ?></p>
      <div class="material-footer">
        <div class="material-meta">
          <span><i class="fas fa-book"></i> <?= sanitize($mat['cat_name']) ?></span>
        </div>
        <span style="color:var(--primary);font-size:0.82rem;font-weight:600;">Baca <i class="fas fa-arrow-right"></i></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <div style="text-align:center;margin-top:40px;">
    <a href="pages/materials.php" class="btn btn-secondary">
      Lihat Semua Materi <i class="fas fa-arrow-right"></i>
    </a>
  </div>
</section>
<?php endif; ?>

<!-- FEATURES SECTION -->
<section class="section" style="background:white;">
  <div class="section-header" data-aos="fade-up">
    <div class="section-eyebrow">Fitur Unggulan</div>
    <h2 class="section-title">Semua yang Kamu Butuhkan</h2>
    <p class="section-subtitle">Fitur lengkap untuk membantu kamu mencapai skor TOEFL tertinggi.</p>
  </div>

  <div class="features-grid">
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="100">
      <div class="feature-icon" style="background:#DBEAFE;">📚</div>
      <h4>12 Materi Terstruktur</h4>
      <p>Materi lengkap mencakup semua aspek TOEFL, dari level dasar hingga lanjutan, dengan penjelasan dan contoh yang mudah dipahami.</p>
    </div>
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="150">
      <div class="feature-icon" style="background:#EDE9FE;">✏️</div>
      <h4>Latihan Per-Materi</h4>
      <p>Setiap materi dilengkapi dengan latihan soal yang relevan untuk mengukur pemahaman kamu secara langsung.</p>
    </div>
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="200">
      <div class="feature-icon" style="background:#D1FAE5;">⚡</div>
      <h4>Mini Test</h4>
      <p>Uji kemampuan dengan mini test 10-15 soal yang terfokus pada bagian tertentu. Ideal untuk latihan harian yang singkat.</p>
    </div>
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="250">
      <div class="feature-icon" style="background:#FEF3C7;">🏆</div>
      <h4>Full Test (100 Soal)</h4>
      <p>Simulasi TOEFL sesungguhnya dengan 100 soal, timer real-time, dan penilaian skor standar TOEFL ITP (200-677).</p>
    </div>
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="300">
      <div class="feature-icon" style="background:#FCE7F3;">💡</div>
      <h4>Pembahasan Lengkap</h4>
      <p>Setiap jawaban dilengkapi penjelasan mendalam mengapa jawaban benar, membantu kamu belajar dari setiap kesalahan.</p>
    </div>
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="350">
      <div class="feature-icon" style="background:#E0F2FE;">📊</div>
      <h4>Tracking Progres</h4>
      <p>Pantau perkembangan skor dan capaian belajarmu dari waktu ke waktu melalui dashboard personal yang informatif.</p>
    </div>
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="400">
      <div class="feature-icon" style="background:#F0FDF4;">🎯</div>
      <h4>Prediksi Skor TOEFL</h4>
      <p>Setelah full test, dapatkan prediksi skor TOEFL ITP-mu berdasarkan performa di setiap bagian (skala 200–677).</p>
    </div>
    <div class="feature-card" data-aos="zoom-in" data-aos-delay="450">
      <div class="feature-icon" style="background:#FFF7ED;">🔊</div>
      <h4>Audio Listening</h4>
      <p>Soal listening dilengkapi audio asli untuk melatih kemampuan mendengar dan memahami percakapan bahasa Inggris.</p>
    </div>
  </div>
</section>

<!-- SCORE BAND -->
<section class="score-band-section">
  <div class="score-band-grid">
    <div data-aos="fade-right">
      <div class="section-eyebrow" style="color:#60A5FA;background:rgba(37,99,235,0.15);">Konversi Skor</div>
      <h2 class="section-title" style="color:white;">Skala Skor TOEFL ITP</h2>
      <p class="section-subtitle" style="text-align:left;">Pahami target skor TOEFL ITP yang kamu butuhkan berdasarkan kebutuhanmu.</p>
      <br>
      <p style="color:#94A3B8;font-size:0.9rem;line-height:1.7;">
        TOEFL ITP menggunakan skala skor <strong style="color:white;">200–677</strong>. Skor dihitung dari rata-rata tiga bagian (Listening, Structure, Reading) yang masing-masing dikonversi ke skala standar.
      </p>
    </div>
    <div data-aos="fade-left">
      <table class="score-table">
        <thead>
          <tr>
            <th>Skor TOEFL</th>
            <th>Level</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>600 – 677</td>
            <td><span class="level-badge" style="background:rgba(16,185,129,0.2);color:#34D399;">Excellent</span></td>
            <td>Master / PhD program</td>
          </tr>
          <tr>
            <td>550 – 599</td>
            <td><span class="level-badge" style="background:rgba(37,99,235,0.2);color:#60A5FA;">Advanced</span></td>
            <td>S2 / Beasiswa Internasional</td>
          </tr>
          <tr>
            <td>500 – 549</td>
            <td><span class="level-badge" style="background:rgba(124,58,237,0.2);color:#A78BFA;">Upper-Int</span></td>
            <td>S1 / Penerimaan Umum</td>
          </tr>
          <tr>
            <td>450 – 499</td>
            <td><span class="level-badge" style="background:rgba(245,158,11,0.2);color:#FCD34D;">Intermediate</span></td>
            <td>Persyaratan Minimum S1</td>
          </tr>
          <tr>
            <td>< 450</td>
            <td><span class="level-badge" style="background:rgba(239,68,68,0.2);color:#FCA5A5;">Beginner</span></td>
            <td>Perlu Persiapan Lebih</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="section" style="background:linear-gradient(135deg,#EFF6FF,#F5F3FF);text-align:center;">
  <div style="max-width:600px;margin:0 auto;" data-aos="zoom-in" data-aos-delay="100">
    <div class="section-eyebrow">Ayo Mulai!</div>
    <h2 class="section-title">Siap Raih Skor TOEFL Terbaikmu?</h2>
    <p class="section-subtitle">Bergabung sekarang dan akses semua materi, latihan, serta full test secara gratis.</p>
    <br><br>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="pages/register.php" class="btn btn-primary btn-lg">
        <i class="fas fa-user-plus"></i> Daftar Sekarang — Gratis!
      </a>
      <a href="pages/tests.php" class="btn btn-secondary btn-lg">
        <i class="fas fa-list-check"></i> Lihat Latihan Soal
      </a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>