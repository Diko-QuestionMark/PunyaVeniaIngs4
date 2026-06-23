<?php
$host = '127.0.0.1';
$db   = 'toefl_platform';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die($e->getMessage());
}

$firstNames = ['Budi', 'Andi', 'Siti', 'Ayu', 'Rizky', 'Dimas', 'Dwi', 'Nanda', 'Putra', 'Putri', 'Agus', 'Tari', 'Eko', 'Sari', 'Bayu', 'Mega', 'Indra', 'Fitri', 'Hendra', 'Dian', 'Fajar', 'Ratna', 'Aditya', 'Maya', 'Wahyu'];
$lastNames = ['Pratama', 'Wijaya', 'Sari', 'Kusuma', 'Saputra', 'Wahyuni', 'Setiawan', 'Nugroho', 'Lestari', 'Hidayat', 'Wibowo', 'Santoso', 'Gunawan', 'Kurniawan', 'Rahayu', 'Siregar', 'Simanjuntak', 'Pangestu', 'Utami', 'Mahendra'];

$domain = 'toeflmaster.com';
$passwordHash = password_hash('password123', PASSWORD_DEFAULT);

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, created_at) VALUES (?, ?, ?, ?, NOW() - INTERVAL FLOOR(RAND() * 365) DAY)");
    
    $insertedCount = 0;
    
    for ($i = 0; $i < 1000; $i++) {
        $fName = $firstNames[array_rand($firstNames)];
        $lName = $lastNames[array_rand($lastNames)];
        
        $fullName = $fName . ' ' . $lName;
        
        // Generate a unique-ish username
        $baseUser = strtolower($fName . $lName);
        $username = $baseUser . '_' . rand(1000, 99999) . $i;
        
        $email = $username . '@' . $domain;
        
        $stmt->execute([$username, $email, $passwordHash, $fullName]);
        $insertedCount++;
    }
    
    $pdo->commit();
    echo "Successfully inserted $insertedCount dummy users into the database.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error inserting users: " . $e->getMessage() . "\n";
}
