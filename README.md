# Adaptive Literacy Learning Platform for Adult Learners

A multi-page PHP/MySQL web application built for XAMPP. Adult learners register,
work through short audio/video lessons in **English, Yorùbá or Pidgin**, take a
quiz after each lesson, and can track their progress and print a report or
certificate. An admin area lets a content manager add lessons, quizzes and
review learner analytics.

## Tech stack
- **Frontend:** semantic HTML5, hand-written CSS3 (no framework), vanilla JavaScript
- **Backend:** PHP (procedural, PDO + prepared statements throughout)
- **Database:** MySQL / MariaDB (via XAMPP)
- **Server:** Apache (XAMPP)

## Features
1. Learner registration & login (bcrypt-hashed passwords, PDO prepared statements)
2. Content in English, Yorùbá and Pidgin — including the site's own navigation, via a small UI dictionary
3. Lessons with a quiz (multiple choice) at the end
4. Interactive content: audio and video players on the lesson page
5. Progress tracking (per-lesson status, best score, attempts)
6. Printable report / certificate of achievement

**Extra features added on top of the brief (kept intentionally small):**
- **Adaptive difficulty engine** — score 80%+ on a quiz and the platform nudges
  you to the next level; score under 40% and it recommends more practice at
  your current level (`includes/functions.php` → `adaptive_recommendation()`).
- **Printable certificate** — auto-unlocks on the Report page once a learner
  completes 3+ lessons with a 70%+ average.
- **Admin dashboard** — lesson/quiz management (with file upload for audio &
  video), a quiz builder, and simple analytics (learners, completion, most
  engaged lessons).

## Folder structure
```
adaptive-literacy-platform/
├── admin/                  Admin area (login, dashboard, lesson & quiz management, learners)
├── assets/
│   ├── css/style.css       Single global stylesheet (design tokens + components)
│   └── js/script.js        Client-side validation & small dynamic UI behaviours
├── config/db.php           PDO connection + BASE_URL constant
├── database/schema.sql     Full schema + seed data (sample lessons, quiz, admin account)
├── includes/               header.php / footer.php / functions.php (shared across all pages)
├── uploads/audio/          Uploaded lesson audio (mp3)
├── uploads/video/          Uploaded lesson video (mp4)
├── index.php, login.php, register.php, lessons.php, lesson.php,
│   quiz.php, quiz_submit.php, dashboard.php, progress.php, report.php
└── README.md
```

## Setup (XAMPP)
1. Copy the whole `adaptive-literacy-platform` folder into your XAMPP
   `htdocs` directory.
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), create nothing manually —
   just click **Import**, choose `database/schema.sql`, and run it. This creates
   the `literacy_platform` database, all tables, sample lessons/quiz questions,
   and a seed admin account.
4. If your MySQL uses a non-default username/password, edit
   `config/db.php` (`DB_USER`, `DB_PASS`).
5. Visit `http://localhost/adaptive-literacy-platform/` in your browser.

### Logins
- **Learner:** register your own account from the site.
- **Admin:** `http://localhost/adaptive-literacy-platform/admin/login.php`
  — username `admin`, password `Admin@123` (change this after first login by
  updating the `admins` table with a new `password_hash`).

### Uploading lesson audio/video
In the admin area, when adding or editing a lesson you can attach an `.mp3`
and/or `.mp4` file. Files are stored under `uploads/audio` and `uploads/video`
and served directly by Apache; PHP execution is disabled in that folder via
`.htaccess` for security.

## Notes on design choices
- All database access goes through PDO with prepared statements — no raw
  string-concatenated SQL — to prevent SQL injection.
- Passwords are hashed with `password_hash()` / verified with `password_verify()`.
- `includes/header.php` and `includes/footer.php` are included on every public
  page to keep markup DRY; the admin area has its own lightweight
  `_header.php` / `_footer.php` pair.
- Client-side validation (`assets/js/script.js`) is a convenience layer only —
  every form is re-validated server-side in PHP before touching the database.
