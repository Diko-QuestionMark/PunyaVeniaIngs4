<?php
require_once '../includes/config.php';
requireUserLogin();
$db  = getDB();
$rid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT r.*,t.title,t.test_type
    FROM user_test_results r JOIN tests t ON r.test_id=t.id
    WHERE r.id=? AND r.user_id=?
");
$stmt->execute([$rid,$uid]); $result = $stmt->fetch();
if (!$result) { flashMessage('danger','Hasil tidak ditemukan.'); redirect(SITE_URL.'/pages/dashboard.php'); }

$studentName = $_SESSION['username'];
$score = $result['toefl_score'];
$date = date('d F Y', strtotime($result['completed_at']));
$testName = $result['title'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sertifikat TOEFL - <?= sanitize($studentName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    background: #e2e8f0;
    margin: 0;
    padding: 40px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    font-family: 'Montserrat', sans-serif;
}
.cert-container {
    width: 1150px; 
    height: 800px; 
    background: white;
    box-sizing: border-box;
    position: relative;
    padding: 30px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}
.cert-border {
    width: 100%;
    height: 100%;
    border: 12px solid #1E293B;
    box-sizing: border-box;
    padding: 10px;
    position: relative;
}
.cert-border-inner {
    width: 100%;
    height: 100%;
    border: 3px solid #EAB308;
    box-sizing: border-box;
    text-align: center;
    padding: 40px;
    position: relative;
    background: url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><text x="10" y="50" font-family="sans-serif" font-size="14" fill="%23f1f5f9" transform="rotate(-45 50 50)">TOEFLMaster</text></svg>');
    display: flex;
    flex-direction: column;
    align-items: center;
}
.cert-header {
    font-family: 'Playfair Display', serif;
    font-size: 4rem;
    color: #0F172A;
    font-weight: 700;
    margin: 0 0 5px 0;
    letter-spacing: 4px;
}
.cert-sub {
    font-size: 1rem;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 6px;
    margin-bottom: 30px;
}
.cert-presented {
    font-size: 1.2rem;
    color: #334155;
    margin-bottom: 20px;
}
.cert-name {
    font-family: 'Playfair Display', serif;
    font-size: 3.2rem;
    color: #2563EB;
    font-weight: 700;
    border-bottom: 2px solid #EAB308;
    display: inline-block;
    padding-bottom: 5px;
    margin-bottom: 20px;
    min-width: 500px;
}
.cert-desc {
    font-size: 1.05rem;
    color: #475569;
    max-width: 850px;
    margin: 0 auto 30px;
    line-height: 1.5;
}
.cert-score {
    font-size: 1.3rem;
    color: #0F172A;
    margin-bottom: 20px;
}
.cert-score span {
    font-size: 2.5rem;
    font-weight: 800;
    color: #10B981;
}
.cert-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding: 0 40px;
    margin-top: auto;
    width: 100%;
    box-sizing: border-box;
}
.cert-sign-col {
    text-align: center;
    width: 250px;
}
.cert-sign-col img {
    height: 60px;
    margin-bottom: -10px;
}
.cert-line {
    border-top: 2px solid #94A3B8;
    margin: 10px 0;
}
.cert-stamp {
    width: 110px;
    height: 110px;
    background: #EAB308;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    border: 4px dashed white;
    margin: 0 20px;
    position: relative;
    top: 5px;
}
.cert-stamp-inner {
    text-align: center;
    color: white;
}
.btn-print {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #2563EB;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 100px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
    z-index: 100;
}
.btn-print:hover { background: #1D4ED8; }
.btn-back {
    position: fixed;
    top: 20px;
    left: 20px;
    background: white;
    color: #334155;
    border: 1px solid #E2E8F0;
    padding: 12px 24px;
    border-radius: 100px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    text-decoration: none;
    z-index: 100;
}

@media print {
    @page { size: landscape; margin: 0; }
    body { padding: 0; background: white; }
    .cert-container { box-shadow: none; width: 100vw; height: 100vh; }
    .btn-print, .btn-back { display: none !important; }
}
</style>
</head>
<body>

<a href="<?= SITE_URL ?>/pages/result.php?id=<?= $rid ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
<button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>

<div class="cert-container">
    <div class="cert-border">
        <div class="cert-border-inner">
            
            <h1 class="cert-header">CERTIFICATE</h1>
            <div class="cert-sub">of Achievement</div>

            <div class="cert-presented">Sertifikat ini diberikan dengan bangga kepada:</div>
            
            <div class="cert-name"><?= sanitize(strtoupper($studentName)) ?></div>

            <div class="cert-desc">
                Telah berhasil menyelesaikan simulasi ujian TOEFL ITP pada <strong><?= sanitize($testName) ?></strong> 
                dengan dedikasi dan performa yang luar biasa di platform TOEFLMaster.
            </div>

            <div class="cert-score">
                Prediksi Skor TOEFL ITP: <br>
                <span><?= $score ?></span>
            </div>

            <div class="cert-footer">
                <div class="cert-sign-col">
                    <div style="font-weight:600;color:#0F172A;font-size:1.1rem;margin-bottom:10px;"><?= $date ?></div>
                    <div class="cert-line"></div>
                    <div style="font-size:0.9rem;color:#64748B;">Tanggal Penyelesaian</div>
                </div>

                <div class="cert-stamp">
                    <div class="cert-stamp-inner">
                        <i class="fas fa-award" style="font-size:2rem;margin-bottom:4px;"></i>
                        <div style="font-size:0.6rem;font-weight:800;letter-spacing:1px;">VERIFIED</div>
                    </div>
                </div>

                <div class="cert-sign-col">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Signature_of_John_Hancock.svg" alt="Signature" style="opacity:0.7;">
                    <div class="cert-line"></div>
                    <div style="font-size:0.9rem;color:#64748B;">Direktur Akademik</div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
