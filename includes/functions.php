<?php
/**
 * Shared helper functions
 * - Session bootstrap
 * - Auth guards
 * - Small 3-language UI dictionary (English / Yoruba / Pidgin)
 * - Adaptive difficulty engine
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------
   Auth helpers
--------------------------------------------------------- */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return isset($_SESSION['admin_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /adaptive-literacy-platform/login.php');
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: /adaptive-literacy-platform/admin/login.php');
        exit;
    }
}

function current_user(PDO $pdo): ?array {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/* ---------------------------------------------------------
   Simple UI dictionary — English / Yoruba / Pidgin
   Drives the site chrome (nav, buttons, labels) so the whole
   interface — not just lesson content — can switch language.
--------------------------------------------------------- */
function ui_lang(): string {
    if (isset($_GET['ui_lang']) && in_array($_GET['ui_lang'], ['english', 'yoruba', 'pidgin'], true)) {
        $_SESSION['ui_lang'] = $_GET['ui_lang'];
    }
    return $_SESSION['ui_lang'] ?? 'english';
}

function t(string $key): string {
    static $dict = [
        'home'        => ['english' => 'Home',            'yoruba' => 'Ilé',              'pidgin' => 'Home'],
        'lessons'     => ['english' => 'Lessons',          'yoruba' => 'Àwọn Ẹ̀kọ́',        'pidgin' => 'Lessons'],
        'dashboard'   => ['english' => 'Dashboard',        'yoruba' => 'Pátákó Ìjọba',      'pidgin' => 'Dashboard'],
        'progress'    => ['english' => 'My Progress',      'yoruba' => 'Ìlọsíwájú Mi',      'pidgin' => 'My Progress'],
        'report'      => ['english' => 'Report',           'yoruba' => 'Ìròyìn',            'pidgin' => 'Report'],
        'login'       => ['english' => 'Log In',           'yoruba' => 'Wọlé',              'pidgin' => 'Log In'],
        'register'    => ['english' => 'Sign Up',          'yoruba' => 'Forúkọsílẹ̀',        'pidgin' => 'Sign Up'],
        'logout'      => ['english' => 'Log Out',          'yoruba' => 'Jáde',              'pidgin' => 'Log Out'],
        'welcome'     => ['english' => 'Welcome back',     'yoruba' => 'Ẹ káàbọ̀',           'pidgin' => 'Welcome back'],
        'start_lesson'=> ['english' => 'Start Lesson',     'yoruba' => 'Bẹ̀rẹ̀ Ẹ̀kọ́',        'pidgin' => 'Start di Lesson'],
        'take_quiz'   => ['english' => 'Take the Quiz',    'yoruba' => 'Ṣe Àdánwò',          'pidgin' => 'Do di Quiz'],
        'continue'    => ['english' => 'Continue',         'yoruba' => 'Tẹ̀síwájú',          'pidgin' => 'Continue'],
    ];
    $lang = ui_lang();
    return $dict[$key][$lang] ?? $dict[$key]['english'] ?? $key;
}

/* ---------------------------------------------------------
   Adaptive difficulty engine
   Very small rule-based model:
   - score >= 80%  -> learner is ready to move UP a level
   - score < 40%   -> recommend repeating this level
   - otherwise     -> stay at the current level
   Also nudges the user's stored literacy_level after they
   finish several lessons at a strong average.
--------------------------------------------------------- */
function next_level(string $level): string {
    return match ($level) {
        'beginner'     => 'intermediate',
        'intermediate' => 'advanced',
        default        => 'advanced',
    };
}

function previous_level(string $level): string {
    return match ($level) {
        'advanced'     => 'intermediate',
        'intermediate' => 'beginner',
        default        => 'beginner',
    };
}

function adaptive_recommendation(int $scorePercent, string $currentLevel): array {
    if ($scorePercent >= 80) {
        return [
            'action'  => 'advance',
            'level'   => next_level($currentLevel),
            'message' => "Great job! You scored {$scorePercent}%. We'll start showing you " . next_level($currentLevel) . " level lessons.",
        ];
    }
    if ($scorePercent < 40) {
        return [
            'action'  => 'reinforce',
            'level'   => $currentLevel,
            'message' => "You scored {$scorePercent}%. Let's practice more " . $currentLevel . " lessons before moving on.",
        ];
    }
    return [
        'action'  => 'stay',
        'level'   => $currentLevel,
        'message' => "You scored {$scorePercent}%. Keep going at the " . $currentLevel . " level.",
    ];
}

function maybe_update_user_level(PDO $pdo, int $userId, string $newLevel, string $currentLevel): void {
    $order = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
    if (($order[$newLevel] ?? 1) > ($order[$currentLevel] ?? 1)) {
        $stmt = $pdo->prepare('UPDATE users SET literacy_level = ? WHERE id = ?');
        $stmt->execute([$newLevel, $userId]);
    }
}
