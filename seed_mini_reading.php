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

$testId = 3; // Mini Test - Reading Comprehension

$passage = "Coral reefs are among the most diverse and biologically complex ecosystems on Earth. Often referred to as the \"rainforests of the sea,\" they occupy less than 0.1% of the world's ocean surface, yet they provide a home for at least 25% of all marine species. Reefs are built by colonies of tiny animals found in marine waters that contain few nutrients. Most coral reefs are built from stony corals, which in turn consist of polyps that cluster in groups. The polyps belong to a group of animals known as Cnidaria, which also includes sea anemones and jellyfish. \n\nCoral polyps secrete hard carbonate exoskeletons which support and protect the coral polyps. Reefs grow best in warm, shallow, clear, sunny and agitated waters. However, coral reefs are incredibly fragile. Because they are highly sensitive to changes in water temperature, they are under threat from climate change, ocean acidification, blast fishing, and runoff from agricultural activities. When water temperatures rise excessively, corals expel the symbiotic algae living in their tissues, causing them to turn completely white. This phenomenon, known as coral bleaching, does not immediately kill the coral, but it leaves them severely weakened and highly susceptible to disease and starvation.";

$questions = [
    [
        'question_text' => 'What is the main topic of the passage?',
        'option_a' => 'The effects of climate change on ocean temperatures.',
        'option_b' => 'The biological composition and fragility of coral reefs.',
        'option_c' => 'How marine species migrate between different oceans.',
        'option_d' => 'The comparison between rainforests and oceans.',
        'correct_answer' => 'B',
        'explanation' => 'The passage primarily discusses what coral reefs are made of, their biodiversity, and the threats they face.'
    ],
    [
        'question_text' => 'According to the passage, why are coral reefs called the "rainforests of the sea"?',
        'option_a' => 'They grow near tropical rainforests.',
        'option_b' => 'They require a lot of rainfall to survive.',
        'option_c' => 'They host a massive diversity of marine life.',
        'option_d' => 'They cover a large percentage of the earth\'s surface.',
        'correct_answer' => 'C',
        'explanation' => 'They are called rainforests of the sea because "they provide a home for at least 25% of all marine species," indicating high biodiversity.'
    ],
    [
        'question_text' => 'The word "they" in the first paragraph refers to:',
        'option_a' => 'Rainforests',
        'option_b' => 'Marine species',
        'option_c' => 'Ocean surfaces',
        'option_d' => 'Coral reefs',
        'correct_answer' => 'D',
        'explanation' => 'The sentence "Often referred to as the \'rainforests of the sea,\' they occupy..." clearly refers back to coral reefs.'
    ],
    [
        'question_text' => 'Which of the following animals is NOT in the same biological group as coral polyps?',
        'option_a' => 'Sea anemones',
        'option_b' => 'Jellyfish',
        'option_c' => 'Stony corals',
        'option_d' => 'Fish',
        'correct_answer' => 'D',
        'explanation' => 'The passage states polyps belong to Cnidaria, which includes sea anemones and jellyfish. Fish are not mentioned in this group.'
    ],
    [
        'question_text' => 'Based on the passage, what is the primary function of the carbonate exoskeletons?',
        'option_a' => 'To attract marine species to the reef.',
        'option_b' => 'To help the coral absorb nutrients from the water.',
        'option_c' => 'To support and protect the coral polyps.',
        'option_d' => 'To regulate the water temperature around the coral.',
        'correct_answer' => 'C',
        'explanation' => 'The text explicitly states: "Coral polyps secrete hard carbonate exoskeletons which support and protect the coral polyps."'
    ],
    [
        'question_text' => 'The word "agitated" in paragraph 2 is closest in meaning to:',
        'option_a' => 'Angry',
        'option_b' => 'Calm',
        'option_c' => 'Moving',
        'option_d' => 'Polluted',
        'correct_answer' => 'C',
        'explanation' => 'In the context of water, "agitated" means moving or turbulent, which helps corals get food and oxygen.'
    ],
    [
        'question_text' => 'What directly causes coral bleaching according to the passage?',
        'option_a' => 'Blast fishing near the reefs.',
        'option_b' => 'Agricultural runoff polluting the water.',
        'option_c' => 'Corals expelling their symbiotic algae due to high temperatures.',
        'option_d' => 'Excessive sunlight hitting shallow waters.',
        'correct_answer' => 'C',
        'explanation' => 'The text says: "When water temperatures rise excessively, corals expel the symbiotic algae living in their tissues, causing them to turn completely white."'
    ],
    [
        'question_text' => 'Which of the following is NOT listed as a threat to coral reefs?',
        'option_a' => 'Ocean acidification',
        'option_b' => 'Overpopulation of fish',
        'option_c' => 'Climate change',
        'option_d' => 'Agricultural runoff',
        'correct_answer' => 'B',
        'explanation' => 'Climate change, ocean acidification, blast fishing, and runoff are listed. Overpopulation of fish is not mentioned.'
    ],
    [
        'question_text' => 'What happens immediately after a coral bleaches?',
        'option_a' => 'It dies instantly.',
        'option_b' => 'It regains its color within a few days.',
        'option_c' => 'It moves to deeper, cooler water.',
        'option_d' => 'It becomes weakened and vulnerable to disease.',
        'correct_answer' => 'D',
        'explanation' => 'The text states that bleaching "does not immediately kill the coral, but it leaves them severely weakened and highly susceptible to disease and starvation."'
    ],
    [
        'question_text' => 'What can be inferred about the waters where coral reefs thrive?',
        'option_a' => 'They are deep and nutrient-rich.',
        'option_b' => 'They are shallow and relatively nutrient-poor.',
        'option_c' => 'They are dark and cold.',
        'option_d' => 'They are located near river mouths with high agricultural runoff.',
        'correct_answer' => 'B',
        'explanation' => 'The passage states reefs are built in "waters that contain few nutrients" and grow best in "warm, shallow, clear, sunny" waters.'
    ]
];

$pdo->beginTransaction();

try {
    // Delete existing questions mapped to test 3
    $delStmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
    $delStmt->execute([$testId]);

    // Get category ID for reading
    $stmt = $pdo->query("SELECT id FROM categories WHERE section = 'reading' LIMIT 1");
    $catId = $stmt->fetchColumn();
    if (!$catId) $catId = 3; // Fallback

    $stmtQ = $pdo->prepare("INSERT INTO questions (section, category_id, question_text, passage_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'easy', NOW())");
                            
    $stmtTQ = $pdo->prepare("INSERT INTO test_questions (test_id, question_id, sort_order) VALUES (?, ?, ?)");

    $sortOrder = 1;
    foreach ($questions as $q) {
        $stmtQ->execute([
            'reading',
            $catId,
            $q['question_text'],
            $passage,
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
    $pdo->prepare("UPDATE tests SET total_questions = 10, time_limit = 20 WHERE id = ?")->execute([$testId]);

    $pdo->commit();
    echo "Successfully generated and inserted 10 new Reading questions into Mini Test 3.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
