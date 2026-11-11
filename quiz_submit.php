<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/lessons.php');
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$lessonId = (int)($_POST['lesson_id'] ?? 0);
$answers  = $_POST['answers'] ?? [];

$stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();
if (!$lesson) {
    header('Location: ' . BASE_URL . '/lessons.php');
    exit;
}

$qStmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE lesson_id = ?');
$qStmt->execute([$lessonId]);
$questions = $qStmt->fetchAll();

$total = count($questions);
$correctCount = 0;
$results = [];

foreach ($questions as $q) {
    $given = $answers[$q['id']] ?? null;
    $isCorrect = ($given === $q['correct_option']);
    if ($isCorrect) $correctCount++;
    $results[] = [
        'question'       => $q['question'],
        'given'          => $given,
        'correct_option' => $q['correct_option'],
        'is_correct'     => $isCorrect,
        'options'        => ['a' => $q['option_a'], 'b' => $q['option_b'], 'c' => $q['option_c'], 'd' => $q['option_d']],
    ];
}

$scorePercent = $total > 0 ? (int)round(($correctCount / $total) * 100) : 0;

// 1. Log the attempt
$pdo->prepare('INSERT INTO quiz_attempts (user_id, lesson_id, score_percent, total_questions, correct_answers) VALUES (?, ?, ?, ?, ?)')
    ->execute([$userId, $lessonId, $scorePercent, $total, $correctCount]);

// 2. Update per-lesson progress (best score wins, completed if score >= 60)
$pdo->prepare('
    INSERT INTO user_progress (user_id, lesson_id, status, best_score, attempts, last_attempt_at, completed_at)
    VALUES (?, ?, ?, ?, 1, NOW(), ?)
    ON DUPLICATE KEY UPDATE
      best_score   = GREATEST(best_score, VALUES(best_score)),
      attempts     = attempts + 1,
      status       = IF(VALUES(best_score) >= 60 OR status = "completed", "completed", "in_progress"),
      last_attempt_at = NOW(),
      completed_at = IF(VALUES(best_score) >= 60 AND completed_at IS NULL, NOW(), completed_at)
')->execute([
    $userId, $lessonId,
    $scorePercent >= 60 ? 'completed' : 'in_progress',
    $scorePercent,
    $scorePercent >= 60 ? date('Y-m-d H:i:s') : null,
]);

// 3. Adaptive engine: recommend next level & possibly bump the learner's level
$stmt = $pdo->prepare('SELECT literacy_level FROM users WHERE id = ?');
$stmt->execute([$userId]);
$currentLevel = $stmt->fetchColumn() ?: 'beginner';

$recommendation = adaptive_recommendation($scorePercent, $currentLevel);
if ($recommendation['action'] === 'advance') {
    maybe_update_user_level($pdo, $userId, $recommendation['level'], $currentLevel);
}

$pageTitle = 'Quiz Results';
$activeNav = 'lessons';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width:720px;">
    <h1>Quiz results</h1>

    <div class="card text-center" style="margin-bottom:24px;">
      <div class="score-ring-wrap">
        <div class="progress-track" style="max-width:320px;">
          <div class="progress-fill" style="width:<?= $scorePercent ?>%"></div>
        </div>
        <h2 style="margin:10px 0 0;"><?= $scorePercent ?>% (<?= $correctCount ?>/<?= $total ?>)</h2>
      </div>
      <p class="mb-0" style="margin-top:8px;"><?= e($recommendation['message']) ?></p>
    </div>

    <div class="card" style="margin-bottom:24px;">
      <h3>Review your answers</h3>
      <?php foreach ($results as $i => $r): ?>
        <div class="quiz-question">
          <h3>Q<?= $i + 1 ?>. <?= e($r['question']) ?></h3>
          <div class="quiz-options">
            <?php foreach ($r['options'] as $key => $label):
              $class = '';
              if ($key === $r['correct_option']) $class = 'correct';
              elseif ($key === $r['given'] && !$r['is_correct']) $class = 'incorrect';
            ?>
              <div class="quiz-option <?= $class ?>">
                <span><?= e($label) ?></span>
                <?php if ($key === $r['correct_option']): ?><strong style="margin-left:auto;">&#10003;</strong><?php endif; ?>
                <?php if ($key === $r['given'] && !$r['is_correct']): ?><strong style="margin-left:auto;">&#10007; your answer</strong><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="flex gap-12" style="flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/quiz.php?lesson_id=<?= (int)$lessonId ?>" class="btn btn-outline">Retake quiz</a>
      <a href="<?= BASE_URL ?>/lessons.php" class="btn btn-primary">More lessons</a>
      <a href="<?= BASE_URL ?>/progress.php" class="btn btn-gold"><?= t('progress') ?></a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
