-- =========================================================
-- Adaptive Literacy Learning Platform for Adult Learners
-- Database Schema
-- Import this file in phpMyAdmin, or run:
--   mysql -u root -p < schema.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS literacy_platform
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE literacy_platform;

-- ---------------------------------------------------------
-- Learners
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  full_name          VARCHAR(100)  NOT NULL,
  email              VARCHAR(150)  NOT NULL UNIQUE,
  phone              VARCHAR(20)   DEFAULT NULL,
  password_hash      VARCHAR(255)  NOT NULL,
  preferred_language ENUM('english','yoruba','pidgin') NOT NULL DEFAULT 'english',
  literacy_level     ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  avatar_seed        VARCHAR(20)   DEFAULT NULL,
  created_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Platform administrators / content managers
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(60)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Lessons (interactive content: audio + video + text)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS lessons (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(150) NOT NULL,
  language     ENUM('english','yoruba','pidgin') NOT NULL,
  level        ENUM('beginner','intermediate','advanced') NOT NULL,
  topic        VARCHAR(100) NOT NULL,
  description  TEXT,
  content_text TEXT,
  video_path   VARCHAR(255) DEFAULT NULL,
  audio_path   VARCHAR(255) DEFAULT NULL,
  sort_order   INT DEFAULT 0,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Quiz questions tied to a lesson
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS quiz_questions (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  lesson_id      INT NOT NULL,
  question       TEXT NOT NULL,
  option_a       VARCHAR(255) NOT NULL,
  option_b       VARCHAR(255) NOT NULL,
  option_c       VARCHAR(255) NOT NULL,
  option_d       VARCHAR(255) NOT NULL,
  correct_option ENUM('a','b','c','d') NOT NULL,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Per-user, per-lesson progress (drives Progress Tracking + Reports)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_progress (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT NOT NULL,
  lesson_id      INT NOT NULL,
  status         ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  best_score     INT DEFAULT 0,
  attempts       INT DEFAULT 0,
  last_attempt_at TIMESTAMP NULL DEFAULT NULL,
  completed_at   TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY unique_user_lesson (user_id, lesson_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Individual quiz attempts (used by the adaptive engine)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS quiz_attempts (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT NOT NULL,
  lesson_id     INT NOT NULL,
  score_percent INT NOT NULL,
  total_questions INT NOT NULL,
  correct_answers INT NOT NULL,
  attempted_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- Seed data
-- =========================================================

-- Default admin -> username: admin | password: Admin@123
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$10$h9zu6FlKNfUyALeQ0OSKf.i4yCiTnXXz.7qFBNaEgFQX.SnX46ZHO');

-- Sample lessons across the three languages / levels
INSERT INTO lessons (title, language, level, topic, description, content_text, sort_order) VALUES
('The Alphabet & Everyday Greetings', 'english', 'beginner', 'Greetings',
 'Learn the English alphabet and common daily greetings.',
 'A is for Apple, B is for Ball... Good morning! Good afternoon! How are you? I am fine, thank you.', 1),
('Reading Simple Signs & Labels', 'english', 'beginner', 'Everyday Reading',
 'Practice reading common signs you see around you: STOP, EXIT, OPEN, CLOSED.',
 'Signs keep us safe. STOP means do not go. EXIT means the way out. OPEN means you may enter.', 2),
('Filling a Simple Form', 'english', 'intermediate', 'Life Skills',
 'Learn how to fill your name, address and date on a form.',
 'A form asks for your Full Name, Address, Phone Number and Date. Write clearly in capital letters.', 3),
('Ábíìdí àti Ìkíni Ojoojúmọ́', 'yoruba', 'beginner', 'Ìkíni',
 'Kọ́ ábíìdí Yorùbá àti bí a ṣe ń kí ara wa lójoojúmọ́.',
 'A, B, D, E, Ẹ, F, G, GB... Ẹ káàrọ̀! Ẹ kú alẹ́! Báwo ni? Dáadáa ni.', 1),
('Kíkà Àwọn Àmì Ojú Pópó', 'yoruba', 'beginner', 'Kíkà Ojoojúmọ́',
 'Ẹ jẹ́ ká kọ́ bí a ṣe ń kà àwọn àmì tá a máa ń rí ní àyíká wa.',
 'Àmì máa ń jẹ́ ká mọ ohun tí a lè ṣe. DÚRÓ ni ìtumọ̀ STOP.', 2),
('Alphabet and How To Greet Person', 'pidgin', 'beginner', 'Greetings',
 'Learn di alphabet and how to greet people for Pidgin.',
 'A B C D... How you dey? I dey fine o. Wetin dey happen?', 1),
('How To Read Simple Sign', 'pidgin', 'beginner', 'Everyday Reading',
 'Make we learn how to read signs wey we dey see every day.',
 'Sign dey helep us waka safe. STOP mean make you no go. OPEN mean say you fit enter.', 2);

-- Sample quiz questions
INSERT INTO quiz_questions (lesson_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES
(1, 'What comes after the letter A?', 'B', 'C', 'D', 'Z', 'a'),
(1, 'How do you greet someone in the morning?', 'Good night', 'Good morning', 'Goodbye', 'Good evening', 'b'),
(2, 'What does a STOP sign mean?', 'Go faster', 'Turn around', 'Do not go', 'Sit down', 'c'),
(2, 'What does EXIT mean?', 'Entrance', 'The way out', 'Danger', 'Wait here', 'b'),
(4, 'Kí ni ìtumọ̀ "Ẹ káàrọ̀"?', 'Good night', 'Good morning', 'Goodbye', 'Thank you', 'b'),
(6, 'How you go greet person for Pidgin?', 'How you dey?', 'I no know', 'Abeg comot', 'E don do', 'a');
