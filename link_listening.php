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

$test1Id = 6;
$test2Id = 8;

$pdo->beginTransaction();

try {
    // Get listening questions from Test 1
    $stmt = $pdo->prepare("SELECT q.id FROM test_questions tq JOIN questions q ON tq.question_id = q.id WHERE tq.test_id = ? AND q.section = 'listening' ORDER BY tq.sort_order ASC, tq.id ASC");
    $stmt->execute([$test1Id]);
    $listening = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get structure questions from Test 2
    $stmt = $pdo->prepare("SELECT q.id FROM test_questions tq JOIN questions q ON tq.question_id = q.id WHERE tq.test_id = ? AND q.section = 'structure' ORDER BY tq.sort_order ASC, tq.id ASC");
    $stmt->execute([$test2Id]);
    $structure = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get reading questions from Test 2
    $stmt = $pdo->prepare("SELECT q.id FROM test_questions tq JOIN questions q ON tq.question_id = q.id WHERE tq.test_id = ? AND q.section = 'reading' ORDER BY tq.sort_order ASC, tq.id ASC");
    $stmt->execute([$test2Id]);
    $reading = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $allQuestions = array_merge($listening, $structure, $reading);

    // Delete existing mappings for Test 2
    $delStmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
    $delStmt->execute([$test2Id]);

    // Re-insert in correct order with explicit sort_order
    $insStmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id, sort_order) VALUES (?, ?, ?)");
    
    $sortOrder = 1;
    foreach ($allQuestions as $qid) {
        $insStmt->execute([$test2Id, $qid, $sortOrder]);
        $sortOrder++;
    }

    // Update the test total
    $updStmt = $pdo->prepare("UPDATE tests SET total_questions = ?, description = 'Simulasi TOEFL ITP lengkap berisi 140 soal (50 Listening, 40 Structure, 50 Reading).' WHERE id = ?");
    $updStmt->execute([count($allQuestions), $test2Id]);

    $pdo->commit();
    echo "Successfully linked " . count($listening) . " Listening questions from Test 1 to Test 2.\n";
    echo "Total questions in Test 2 is now " . count($allQuestions) . ".\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
