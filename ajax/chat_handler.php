<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

// Ensure user is logged in
if (!isUserLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login untuk menggunakan fitur ini.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty(trim($userMessage))) {
    echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.']);
    exit;
}

// Check if API Key is set
if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'ISI_API_KEY_GEMINI_ANDA_DI_SINI' || empty(GEMINI_API_KEY)) {
    echo json_encode(['status' => 'error', 'message' => 'Sistem belum terhubung ke otak AI (API Key Gemini belum dikonfigurasi di config.php). Hubungi Administrator.']);
    exit;
}

$apiKey = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$systemInstruction = "Anda adalah AI Tutor TOEFL yang ramah, profesional, dan ahli dalam materi bahasa Inggris (Listening, Structure & Written Expression, Reading). Jawab pertanyaan pengguna dengan jelas, terstruktur, dan mudah dipahami. Gunakan bahasa Indonesia. Format jawaban bisa menggunakan markdown (bold, italic, list). Jangan memberikan jawaban yang terlalu panjang dan bertele-tele.";

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $userMessage]
            ]
        ]
    ],
    "systemInstruction" => [
        "parts" => [
            ["text" => $systemInstruction]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi ke AI gagal: ' . $error]);
    exit;
}

$resData = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiText = $resData['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['status' => 'success', 'message' => $aiText]);
} else {
    // Handle API error gracefully
    $errMsg = $resData['error']['message'] ?? 'Respons tidak diketahui dari API.';
    echo json_encode(['status' => 'error', 'message' => 'Maaf, AI sedang mengalami kendala: ' . $errMsg]);
}
?>
