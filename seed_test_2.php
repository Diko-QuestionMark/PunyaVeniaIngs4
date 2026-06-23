<?php
// seed_test_2.php
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
    // 1. Create the new Test
    $stmt = $pdo->prepare("INSERT INTO tests (title, description, test_type, time_limit, total_questions, is_published, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        'Full Test 2: Simulasi TOEFL ITP (Structure & Reading)',
        'Simulasi TOEFL ITP berisi 90 soal (40 Structure, 50 Reading). Bagian Listening (50 soal) akan ditambahkan menyusul.',
        'full',
        80, // 25 mins structure + 55 mins reading
        90,
        1
    ]);
    
    $testId = $pdo->lastInsertId();
    echo "Created new Test with ID: $testId\n";

    // 2. Load and Insert Structure Questions
    $structureJson = file_get_contents(__DIR__ . '/structure_questions.json');
    $structureQuestions = json_decode($structureJson, true);
    
    if (!$structureQuestions) {
        throw new Exception("Failed to load structure_questions.json");
    }

    // Category ID 5 for Structure (from our previous SQL query check)
    // Actually let's just query to find a valid structure category
    $stmt = $pdo->query("SELECT id FROM categories WHERE section = 'structure' LIMIT 1");
    $catStruct = $stmt->fetchColumn();
    if (!$catStruct) $catStruct = 5;

    $stmtQ = $pdo->prepare("INSERT INTO questions (section, category_id, question_text, passage_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, created_at) 
                            VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, NOW())");
                            
    $stmtTQ = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");

    $count = 0;
    foreach ($structureQuestions as $q) {
        $stmtQ->execute([
            'structure',
            $catStruct,
            $q['question_text'],
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
    echo "Inserted $count Structure questions.\n";

    // 3. Load and Insert Reading Questions
    $readingJson = file_get_contents(__DIR__ . '/reading_questions.json');
    $readingQuestions = json_decode($readingJson, true);
    
    if (!$readingQuestions) {
        throw new Exception("Failed to load reading_questions.json");
    }

    $stmt = $pdo->query("SELECT id FROM categories WHERE section = 'reading' LIMIT 1");
    $catRead = $stmt->fetchColumn();
    if (!$catRead) $catRead = 9;

    $stmtQRead = $pdo->prepare("INSERT INTO questions (section, category_id, question_text, passage_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $countRead = 0;
    foreach ($readingQuestions as $q) {
        $stmtQRead->execute([
            'reading',
            $catRead,
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
        $countRead++;
    }
    echo "Inserted $countRead Reading questions.\n";

    $pdo->commit();
    echo "Successfully seeded Full Test 2!\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
