<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$lessonId = (int)($_GET['lesson_id'] ?? 0);
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

$pageTitle = 'Quiz: ' . $lesson['title'];
$activeNav = 'lessons';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width:720px;">
    <a href="<?= BASE_URL ?>/lesson.php?id=<?= (int)$lesson['id'] ?>" style="font-size:0.9rem; color:var(--ink-600);">&larr; Back to lesson</a>
    <h1 style="margin-top:14px;">Quiz: <?= e($lesson['title']) ?></h1>

    <?php if (!$questions): ?>
      <div class="card empty-state">
        <h3>No quiz yet for this lesson</h3>
        <p class="mb-0">Check back soon, or explore another lesson.</p>
      </div>
    <?php else: ?>
      <p>Answer all <?= count($questions) ?> question<?= count($questions) === 1 ? '' : 's' ?>, then submit. You can retake this quiz as many times as you like.</p>

      <form method="POST" action="<?= BASE_URL ?>/quiz_submit.php" id="quizForm" class="card">
        <input type="hidden" name="lesson_id" value="<?= (int)$lesson['id'] ?>">
        <?php foreach ($questions as $i => $q): ?>
          <div class="quiz-question">
            <h3>Q<?= $i + 1 ?>. <?= e($q['question']) ?></h3>
            <div class="quiz-options">
              <?php foreach (['a', 'b', 'c', 'd'] as $opt): ?>
                <label class="quiz-option">
                  <input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="<?= $opt ?>" required>
                  <span><?= e($q['option_' . $opt]) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary btn-block">Submit answers</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
