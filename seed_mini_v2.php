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

$pdo->beginTransaction();

try {
    // 1. Insert 3 new tests
    $stmtTest = $pdo->prepare("INSERT INTO tests (title, description, test_type, time_limit, total_questions, is_published, created_at) VALUES (?, ?, 'mini', ?, 10, 1, NOW())");
    
    // Mini Test v2 - Listening
    $stmtTest->execute(['Mini Test - Listening Basics v2', 'Latihan Listening TOEFL bagian pendek part 2. Terdiri dari 10 soal.', 15]);
    $testIdList = $pdo->lastInsertId();
    
    // Mini Test v2 - Structure
    $stmtTest->execute(['Mini Test - Structure Basics v2', 'Latihan tata bahasa dan structure TOEFL part 2. Terdiri dari 10 soal.', 15]);
    $testIdStruc = $pdo->lastInsertId();
    
    // Mini Test v2 - Reading
    $stmtTest->execute(['Mini Test - Reading Comprehension v2', 'Latihan pemahaman bacaan TOEFL part 2. Terdiri dari 10 soal.', 20]);
    $testIdRead = $pdo->lastInsertId();

    // Prepare Question Insert
    $stmtQ = $pdo->prepare("INSERT INTO questions (section, category_id, question_text, passage_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'medium', NOW())");
    $stmtTQ = $pdo->prepare("INSERT INTO test_questions (test_id, question_id, sort_order) VALUES (?, ?, ?)");

    // Get categories
    $catList = $pdo->query("SELECT id FROM categories WHERE section = 'listening' LIMIT 1")->fetchColumn() ?: 1;
    $catStruc = $pdo->query("SELECT id FROM categories WHERE section = 'structure' LIMIT 1")->fetchColumn() ?: 2;
    $catRead = $pdo->query("SELECT id FROM categories WHERE section = 'reading' LIMIT 1")->fetchColumn() ?: 3;

    // --- LISTENING V2 QUESTIONS ---
    $listQ = [
        ['Man: "Are you sure you want to take the 8 AM flight?" Woman: "It\'s the only way I\'ll make the conference on time." What does the woman mean?', null, 'She prefers flying later in the day.', 'She must take the early flight to be punctual.', 'The conference starts at 8 AM.', 'She missed her flight.', 'B', 'She implies that taking the 8 AM flight is necessary to arrive at the conference on time.'],
        ['Woman: "Can you review my essay tonight?" Man: "I\'d love to, but I have to finish my own project first." What does the man mean?', null, 'He will review her essay right now.', 'He is unable to help her tonight.', 'He finished his project already.', 'He wants her to review his project.', 'B', '"I\'d love to, but..." indicates he cannot do it due to a prior commitment.'],
        ['Man: "The weather forecast says it will rain all weekend." Woman: "There goes our plan for the picnic." What does the woman imply?', null, 'They will have the picnic in the rain.', 'They need to cancel the picnic.', 'The weather forecast is wrong.', 'They should go to a different park.', 'B', '"There goes our plan" implies that the plan is now ruined and must be canceled.'],
        ['Woman: "I can\'t believe the concert is completely sold out." Man: "I told you we should have bought the tickets last week." What does the man mean?', null, 'He bought the tickets last week.', 'They shouldn\'t go to the concert.', 'He regrets that they delayed buying the tickets.', 'The concert is not sold out.', 'C', 'He implies that waiting to buy the tickets was a mistake.'],
        ['Man: "Did you hear that Professor Adams is retiring?" Woman: "I know! It won\'t be the same around here without him." What does the woman mean?', null, 'Professor Adams is not well-liked.', 'Professor Adams is taking a sabbatical.', 'She will miss Professor Adams.', 'She is replacing Professor Adams.', 'C', '"It won\'t be the same" means she will miss him and his presence.'],
        ['Woman: "Is the new software difficult to learn?" Man: "It\'s a piece of cake once you get the hang of it." What does the man mean?', null, 'The software is very complicated.', 'The software is easy after some practice.', 'He wants some cake.', 'He doesn\'t know how to use it.', 'B', '"A piece of cake" means something is very easy.'],
        ['Man: "I\'m exhausted. I stayed up until 3 AM studying for the biology test." Woman: "Why didn\'t you start studying earlier?" What does the woman imply?', null, 'The man should have managed his time better.', 'The biology test is easy.', 'She also stayed up late studying.', 'The man shouldn\'t take the test.', 'A', 'She is suggesting that starting earlier would have prevented his exhaustion.'],
        ['Woman: "Do you know where the nearest post office is?" Man: "There\'s one just around the corner, next to the bank." What does the man suggest?', null, 'The post office is far away.', 'The post office is currently closed.', 'The post office is very close by.', 'The bank is the post office.', 'C', '"Just around the corner" implies it is very close.'],
        ['Man: "I think we took a wrong turn. This doesn\'t look like the way to the museum." Woman: "Let\'s pull over and check the map." What does the woman suggest?', null, 'They should keep driving.', 'They should stop the car and consult the map.', 'They should go back home.', 'They should ask someone for directions.', 'B', '"Pull over" means to stop the car by the side of the road.'],
        ['Woman: "The printer is jammed again." Man: "Not again! I\'ll call the technician right away." What can be inferred from the man\'s response?', null, 'The printer has never jammed before.', 'He knows how to fix the printer.', 'This is a recurring problem with the printer.', 'He doesn\'t want to fix the printer.', 'C', '"Not again!" indicates that this has happened before and is a frustrating recurrence.']
    ];
    $order = 1;
    foreach ($listQ as $q) {
        $stmtQ->execute(['listening', $catList, $q[0], $q[1], $q[2], $q[3], $q[4], $q[5], $q[6], $q[7]]);
        $stmtTQ->execute([$testIdList, $pdo->lastInsertId(), $order++]);
    }

    // --- STRUCTURE V2 QUESTIONS ---
    $strucQ = [
        ['Not only __________ a great musician, but she is also an accomplished painter.', null, 'is she', 'she is', 'was she', 'she was', 'A', 'Sentences beginning with negative adverbials like "Not only" require subject-verb inversion (is she).'],
        ['__________ the fact that it was raining heavily, they decided to proceed with the outdoor event.', null, 'Although', 'Despite', 'However', 'Because', 'B', '"Despite" is a preposition that takes a noun phrase ("the fact that..."), whereas "Although" requires a full clause.'],
        ['If I __________ you, I would take that job offer immediately.', null, 'was', 'am', 'were', 'have been', 'C', 'In the second conditional, the subjunctive mood uses "were" for all subjects (If I were you).'],
        ['The new shopping mall, __________ took three years to build, is finally opening next week.', null, 'that', 'which', 'who', 'where', 'B', 'In non-restrictive relative clauses set off by commas, "which" must be used instead of "that" for things.'],
        ['By the end of this century, scientists __________ a cure for many currently incurable diseases.', null, 'will discover', 'are discovering', 'will have discovered', 'discovered', 'C', '"By the end of..." indicates an action completed before a future time, requiring the future perfect tense ("will have discovered").'],
        ['Rarely __________ such a spectacular display of meteor showers in this part of the world.', null, 'we see', 'do we see', 'we have seen', 'have we see', 'B', 'Sentences starting with restrictive/negative adverbs like "Rarely" require inversion (do we see).'],
        ['The professor insisted that every student __________ their assignment by Friday noon.', null, 'submit', 'submits', 'submitted', 'submitting', 'A', 'Verbs like "insist" trigger the subjunctive mood, requiring the base form of the verb ("submit") for all subjects.'],
        ['Scarcely had the speaker finished his presentation __________ the audience erupted in applause.', null, 'when', 'than', 'that', 'then', 'A', 'The correlative conjunction used with "Scarcely" is "when" (Scarcely had... when...).'],
        ['The more you practice speaking English, __________ you will become at it.', null, 'the better', 'better', 'the best', 'best', 'A', 'This uses the comparative parallel structure: "The more..., the better...".'],
        ['It is essential that the patient __________ the medication exactly as prescribed.', null, 'takes', 'take', 'took', 'taking', 'B', 'Phrases like "It is essential that" trigger the subjunctive mood, requiring the base verb ("take").']
    ];
    $order = 1;
    foreach ($strucQ as $q) {
        $stmtQ->execute(['structure', $catStruc, $q[0], $q[1], $q[2], $q[3], $q[4], $q[5], $q[6], $q[7]]);
        $stmtTQ->execute([$testIdStruc, $pdo->lastInsertId(), $order++]);
    }

    // --- READING V2 QUESTIONS ---
    $passage = "The phenomenon of bioluminescence, the emission of light by living organisms, is widely distributed in the marine environment. It is estimated that up to 90% of deep-sea marine life produces bioluminescence in one form or another. Unlike the sunlight that illuminates the ocean surface, this biological light is generated by a chemical reaction within the organism. This reaction involves a light-emitting molecule called luciferin and an enzyme called luciferase. When luciferin reacts with oxygen, aided by luciferase, energy is released in the form of cold light.\n\nBioluminescence serves a variety of crucial functions in the dark depths of the ocean. For some species, such as the anglerfish, it acts as a lure to attract prey. The anglerfish dangles a glowing appendage in front of its mouth, drawing in curious smaller fish. For others, like certain species of squid, bioluminescence is used as a defense mechanism. When threatened, they release a glowing cloud of bioluminescent chemicals to confuse predators and escape into the darkness. Additionally, many organisms use specific light patterns for communication and mate attraction, ensuring species recognition in the vast, featureless deep sea.";
    $readQ = [
        ['What is the primary topic of the passage?', $passage, 'The diet of deep-sea marine life.', 'The mechanism and functions of bioluminescence.', 'How sunlight affects ocean organisms.', 'The mating habits of the anglerfish.', 'B', 'The passage defines bioluminescence, explains its chemical process, and details its various functions in marine life.'],
        ['According to the passage, what percentage of deep-sea marine life produces bioluminescence?', $passage, 'Less than 10%', 'Exactly 50%', 'Up to 90%', '100%', 'C', 'The text explicitly states: "It is estimated that up to 90% of deep-sea marine life produces bioluminescence..."'],
        ['Which two substances are required to produce bioluminescence according to the text?', $passage, 'Sunlight and oxygen.', 'Luciferin and luciferase.', 'Anglerfish and squid.', 'Carbon dioxide and cold light.', 'B', 'The text states: "This reaction involves a light-emitting molecule called luciferin and an enzyme called luciferase."'],
        ['The word "illuminates" in paragraph 1 is closest in meaning to:', $passage, 'Heats up', 'Darkens', 'Lights up', 'Destroys', 'C', '"Illuminates" means to supply with light or to light up.'],
        ['How does the anglerfish use bioluminescence?', $passage, 'To communicate with other anglerfish.', 'To confuse its predators.', 'To attract prey to its mouth.', 'To navigate through the dark ocean.', 'C', 'The text says: "...it acts as a lure to attract prey. The anglerfish dangles a glowing appendage in front of its mouth..."'],
        ['Based on the passage, how do certain squids use bioluminescence for defense?', $passage, 'They flash bright lights to blind predators.', 'They release a glowing cloud to confuse predators.', 'They camouflage themselves to match the glowing water.', 'They produce a loud noise accompanied by light.', 'B', 'The text states: "...they release a glowing cloud of bioluminescent chemicals to confuse predators..."'],
        ['The light produced by bioluminescence is described as:', $passage, 'Hot light', 'Cold light', 'Sunlight', 'Ultraviolet light', 'B', 'The passage says: "...energy is released in the form of cold light."'],
        ['What is one reason organisms use specific light patterns?', $passage, 'To mark their territory.', 'To warm up the surrounding water.', 'For communication and mate attraction.', 'To signal humans.', 'C', 'The text states: "...many organisms use specific light patterns for communication and mate attraction..."'],
        ['What does the word "featureless" in the last sentence imply about the deep sea?', $passage, 'It is full of interesting landmarks.', 'It is very colorful.', 'It lacks distinct visual landmarks or characteristics.', 'It is highly populated.', 'C', '"Featureless" means lacking distinct parts or characteristics, emphasizing the uniform darkness of the deep sea.'],
        ['Which of the following is NOT mentioned as a function of bioluminescence?', $passage, 'Attracting prey', 'Defense mechanism', 'Mate attraction', 'Photosynthesis', 'D', 'Attracting prey, defense, and mate attraction are all explicitly mentioned. Photosynthesis is not.']
    ];
    $order = 1;
    foreach ($readQ as $q) {
        $stmtQ->execute(['reading', $catRead, $q[0], $q[1], $q[2], $q[3], $q[4], $q[5], $q[6], $q[7]]);
        $stmtTQ->execute([$testIdRead, $pdo->lastInsertId(), $order++]);
    }

    $pdo->commit();
    echo "Successfully generated Mini Tests v2 (IDs: $testIdList, $testIdStruc, $testIdRead) with 30 new questions total.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
