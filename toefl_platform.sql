-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 02:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toefl_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `created_at`) VALUES
(1, 'admin', 'admin@toeflplatform.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '2026-06-13 12:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `section` enum('listening','structure','reading') NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `section`, `icon`, `description`, `sort_order`) VALUES
(1, 'Short Conversations', 'short-conversations', 'listening', 'chat-bubble', 'Memahami percakapan singkat antara dua orang dalam konteks sehari-hari.', 1),
(2, 'Long Conversations', 'long-conversations', 'listening', 'chat-bubbles', 'Memahami percakapan panjang dengan beberapa pertukaran antar pembicara.', 2),
(3, 'Lectures & Talks', 'lectures-talks', 'listening', 'microphone', 'Memahami ceramah akademis dan presentasi formal.', 3),
(4, 'Announcements', 'announcements', 'listening', 'megaphone', 'Memahami pengumuman dan informasi lisan di tempat umum.', 4),
(5, 'Sentence Completion', 'sentence-completion', 'structure', 'puzzle', 'Melengkapi kalimat dengan struktur gramatikal yang tepat.', 5),
(6, 'Error Identification', 'error-identification', 'structure', 'search', 'Menemukan dan mengidentifikasi kesalahan gramatikal dalam kalimat.', 6),
(7, 'Clause & Phrase', 'clause-phrase', 'structure', 'layers', 'Memahami penggunaan klausa dan frasa dalam kalimat kompleks.', 7),
(8, 'Verb Forms & Tenses', 'verb-forms-tenses', 'structure', 'clock', 'Penggunaan bentuk kata kerja dan tenses yang benar.', 8),
(9, 'Main Idea & Topic', 'main-idea-topic', 'reading', 'target', 'Menemukan ide utama dan topik dari suatu bacaan.', 9),
(10, 'Vocabulary in Context', 'vocabulary-context', 'reading', 'book', 'Memahami makna kosakata dalam konteks bacaan.', 10),
(11, 'Inference & Detail', 'inference-detail', 'reading', 'zoom-in', 'Membuat inferensi dan menemukan detail tersurat dalam teks.', 11),
(12, 'Reference & Pronoun', 'reference-pronoun', 'reading', 'link', 'Memahami referensi dan penggunaan pronoun dalam teks.', 12);

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `summary` text DEFAULT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `category_id`, `title`, `slug`, `content`, `summary`, `audio_file`, `thumbnail`, `sort_order`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pengantar Short Conversations', 'pengantar-short-conversations', '<h2>Short Conversations dalam TOEFL</h2>\r\n<p>Bagian Listening Comprehension TOEFL terdiri dari tiga bagian, yang pertama adalah <strong>Short Conversations</strong>. Pada bagian ini, kamu akan mendengarkan percakapan singkat antara dua orang kemudian menjawab satu pertanyaan untuk setiap percakapan.</p>\r\n\r\n<h3>Karakteristik Short Conversations</h3>\r\n<ul>\r\n  <li>Terdiri dari 30 soal percakapan singkat</li>\r\n  <li>Setiap percakapan hanya diputar satu kali</li>\r\n  <li>Melibatkan dua pembicara (pria dan wanita)</li>\r\n  <li>Konteks: kampus, toko, restoran, asrama, dll.</li>\r\n</ul>\r\n\r\n<h3>Tipe Pertanyaan Umum</h3>\r\n<ol>\r\n  <li><strong>What does the man/woman mean?</strong> - Memahami makna tersirat</li>\r\n  <li><strong>What will the man/woman probably do?</strong> - Prediksi tindakan</li>\r\n  <li><strong>Where does the conversation take place?</strong> - Lokasi percakapan</li>\r\n  <li><strong>What is the conversation mainly about?</strong> - Topik utama</li>\r\n</ol>\r\n\r\n<h3>Strategi Menjawab</h3>\r\n<p>Dengarkan dengan seksama nada dan intonasi pembicara. Sering kali jawaban tersembunyi dalam cara pembicara merespons, bukan hanya kata-kata literal yang diucapkan.</p>\r\n\r\n<div class=\"tip-box\">\r\n  <h4>💡 Tips Penting</h4>\r\n  <p>Fokus pada kata kunci dan ekspresi idiomatik. Banyak pertanyaan menguji pemahaman tentang makna tidak langsung (implied meaning).</p>\r\n</div>', 'Pelajari strategi dasar menghadapi soal Short Conversations dalam TOEFL Listening.', NULL, NULL, 1, 1, '2026-06-13 12:19:28', '2026-06-13 12:19:28'),
(2, 5, 'Sentence Completion - Dasar', 'sentence-completion-dasar', '<h2>Structure: Sentence Completion</h2>\r\n<p>Bagian <strong>Structure and Written Expression</strong> terdiri dari 40 soal yang dibagi menjadi dua tipe: Sentence Completion (15 soal) dan Error Identification (25 soal).</p>\r\n\r\n<h3>Sentence Completion</h3>\r\n<p>Pada tipe ini, kamu diminta melengkapi kalimat yang belum selesai dengan memilih salah satu dari empat pilihan jawaban yang paling tepat secara gramatikal.</p>\r\n\r\n<h3>Pola Kalimat yang Sering Muncul</h3>\r\n<ol>\r\n  <li><strong>Subject + Verb Agreement</strong><br>Pastikan subject dan verb sesuai (singular/plural).<br><em>Contoh: The group of students <u>is</u> studying in the library.</em></li>\r\n  <li><strong>Relative Clauses</strong><br>Penggunaan who, which, that yang tepat.<br><em>Contoh: The book <u>that</u> she borrowed was interesting.</em></li>\r\n  <li><strong>Parallel Structure</strong><br>Elemen dalam satu kalimat harus memiliki bentuk yang paralel.<br><em>Contoh: She likes reading, writing, and <u>drawing</u>.</em></li>\r\n</ol>\r\n\r\n<h3>Teknik Eliminasi</h3>\r\n<p>Ketika tidak yakin, gunakan teknik eliminasi: buang pilihan yang jelas salah terlebih dahulu, kemudian pilih dari yang tersisa dengan memperhatikan konteks kalimat secara keseluruhan.</p>', 'Dasar-dasar menjawab soal Sentence Completion dalam TOEFL Structure.', NULL, NULL, 1, 1, '2026-06-13 12:19:28', '2026-06-13 12:19:28'),
(3, 9, 'Menemukan Main Idea', 'menemukan-main-idea', '<h2>Reading: Main Idea & Topic</h2>\r\n<p>Dalam TOEFL Reading Comprehension, kemampuan menemukan <strong>main idea</strong> (ide utama) adalah keterampilan paling fundamental yang harus dikuasai.</p>\r\n\r\n<h3>Apa itu Main Idea?</h3>\r\n<p>Main idea adalah pernyataan terpenting yang ingin disampaikan penulis dalam sebuah paragraf atau teks. Main idea biasanya ditemukan di:</p>\r\n<ul>\r\n  <li><strong>Topic sentence</strong>: biasanya kalimat pertama atau terakhir paragraf</li>\r\n  <li><strong>Thesis statement</strong>: pernyataan utama dalam paragraf pembuka</li>\r\n</ul>\r\n\r\n<h3>Cara Identifikasi Main Idea</h3>\r\n<ol>\r\n  <li>Baca judul dan heading terlebih dahulu</li>\r\n  <li>Baca kalimat pertama setiap paragraf (skimming)</li>\r\n  <li>Cari kata atau frasa yang berulang</li>\r\n  <li>Perhatikan kata transisi: however, therefore, in conclusion, etc.</li>\r\n</ol>\r\n\r\n<h3>Perbedaan Main Idea vs Topic</h3>\r\n<table class=\"content-table\">\r\n  <tr><th>Topic</th><th>Main Idea</th></tr>\r\n  <tr><td>Topik umum (noun/noun phrase)</td><td>Pernyataan lengkap tentang topik</td></tr>\r\n  <tr><td>Contoh: \"Climate Change\"</td><td>Contoh: \"Climate change poses serious threats to biodiversity.\"</td></tr>\r\n</table>', 'Strategi efektif menemukan main idea dan topic dalam teks TOEFL Reading.', NULL, NULL, 1, 1, '2026-06-13 12:19:28', '2026-06-13 12:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `practice_questions`
--

CREATE TABLE `practice_questions` (
  `id` int(11) NOT NULL,
  `practice_set_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `practice_questions`
--

INSERT INTO `practice_questions` (`id`, `practice_set_id`, `question_id`, `sort_order`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 2),
(3, 2, 3, 1),
(4, 2, 4, 2),
(5, 3, 6, 1),
(6, 3, 7, 2),
(7, 3, 8, 3);

-- --------------------------------------------------------

--
-- Table structure for table `practice_sets`
--

CREATE TABLE `practice_sets` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `time_limit` int(11) DEFAULT 15 COMMENT 'minutes',
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `practice_sets`
--

INSERT INTO `practice_sets` (`id`, `material_id`, `title`, `description`, `time_limit`, `is_published`, `created_at`) VALUES
(1, 1, 'Latihan Short Conversations #1', 'Latihan soal dasar Short Conversations untuk mengasah kemampuan mendengarkan percakapan singkat.', 15, 1, '2026-06-13 12:19:28'),
(2, 2, 'Latihan Sentence Completion #1', 'Latihan melengkapi kalimat dengan pilihan gramatikal yang tepat.', 15, 1, '2026-06-13 12:19:28'),
(3, 3, 'Latihan Main Idea #1', 'Identifikasi ide utama dari berbagai paragraf pendek.', 20, 1, '2026-06-13 12:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `section` enum('listening','structure','reading') NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `passage_text` longtext DEFAULT NULL,
  `option_a` text NOT NULL,
  `option_b` text NOT NULL,
  `option_c` text NOT NULL,
  `option_d` text NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `explanation` text DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `section`, `category_id`, `material_id`, `question_text`, `audio_file`, `passage_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `difficulty`, `created_at`) VALUES
(1, 'listening', 1, NULL, 'Man: \"I can\'t believe how long the line is at the bookstore.\" Woman: \"I know. Maybe we should come back later.\" What does the woman suggest?', NULL, NULL, 'They should buy books online.', 'They should wait in line.', 'They should return at another time.', 'They should go to a different store.', 'C', 'The woman says \"Maybe we should come back later,\" which means she suggests returning at another time.', 'easy', '2026-06-13 12:19:28'),
(2, 'listening', 1, NULL, 'Woman: \"Did you finish the assignment for Professor Johnson?\" Man: \"Are you kidding? I haven\'t even started.\" What can be inferred about the man?', NULL, NULL, 'He completed the assignment early.', 'He needs more time to finish.', 'He does not have an assignment.', 'He already submitted the assignment.', 'B', 'The expression \"I haven\'t even started\" implies he needs much more time to complete the assignment.', 'medium', '2026-06-13 12:19:28'),
(3, 'structure', 5, NULL, '_______ the President signed the bill into law, it immediately went into effect.', NULL, NULL, 'When', 'Although', 'Despite', 'However', 'A', '\"When\" introduces a time clause showing that the bill went into effect at the moment the President signed it.', 'easy', '2026-06-13 12:19:28'),
(4, 'structure', 5, NULL, 'The committee members, _______ had been working for months, finally reached a decision.', NULL, NULL, 'who', 'which', 'what', 'that', 'A', 'Use \"who\" for people. \"Committee members\" refers to people, so \"who\" is the correct relative pronoun.', 'easy', '2026-06-13 12:19:28'),
(5, 'structure', 6, NULL, 'Identify the error: \"Neither the manager nor the employees was present at the meeting.\"', NULL, NULL, 'Neither', 'nor', 'was', 'at the meeting', 'C', 'With \"neither...nor\", the verb agrees with the subject closest to it. \"Employees\" is plural, so it should be \"were\" not \"was\".', 'medium', '2026-06-13 12:19:28'),
(6, 'reading', 9, NULL, 'Read the passage: \"The Amazon rainforest, often called the lungs of the Earth, produces approximately 20% of the world\'s oxygen. Despite its importance, deforestation continues at an alarming rate, destroying millions of hectares annually.\" What is the main idea of this passage?', NULL, NULL, 'The Amazon produces 20% of the world\'s oxygen.', 'Deforestation is destroying the Amazon at an alarming rate.', 'The Amazon is vital but threatened by deforestation.', 'The Earth has many important rainforests.', 'C', 'The passage presents both the importance of the Amazon (oxygen production) and the threat it faces (deforestation), making option C the best summary of the main idea.', 'medium', '2026-06-13 12:19:28'),
(7, 'reading', 10, NULL, 'The word \"alarming\" in the passage most nearly means:', NULL, NULL, 'surprising', 'disturbing', 'exciting', 'ordinary', 'B', '\"Alarming\" means causing worry or concern. In context, the rate of deforestation is described as disturbing/worrying.', 'easy', '2026-06-13 12:19:28'),
(8, 'reading', 11, NULL, 'Based on the passage about the Amazon, what can be inferred?', NULL, NULL, 'The Amazon will recover quickly from deforestation.', 'Governments are doing nothing to stop deforestation.', 'The loss of the Amazon could affect global oxygen levels.', 'The Amazon is located in Africa.', 'C', 'If the Amazon produces 20% of the world\'s oxygen and is being destroyed, it can be inferred that its loss would impact global oxygen levels.', 'medium', '2026-06-13 12:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `test_type` enum('mini','full') NOT NULL,
  `time_limit` int(11) NOT NULL COMMENT 'in minutes',
  `total_questions` int(11) NOT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`id`, `title`, `description`, `test_type`, `time_limit`, `total_questions`, `is_published`, `created_at`) VALUES
(1, 'Mini Test - Listening Basics', 'Test pemahaman dasar Listening Comprehension dengan 10 soal pilihan.', 'mini', 15, 10, 1, '2026-06-13 12:19:28'),
(2, 'Mini Test - Structure Basics', 'Latihan Structure and Written Expression untuk pemula.', 'mini', 15, 10, 1, '2026-06-13 12:19:28'),
(3, 'Mini Test - Reading Comprehension', 'Latihan Reading Comprehension dengan passage pendek.', 'mini', 20, 10, 1, '2026-06-13 12:19:28'),
(4, 'TOEFL Full Test 1', 'Simulasi TOEFL lengkap dengan 100 soal: Listening (50), Structure (40), Reading (10). Waktu 115 menit.', 'full', 115, 100, 1, '2026-06-13 12:19:28'),
(5, 'TOEFL Full Test 2', 'Simulasi TOEFL lengkap kedua dengan 100 soal bervariasi untuk mengukur kemajuan belajar.', 'full', 115, 100, 1, '2026-06-13 12:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `test_questions`
--

CREATE TABLE `test_questions` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `test_questions`
--

INSERT INTO `test_questions` (`id`, `test_id`, `question_id`, `sort_order`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 2),
(3, 2, 3, 1),
(4, 2, 4, 2),
(5, 2, 5, 3),
(6, 3, 6, 1),
(7, 3, 7, 2),
(8, 3, 8, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_practice_results`
--

CREATE TABLE `user_practice_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `practice_set_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_correct` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_test_answers`
--

CREATE TABLE `user_test_answers` (
  `id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_answer` enum('A','B','C','D') DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_test_results`
--

CREATE TABLE `user_test_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `toefl_score` int(11) NOT NULL,
  `total_correct` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `time_taken` int(11) DEFAULT NULL COMMENT 'seconds',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `practice_questions`
--
ALTER TABLE `practice_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `practice_set_id` (`practice_set_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `practice_sets`
--
ALTER TABLE `practice_sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_questions`
--
ALTER TABLE `test_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_practice_results`
--
ALTER TABLE `user_practice_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `practice_set_id` (`practice_set_id`);

--
-- Indexes for table `user_test_answers`
--
ALTER TABLE `user_test_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `result_id` (`result_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `user_test_results`
--
ALTER TABLE `user_test_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `test_id` (`test_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `practice_questions`
--
ALTER TABLE `practice_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `practice_sets`
--
ALTER TABLE `practice_sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `test_questions`
--
ALTER TABLE `test_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_practice_results`
--
ALTER TABLE `user_practice_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_test_answers`
--
ALTER TABLE `user_test_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_test_results`
--
ALTER TABLE `user_test_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `practice_questions`
--
ALTER TABLE `practice_questions`
  ADD CONSTRAINT `practice_questions_ibfk_1` FOREIGN KEY (`practice_set_id`) REFERENCES `practice_sets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `practice_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `practice_sets`
--
ALTER TABLE `practice_sets`
  ADD CONSTRAINT `practice_sets_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `test_questions`
--
ALTER TABLE `test_questions`
  ADD CONSTRAINT `test_questions_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_practice_results`
--
ALTER TABLE `user_practice_results`
  ADD CONSTRAINT `user_practice_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_practice_results_ibfk_2` FOREIGN KEY (`practice_set_id`) REFERENCES `practice_sets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_test_answers`
--
ALTER TABLE `user_test_answers`
  ADD CONSTRAINT `user_test_answers_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `user_test_results` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_test_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_test_results`
--
ALTER TABLE `user_test_results`
  ADD CONSTRAINT `user_test_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_test_results_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
