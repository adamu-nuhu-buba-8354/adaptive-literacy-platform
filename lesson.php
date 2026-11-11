<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$lessonId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    header('Location: ' . BASE_URL . '/lessons.php');
    exit;
}

$progress = null;
$user = current_user($pdo);
if ($user) {
  $userId = (int)$user['id'];
  // Mark lesson as at least "in_progress" the moment the learner opens it
  $upsert = $pdo->prepare(
    'INSERT INTO user_progress (user_id, lesson_id, status, last_attempt_at)
    VALUES (?, ?, "in_progress", NOW())
    ON DUPLICATE KEY UPDATE
      status = IF(status = "completed", status, "in_progress"),
      last_attempt_at = NOW()'
  );
  $upsert->execute([$userId, $lessonId]);

  $p = $pdo->prepare('SELECT * FROM user_progress WHERE user_id = ? AND lesson_id = ?');
  $p->execute([$userId, $lessonId]);
  $progress = $p->fetch();
} else {
  // Clear stale session user id if present to avoid FK errors
  if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
  }
}

$pageTitle = $lesson['title'];
$activeNav = 'lessons';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width:760px;">
    <a href="<?= BASE_URL ?>/lessons.php" style="font-size:0.9rem; color:var(--ink-600);">&larr; Back to lessons</a>

    <div style="margin:14px 0 20px;">
      <span class="badge badge-<?= e($lesson['level']) ?>"><?= e(ucfirst($lesson['level'])) ?></span>
      <span class="badge badge-lang"><?= e(ucfirst($lesson['language'])) ?></span>
      <span class="badge badge-lang"><?= e($lesson['topic']) ?></span>
    </div>

    <h1><?= e($lesson['title']) ?></h1>
    <p style="font-size:1.05rem;"><?= e($lesson['description']) ?></p>

    <?php if ($lesson['video_path']): ?>
      <div class="card" style="padding:0; overflow:hidden; margin-bottom:20px;">
        <video controls style="width:100%; display:block; background:#000;" poster="">
          <source src="<?= e($lesson['video_path']) ?>" type="video/mp4">
          Your browser does not support the video tag.
        </video>
      </div>
    <?php endif; ?>

    <?php if ($lesson['audio_path']): ?>
      <div class="card" style="margin-bottom:20px; display:flex; align-items:center; gap:16px;">
        <span class="icon-chip" style="width:44px;height:44px;font-size:1.2rem;"><i class="fa-solid fa-headphones"></i></span>
        <div style="flex:1;">
          <strong style="display:block; margin-bottom:6px;">Listen to this lesson</strong>
          <audio controls style="width:100%;">
            <source src="<?= e($lesson['audio_path']) ?>" type="audio/mpeg">
            Your browser does not support the audio tag.
          </audio>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!$lesson['video_path'] && !$lesson['audio_path']): ?>
      <div class="alert alert-info">This lesson doesn't have audio or video attached yet — read the text below.</div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:28px;">
      <h3>Lesson content</h3>
      <p class="mb-0" style="white-space:pre-line; color:var(--ink-900);"><?= nl2br(e($lesson['content_text'])) ?></p>
    </div>

    <?php if (is_logged_in()): ?>
      <?php if ($progress && $progress['status'] === 'completed'): ?>
        <div class="alert alert-success">You've completed this lesson — best score: <?= (int)$progress['best_score'] ?>%.</div>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/quiz.php?lesson_id=<?= (int)$lesson['id'] ?>" class="btn btn-gold"><?= t('take_quiz') ?></a>
    <?php else: ?>
      <div class="alert alert-info">
        <a href="<?= BASE_URL ?>/login.php" style="font-weight:700;">Log in</a> or
        <a href="<?= BASE_URL ?>/register.php" style="font-weight:700;">sign up</a> to take the quiz and save your progress.
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
