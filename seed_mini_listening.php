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

$testId = 1; // Mini Test - Listening Basics

$questions = [
    [
        'question_text' => 'Woman: "I need to drop off these books at the library before it closes." Man: "You\'d better hurry, it closes in twenty minutes." What does the man mean?',
        'option_a' => 'The library is already closed.',
        'option_b' => 'The woman has plenty of time.',
        'option_c' => 'The woman needs to leave quickly.',
        'option_d' => 'He will take the books for her.',
        'correct_answer' => 'C',
        'explanation' => 'The man says "You\'d better hurry," indicating that the woman must leave quickly to make it before the library closes.'
    ],
    [
        'question_text' => 'Man: "I think the heating system is broken. It\'s freezing in here." Woman: "Tell me about it! I\'m wearing two sweaters." What does the woman mean?',
        'option_a' => 'She wants the man to fix the heater.',
        'option_b' => 'She agrees that it is very cold.',
        'option_c' => 'She wants to buy a new sweater.',
        'option_d' => 'She doesn\'t feel cold at all.',
        'correct_answer' => 'B',
        'explanation' => 'The idiom "Tell me about it" means that the speaker strongly agrees with what was just said.'
    ],
    [
        'question_text' => 'Woman: "Have you seen my blue pen? I can\'t find it anywhere on my desk." Man: "Check the drawer next to the printer. I saw one there earlier." What does the man suggest?',
        'option_a' => 'The woman should use a pencil.',
        'option_b' => 'The pen might be in the drawer.',
        'option_c' => 'He borrowed the pen earlier.',
        'option_d' => 'The printer needs ink.',
        'correct_answer' => 'B',
        'explanation' => 'The man suggests checking the drawer, implying the pen might be there.'
    ],
    [
        'question_text' => 'Man: "Are you going to the seminar on marketing strategies tomorrow?" Woman: "I wish I could, but I have a doctor\'s appointment." What does the woman mean?',
        'option_a' => 'She will attend the seminar.',
        'option_b' => 'She is presenting at the seminar.',
        'option_c' => 'She cannot attend the seminar.',
        'option_d' => 'She is a doctor.',
        'correct_answer' => 'C',
        'explanation' => '"I wish I could, but..." indicates an inability to do something.'
    ],
    [
        'question_text' => 'Woman: "I thought the meeting was supposed to start at 10:00." Man: "It was pushed back an hour." What does the man mean?',
        'option_a' => 'The meeting started early.',
        'option_b' => 'The meeting is canceled.',
        'option_c' => 'The meeting will start at 11:00.',
        'option_d' => 'The meeting was moved to a different room.',
        'correct_answer' => 'C',
        'explanation' => '"Pushed back an hour" means delayed by one hour, so 10:00 becomes 11:00.'
    ],
    [
        'question_text' => 'Man: "I really struggled with the math assignment." Woman: "You and me both." What does the woman mean?',
        'option_a' => 'She also found the assignment difficult.',
        'option_b' => 'She wants to help the man.',
        'option_c' => 'She finished the assignment easily.',
        'option_d' => 'She hasn\'t started the assignment yet.',
        'correct_answer' => 'A',
        'explanation' => 'The phrase "You and me both" is used to say that you have the same problem or experience as someone else.'
    ],
    [
        'question_text' => 'Woman: "Do we need to buy more printer paper?" Man: "We have a few reams left in the supply closet." What does the man imply?',
        'option_a' => 'They urgently need to buy paper.',
        'option_b' => 'They have enough paper for now.',
        'option_c' => 'The printer is out of ink.',
        'option_d' => 'He will go buy paper immediately.',
        'correct_answer' => 'B',
        'explanation' => 'Saying they have a few reams left implies there is no immediate need to buy more.'
    ],
    [
        'question_text' => 'Man: "Did Jane finish the report?" Woman: "She was working on it when I left the office." What does the woman mean?',
        'option_a' => 'Jane has already finished the report.',
        'option_b' => 'She does not know if Jane finished it.',
        'option_c' => 'She helped Jane finish the report.',
        'option_d' => 'Jane decided not to do the report.',
        'correct_answer' => 'B',
        'explanation' => 'The woman only knows Jane was working on it when she left, implying she doesn\'t know the final status.'
    ],
    [
        'question_text' => 'Woman: "I can\'t get my computer to start." Man: "Have you checked if it\'s plugged in?" What does the man imply?',
        'option_a' => 'The computer is broken.',
        'option_b' => 'The power cord might be disconnected.',
        'option_c' => 'He will buy a new computer.',
        'option_d' => 'The woman should call IT support.',
        'correct_answer' => 'B',
        'explanation' => 'Asking if it\'s plugged in implies the most basic power connection issue might be the cause.'
    ],
    [
        'question_text' => 'Man: "The traffic was terrible this morning." Woman: "It took me twice as long to get to work." What can be inferred from the conversation?',
        'option_a' => 'They both experienced bad traffic.',
        'option_b' => 'The woman lives close to work.',
        'option_c' => 'The man arrived early.',
        'option_d' => 'They carpooled together.',
        'correct_answer' => 'A',
        'explanation' => 'Both complain about the traffic and the delay it caused, meaning both experienced it.'
    ]
];

$pdo->beginTransaction();

try {
    // Delete existing questions mapped to test 1
    $delStmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
    $delStmt->execute([$testId]);

    // Get category ID for listening
    $stmt = $pdo->query("SELECT id FROM categories WHERE section = 'listening' LIMIT 1");
    $catId = $stmt->fetchColumn();
    if (!$catId) $catId = 1;

    $stmtQ = $pdo->prepare("INSERT INTO questions (section, category_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'easy', NOW())");
                            
    $stmtTQ = $pdo->prepare("INSERT INTO test_questions (test_id, question_id, sort_order) VALUES (?, ?, ?)");

    $sortOrder = 1;
    foreach ($questions as $q) {
        $stmtQ->execute([
            'listening',
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
    echo "Successfully generated and inserted 10 new Listening questions into Mini Test 1.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
