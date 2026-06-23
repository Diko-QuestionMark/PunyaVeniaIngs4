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

$testId = 2; // Mini Test - Structure Basics

$questions = [
    [
        'question_text' => 'The majestic mountains __________ the city provide a breathtaking backdrop.',
        'option_a' => 'surround',
        'option_b' => 'surrounded',
        'option_c' => 'surrounding',
        'option_d' => 'surrounds',
        'correct_answer' => 'C',
        'explanation' => '"Surrounding" is an active participle modifying "mountains," creating a reduced relative clause (that surround).'
    ],
    [
        'question_text' => 'Not until the late 19th century __________ a common part of everyday life in the city.',
        'option_a' => 'did electricity become',
        'option_b' => 'electricity became',
        'option_c' => 'became electricity',
        'option_d' => 'electricity has become',
        'correct_answer' => 'A',
        'explanation' => 'Sentences beginning with negative adverbial phrases like "Not until" require subject-auxiliary inversion (did + subject + verb).'
    ],
    [
        'question_text' => 'Many scientists believe that the global climate __________ drastically over the next few decades.',
        'option_a' => 'will change',
        'option_b' => 'changes',
        'option_c' => 'has changed',
        'option_d' => 'changed',
        'correct_answer' => 'A',
        'explanation' => '"Over the next few decades" refers to the future, so the future tense "will change" is required.'
    ],
    [
        'question_text' => 'The committee recommended that the new policy __________ implemented immediately to prevent further delays.',
        'option_a' => 'is',
        'option_b' => 'be',
        'option_c' => 'was',
        'option_d' => 'will be',
        'correct_answer' => 'B',
        'explanation' => 'Verbs like "recommend" trigger the subjunctive mood in a "that" clause, which uses the base form of the verb ("be").'
    ],
    [
        'question_text' => 'Had the researchers known about the anomaly earlier, they __________ the entire experiment.',
        'option_a' => 'would alter',
        'option_b' => 'will alter',
        'option_c' => 'would have altered',
        'option_d' => 'altered',
        'correct_answer' => 'C',
        'explanation' => 'This is a third conditional sentence with inverted syntax ("Had they known"). The main clause requires "would have + past participle".'
    ],
    [
        'question_text' => '__________ the fierce storm, the ship managed to reach the harbor safely.',
        'option_a' => 'Although',
        'option_b' => 'Because of',
        'option_c' => 'Despite',
        'option_d' => 'However',
        'correct_answer' => 'C',
        'explanation' => '"Despite" is a preposition used before a noun phrase ("the fierce storm") to express contrast. "Although" requires a full clause.'
    ],
    [
        'question_text' => 'The novel, written by a relatively unknown author, __________ a bestseller almost overnight.',
        'option_a' => 'became',
        'option_b' => 'becoming',
        'option_c' => 'has become',
        'option_d' => 'to become',
        'correct_answer' => 'A',
        'explanation' => 'The sentence needs a main verb for the subject "The novel". "Became" fits the past context.'
    ],
    [
        'question_text' => 'By the time we arrive at the theater, the performance __________, so we must hurry.',
        'option_a' => 'will start',
        'option_b' => 'will have started',
        'option_c' => 'started',
        'option_d' => 'has started',
        'correct_answer' => 'B',
        'explanation' => '"By the time" in a future context is followed by the future perfect tense ("will have started") to show an action completed before a certain time.'
    ],
    [
        'question_text' => 'The new regulations require that all employees __________ safety gear while on the factory floor.',
        'option_a' => 'wearing',
        'option_b' => 'wears',
        'option_c' => 'wear',
        'option_d' => 'to wear',
        'correct_answer' => 'C',
        'explanation' => '"Require" triggers the subjunctive mood in the following "that" clause, so the base form "wear" is used for all subjects.'
    ],
    [
        'question_text' => 'Neither the manager nor the employees __________ aware of the upcoming changes to the company structure.',
        'option_a' => 'was',
        'option_b' => 'were',
        'option_c' => 'is',
        'option_d' => 'are being',
        'correct_answer' => 'B',
        'explanation' => 'In a "neither... nor" structure, the verb agrees with the noun closest to it. "Employees" is plural, so "were" is correct for past tense (or "are" for present, but "were" fits the options best without conflict).'
    ]
];

$pdo->beginTransaction();

try {
    // Delete existing questions mapped to test 2
    $delStmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
    $delStmt->execute([$testId]);

    // Get category ID for structure
    $stmt = $pdo->query("SELECT id FROM categories WHERE section = 'structure' LIMIT 1");
    $catId = $stmt->fetchColumn();
    if (!$catId) $catId = 2; // Fallback

    $stmtQ = $pdo->prepare("INSERT INTO questions (section, category_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'easy', NOW())");
                            
    $stmtTQ = $pdo->prepare("INSERT INTO test_questions (test_id, question_id, sort_order) VALUES (?, ?, ?)");

    $sortOrder = 1;
    foreach ($questions as $q) {
        $stmtQ->execute([
            'structure',
            $catId,
            $q['question_text'],
            $q['option_a'],
            $q['option_b'],
            $q['option_c'],
            $q['option_d'],
            $q['correct_answer'],
            $q['explanation']
        ]);
        
        $qId = $pdo->lastInsertId();
        
        $stmtTQ->execute([$testId, $qId, $sortOrder]);
        $sortOrder++;
    }

    // Ensure test is updated
    $pdo->prepare("UPDATE tests SET total_questions = 10, time_limit = 15 WHERE id = ?")->execute([$testId]);

    $pdo->commit();
    echo "Successfully generated and inserted 10 new Structure questions into Mini Test 2.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
