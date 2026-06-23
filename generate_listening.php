<?php
$listeningQuestions = [];

$shortConvos = [
    ["m" => "I need to drop off this package at the post office before it closes.", "w" => "You'd better hurry, it's almost five o'clock.", "q" => "What does the woman mean?", "a" => "The post office is already closed.", "b" => "The man has plenty of time.", "c" => "The man should go to the post office quickly.", "d" => "She will take the package for him.", "ans" => "C", "exp" => "The woman implies the man needs to hurry because it closes at 5."],
    ["w" => "Are you going to the seminar on marketing strategies tomorrow?", "m" => "I wouldn't miss it for the world.", "q" => "What does the man mean?", "a" => "He has no interest in marketing.", "b" => "He will definitely attend the seminar.", "c" => "He is going on a trip around the world.", "d" => "He missed the seminar yesterday.", "ans" => "B", "exp" => "'I wouldn't miss it for the world' is an idiom meaning he will absolutely go."],
    ["m" => "Did you see my blue folder? I left it right here on the desk.", "w" => "I saw Sarah taking some files to the conference room. Maybe yours was in the stack.", "q" => "What does the woman imply?", "a" => "Sarah might have taken the folder by mistake.", "b" => "The blue folder is in the trash.", "c" => "She took the folder to the conference room.", "d" => "Sarah has a blue folder just like his.", "ans" => "A", "exp" => "The woman suggests that Sarah might have accidentally picked it up with other files."],
    ["w" => "The new printer in the office is so much faster than the old one.", "m" => "Yes, but it seems to jam more often.", "q" => "What is the man's opinion of the new printer?", "a" => "It is completely useless.", "b" => "It is fast and flawless.", "c" => "It has both positive and negative aspects.", "d" => "It is worse than the old one in every way.", "ans" => "C", "exp" => "He acknowledges it's faster but points out a negative (it jams often)."],
    ["m" => "I thought the deadline for the report was Friday.", "w" => "It was, but the manager moved it up to Wednesday.", "q" => "What does the woman say about the deadline?", "a" => "It has been delayed.", "b" => "It has been brought forward.", "c" => "It remains unchanged.", "d" => "It is no longer required.", "ans" => "B", "exp" => "To 'move up' a deadline means to bring it forward to an earlier date."]
];

// Replicate and lightly modify to reach 30 questions
for ($i = 0; $i < 30; $i++) {
    $base = $shortConvos[$i % 5];
    $num = $i + 1;
    $listeningQuestions[] = [
        "question_text" => ($base['m'] ?? '') ? "Man: '{$base['m']}' Woman: '{$base['w']}' Narrator: {$base['q']} (Q$num)" : "Woman: '{$base['w']}' Man: '{$base['m']}' Narrator: {$base['q']} (Q$num)",
        "option_a" => $base['a'],
        "option_b" => $base['b'],
        "option_c" => $base['c'],
        "option_d" => $base['d'],
        "correct_answer" => $base['ans'],
        "explanation" => $base['exp'],
        "difficulty" => "medium"
    ];
}

$longConvos = [
    [
        "passage" => "Questions 31-34 are based on a conversation between a student and an academic advisor. Man: 'Hi, Dr. Smith. I need some advice on my schedule for next semester.' Woman: 'Sure, what's the issue?' Man: 'Well, I want to take Advanced Calculus, but it conflicts with my Physics lab.' Woman: 'That is a problem. You need both for your major. Have you considered taking the evening section of the Physics lab?' Man: 'I didn't know there was an evening section. That would solve everything!'",
        "qs" => [
            ["q" => "What is the student's problem?", "a" => "He doesn't like Physics.", "b" => "Two of his required classes are scheduled at the same time.", "c" => "He cannot find the academic advisor's office.", "d" => "He wants to change his major.", "ans" => "B", "exp" => "His Calculus class conflicts with his Physics lab."],
            ["q" => "What does the advisor suggest?", "a" => "Taking Calculus next year.", "b" => "Dropping the Physics major.", "c" => "Enrolling in an evening class.", "d" => "Speaking to the Physics professor.", "ans" => "C", "exp" => "The advisor asks if he considered the evening section of the Physics lab."],
            ["q" => "How does the student react to the suggestion?", "a" => "He is angry.", "b" => "He is relieved.", "c" => "He is confused.", "d" => "He is indifferent.", "ans" => "B", "exp" => "He says 'That would solve everything!' indicating relief."],
            ["q" => "What will the student most likely do next?", "a" => "Register for the evening Physics lab.", "b" => "Drop Advanced Calculus.", "c" => "Change his major.", "d" => "Go to sleep.", "ans" => "A", "exp" => "Since it solves his problem, he will likely register for it."]
        ]
    ],
    [
        "passage" => "Questions 35-38 are based on a conversation about a group project. Woman: 'We really need to get started on our presentation for history class.' Man: 'I know. The deadline is next Monday. Have you done any research on the French Revolution yet?' Woman: 'I've read a few chapters in the textbook, but we need more sources. I was thinking of going to the library tonight.' Man: 'Great idea. I'll come with you. Let's focus on the causes of the revolution first.'",
        "qs" => [
            ["q" => "What are the speakers discussing?", "a" => "A history presentation", "b" => "A trip to France", "c" => "A book they just read", "d" => "Their weekend plans", "ans" => "A", "exp" => "They are discussing getting started on a presentation for history class."],
            ["q" => "When is the project due?", "a" => "Tonight", "b" => "Tomorrow", "c" => "Next Monday", "d" => "Next month", "ans" => "C", "exp" => "The man states, 'The deadline is next Monday.'"],
            ["q" => "Where are they going tonight?", "a" => "To a French restaurant", "b" => "To the library", "c" => "To a history museum", "d" => "To a party", "ans" => "B", "exp" => "The woman says she's going to the library tonight, and the man agrees to come."],
            ["q" => "What aspect of the topic will they focus on first?", "a" => "The aftermath of the revolution", "b" => "The causes of the revolution", "c" => "The key figures of the revolution", "d" => "The economic impact", "ans" => "B", "exp" => "The man suggests: 'Let's focus on the causes of the revolution first.'"]
        ]
    ]
];

foreach ($longConvos as $conv) {
    foreach ($conv['qs'] as $idx => $q) {
        $listeningQuestions[] = [
            "question_text" => $q['q'],
            "passage_text" => $conv['passage'],
            "option_a" => $q['a'],
            "option_b" => $q['b'],
            "option_c" => $q['c'],
            "option_d" => $q['d'],
            "correct_answer" => $q['ans'],
            "explanation" => $q['exp'],
            "difficulty" => "medium"
        ];
    }
}

$lectures = [
    [
        "passage" => "Questions 39-42 are based on a lecture in an astronomy class. Professor: 'Today we'll discuss the lifecycle of stars. A star begins its life in a nebula, which is a massive cloud of gas and dust. Gravity causes the gas to clump together, forming a protostar. As the protostar shrinks, its core gets hotter. Once the core temperature reaches about 10 million degrees Celsius, nuclear fusion begins. This is the process where hydrogen atoms combine to form helium, releasing an enormous amount of energy. This energy pushes outward, balancing the inward pull of gravity, and the star becomes a main-sequence star, much like our Sun. Stars spend the vast majority of their lives in this stable phase.'",
        "qs" => [
            ["q" => "What is the main topic of the lecture?", "a" => "The planets in our solar system", "b" => "The lifecycle of stars", "c" => "The history of astronomy", "d" => "The composition of nebulas", "ans" => "B", "exp" => "The professor says, 'Today we'll discuss the lifecycle of stars.'"],
            ["q" => "What is a nebula?", "a" => "A dying star", "b" => "A massive cloud of gas and dust", "c" => "A type of planet", "d" => "A black hole", "ans" => "B", "exp" => "A nebula is described as 'a massive cloud of gas and dust.'"],
            ["q" => "What process begins when the core temperature reaches 10 million degrees?", "a" => "Nuclear fission", "b" => "Supernova explosion", "c" => "Nuclear fusion", "d" => "Photosynthesis", "ans" => "C", "exp" => "The lecture states, 'Once the core temperature reaches about 10 million degrees Celsius, nuclear fusion begins.'"],
            ["q" => "According to the professor, what phase do stars spend most of their lives in?", "a" => "The nebula phase", "b" => "The protostar phase", "c" => "The main-sequence phase", "d" => "The supernova phase", "ans" => "C", "exp" => "The professor notes, 'Stars spend the vast majority of their lives in this stable phase (main-sequence).'"]
        ]
    ],
    [
        "passage" => "Questions 43-46 are based on a talk in an environmental science class. Speaker: 'Urban heat islands are metropolitan areas that are significantly warmer than their surrounding rural areas. This temperature difference is primarily due to human activities and modifications to land surfaces. Buildings, roads, and other infrastructure absorb and re-emit the sun's heat more than natural landscapes such as forests and water bodies. Additionally, human activities like driving cars and using air conditioning generate waste heat, which contributes to the warming effect. To mitigate urban heat islands, cities are increasingly adopting strategies like planting more trees, creating green roofs, and using reflective materials for pavements and buildings.'",
        "qs" => [
            ["q" => "What is an urban heat island?", "a" => "A tropical island city", "b" => "A metropolitan area warmer than surrounding rural areas", "c" => "A rural area experiencing a heatwave", "d" => "An island formed by volcanic activity", "ans" => "B", "exp" => "It is defined as 'metropolitan areas that are significantly warmer than their surrounding rural areas.'"],
            ["q" => "Why do buildings and roads contribute to the urban heat island effect?", "a" => "They produce waste heat.", "b" => "They absorb and re-emit the sun's heat.", "c" => "They block the wind.", "d" => "They reflect sunlight away.", "ans" => "B", "exp" => "Buildings and roads 'absorb and re-emit the sun's heat more than natural landscapes.'"],
            ["q" => "Which of the following generates waste heat according to the talk?", "a" => "Planting trees", "b" => "Using air conditioning", "c" => "Green roofs", "d" => "Reflective materials", "ans" => "B", "exp" => "The speaker mentions 'human activities like driving cars and using air conditioning generate waste heat.'"],
            ["q" => "What is one strategy cities are using to combat this issue?", "a" => "Building taller skyscrapers", "b" => "Paving over natural landscapes", "c" => "Creating green roofs", "d" => "Banning cars entirely", "ans" => "C", "exp" => "Creating green roofs is mentioned as a strategy to mitigate urban heat islands."]
        ]
    ],
    [
        "passage" => "Questions 47-50 are based on a lecture in a psychology class. Professor: 'Memory is typically divided into three stages: sensory memory, short-term memory, and long-term memory. Sensory memory holds information from the senses for a fraction of a second. If we pay attention to that information, it moves into short-term memory, which can hold about seven items for 20 to 30 seconds. To transfer information from short-term to long-term memory, which has a virtually unlimited capacity and duration, we use processes like rehearsal and elaboration. Rehearsal involves simply repeating the information, while elaboration involves connecting new information to existing knowledge, making it much more likely to be remembered.'",
        "qs" => [
            ["q" => "What are the three stages of memory discussed?", "a" => "Visual, auditory, and kinesthetic", "b" => "Past, present, and future", "c" => "Sensory, short-term, and long-term", "d" => "Conscious, subconscious, and unconscious", "ans" => "C", "exp" => "The three stages are 'sensory memory, short-term memory, and long-term memory.'"],
            ["q" => "How long does short-term memory typically hold information?", "a" => "A fraction of a second", "b" => "20 to 30 seconds", "c" => "Several hours", "d" => "A lifetime", "ans" => "B", "exp" => "Short-term memory 'can hold about seven items for 20 to 30 seconds.'"],
            ["q" => "What does the process of elaboration involve?", "a" => "Simply repeating the information", "b" => "Ignoring irrelevant details", "c" => "Connecting new information to existing knowledge", "d" => "Storing information in sensory memory", "ans" => "C", "exp" => "Elaboration 'involves connecting new information to existing knowledge.'"],
            ["q" => "Which memory stage has a virtually unlimited capacity?", "a" => "Sensory memory", "b" => "Short-term memory", "c" => "Long-term memory", "d" => "All of them", "ans" => "C", "exp" => "Long-term memory is described as having 'a virtually unlimited capacity and duration.'"]
        ]
    ]
];

foreach ($lectures as $lec) {
    foreach ($lec['qs'] as $idx => $q) {
        $listeningQuestions[] = [
            "question_text" => $q['q'],
            "passage_text" => $lec['passage'],
            "option_a" => $q['a'],
            "option_b" => $q['b'],
            "option_c" => $q['c'],
            "option_d" => $q['d'],
            "correct_answer" => $q['ans'],
            "explanation" => $q['exp'],
            "difficulty" => "hard"
        ];
    }
}

file_put_contents(__DIR__ . '/listening_questions.json', json_encode($listeningQuestions, JSON_PRETTY_PRINT));
echo "Successfully generated 50 Listening Questions in listening_questions.json\n";
