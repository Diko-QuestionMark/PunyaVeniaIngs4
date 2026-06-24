<!-- FOOTER -->
<footer class="footer">
  <div class="footer-grid">
    <div>
      <div class="footer-brand">
        <div class="brand-icon">T</div>
        <div class="footer-brand-name">TOEFLMaster</div>
      </div>
      <p class="footer-desc">Platform belajar TOEFL terlengkap di Indonesia. Kuasai Listening, Structure, dan Reading dengan materi dan latihan soal berkualitas tinggi.</p>
      <div style="display:flex;gap:10px;">
        <a href="https://web.facebook.com/?_rdc=1&_rdr#" style="width:36px;height:36px;background:rgba(255,255,255,0.08);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94A3B8;transition:all 0.2s;" onmouseover="this.style.background='rgba(37,99,235,0.3)';this.style.color='white';" onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.color='#94A3B8';"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/polmanbabelofficial?igsh=eG15NGY3YXVmcDYz" style="width:36px;height:36px;background:rgba(255,255,255,0.08);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94A3B8;transition:all 0.2s;" onmouseover="this.style.background='rgba(37,99,235,0.3)';this.style.color='white';" onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.color='#94A3B8';"><i class="fab fa-instagram"></i></a>
        <a href="https://www.youtube.com/@PolmanBabel" style="width:36px;height:36px;background:rgba(255,255,255,0.08);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94A3B8;transition:all 0.2s;" onmouseover="this.style.background='rgba(37,99,235,0.3)';this.style.color='white';" onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.color='#94A3B8';"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div>
      <h5>Materi</h5>
      <div class="footer-links">
        <a href="<?= SITE_URL ?>/pages/listening.php">Listening Comprehension</a>
        <a href="<?= SITE_URL ?>/pages/structure.php">Structure & Written Expression</a>
        <a href="<?= SITE_URL ?>/pages/reading.php">Reading Comprehension</a>
        <a href="<?= SITE_URL ?>/pages/materials.php">Semua Materi</a>
      </div>
    </div>
    <div>
      <h5>Latihan</h5>
      <div class="footer-links">
        <a href="<?= SITE_URL ?>/pages/tests.php?type=mini">Mini Test</a>
        <a href="<?= SITE_URL ?>/pages/tests.php?type=full">Full Test (140 Soal)</a>
        <a href="<?= SITE_URL ?>/pages/tests.php">Semua Latihan</a>
      </div>
    </div>
    <div>
      <h5>Akun</h5>
      <div class="footer-links">
        <a href="<?= SITE_URL ?>/pages/register.php">Daftar</a>
        <a href="<?= SITE_URL ?>/pages/login.php">Masuk</a>
        <a href="<?= SITE_URL ?>/pages/dashboard.php">Dashboard</a>
        <a href="<?= SITE_URL ?>/pages/about.php">Tentang Platform</a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© <?= date('Y') ?> TOEFLMaster. Dibuat untuk keperluan akademis.</p>
    <p style="color:#475569;">Designed with ❤️ for TOEFL Learners</p>
  </div>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 500,
    once: true,
    offset: 50
  });
</script>
