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
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$pdo->beginTransaction();

try {
    $testId = 8; // The current Test ID for Full Test 2

    // Update total questions to 140
    $stmt = $pdo->prepare("UPDATE tests SET total_questions = 140, description = 'Simulasi TOEFL ITP lengkap berisi 140 soal (50 Listening, 40 Structure, 50 Reading).' WHERE id = ?");
    $stmt->execute([$testId]);

    // Load and Insert Listening Questions
    $listeningJson = file_get_contents(__DIR__ . '/listening_questions.json');
    $listeningQuestions = json_decode($listeningJson, true);
    
    if (!$listeningQuestions) {
        throw new Exception("Failed to load listening_questions.json");
    }

    $stmt = $pdo->query("SELECT id FROM categories WHERE section = 'listening' LIMIT 1");
    $catList = $stmt->fetchColumn();
    if (!$catList) $catList = 1; // Default to 1 if not found

    $stmtQ = $pdo->prepare("INSERT INTO questions (section, category_id, question_text, passage_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                            
    $stmtTQ = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");

    $count = 0;
    foreach ($listeningQuestions as $q) {
        $stmtQ->execute([
            'listening',
            $catList,
            $q['question_text'],
            $q['passage_text'] ?? NULL,
            $q['option_a'],
            $q['option_b'],
            $q['option_c'],
            $q['option_d'],
            $q['correct_answer'],
            $q['explanation'],
            $q['difficulty'] ?? 'medium'
        ]);
        $qId = $pdo->lastInsertId();
        $stmtTQ->execute([$testId, $qId]);
        $count++;
    }
    echo "Inserted $count Listening questions into Test ID $testId.\n";

    $pdo->commit();
    echo "Successfully updated Full Test 2 to 140 questions!\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
