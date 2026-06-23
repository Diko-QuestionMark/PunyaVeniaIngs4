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

$testId = 8;

// Get listening questions
$stmt = $pdo->prepare("SELECT q.id FROM test_questions tq JOIN questions q ON tq.question_id = q.id WHERE tq.test_id = ? AND q.section = 'listening' ORDER BY tq.id ASC");
$stmt->execute([$testId]);
$listening = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get structure questions
$stmt = $pdo->prepare("SELECT q.id FROM test_questions tq JOIN questions q ON tq.question_id = q.id WHERE tq.test_id = ? AND q.section = 'structure' ORDER BY tq.id ASC");
$stmt->execute([$testId]);
$structure = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get reading questions
$stmt = $pdo->prepare("SELECT q.id FROM test_questions tq JOIN questions q ON tq.question_id = q.id WHERE tq.test_id = ? AND q.section = 'reading' ORDER BY tq.id ASC");
$stmt->execute([$testId]);
$reading = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Combine in correct order
$allQuestions = array_merge($listening, $structure, $reading);

$pdo->beginTransaction();

try {
    // Delete existing mappings
    $delStmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
    $delStmt->execute([$testId]);

    // Re-insert in correct order with explicit sort_order just in case
    $insStmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id, sort_order) VALUES (?, ?, ?)");
    
    $sortOrder = 1;
    foreach ($allQuestions as $qid) {
        $insStmt->execute([$testId, $qid, $sortOrder]);
        $sortOrder++;
    }

    $pdo->commit();
    echo "Successfully reordered all " . count($allQuestions) . " questions (Listening -> Structure -> Reading).\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
