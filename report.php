<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user($pdo);
$userId = $user['id'];

$stmt = $pdo->prepare('SELECT COUNT(*) FROM lessons');
$stmt->execute();
$totalLessons = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*), COALESCE(AVG(best_score),0) FROM user_progress WHERE user_id = ? AND status = "completed"');
$stmt->execute([$userId]);
[$completed, $avgScore] = $stmt->fetch(PDO::FETCH_NUM);
$completed = (int)$completed;
$avgScore = (int)round($avgScore);

$stmt = $pdo->prepare('
    SELECT l.title, l.language, l.level, up.best_score, up.attempts, up.completed_at
    FROM user_progress up JOIN lessons l ON l.id = up.lesson_id
    WHERE up.user_id = ? AND up.status = "completed"
    ORDER BY up.completed_at DESC
');
$stmt->execute([$userId]);
$completedLessons = $stmt->fetchAll();

$overallPercent = $totalLessons > 0 ? (int)round(($completed / $totalLessons) * 100) : 0;
$qualifiesForCertificate = $totalLessons > 0 && $completed >= $totalLessons && $totalLessons > 0;
// Also allow a "level certificate" once 3+ lessons completed with a strong average
$qualifiesForLevelCertificate = $completed >= 3 && $avgScore >= 70;

$pageTitle = 'My Report';
$activeNav = 'report';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section no-print">
  <div class="container">
    <div class="section-head">
      <div>
        <h1 class="mb-0"><?= t('report') ?></h1>
        <p>A summary you can print or save as a record of your learning.</p>
      </div>
      <button onclick="window.print()" class="btn btn-primary btn-sm">Print report</button>
    </div>

    <div class="grid grid-3" style="margin-bottom:32px;">
      <div class="card stat-card"><strong><?= $completed ?>/<?= $totalLessons ?></strong><span>Lessons completed</span></div>
      <div class="card stat-card"><strong><?= $avgScore ?>%</strong><span>Average score (completed lessons)</span></div>
      <div class="card stat-card"><strong><?= $overallPercent ?>%</strong><span>Overall course progress</span></div>
    </div>

    <div class="card" style="margin-bottom:32px;">
      <h3>Completed lessons</h3>
      <?php if (!$completedLessons): ?>
        <p class="mb-0">No completed lessons yet. Score 60% or higher on a quiz to mark a lesson complete.</p>
      <?php else: ?>
        <table class="data-table">
          <thead><tr><th>Lesson</th><th>Language</th><th>Level</th><th>Score</th><th>Attempts</th><th>Completed</th></tr></thead>
          <tbody>
            <?php foreach ($completedLessons as $r): ?>
              <tr>
                <td><?= e($r['title']) ?></td>
                <td><?= e(ucfirst($r['language'])) ?></td>
                <td><span class="badge badge-<?= e($r['level']) ?>"><?= e(ucfirst($r['level'])) ?></span></td>
                <td><?= (int)$r['best_score'] ?>%</td>
                <td><?= (int)$r['attempts'] ?></td>
                <td><?= $r['completed_at'] ? e(date('d M Y', strtotime($r['completed_at']))) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <?php if (!$qualifiesForLevelCertificate): ?>
      <div class="alert alert-info">Complete at least 3 lessons with an average score of 70% or higher to unlock a printable certificate.</div>
    <?php endif; ?>
  </div>
</section>

<?php if ($qualifiesForLevelCertificate): ?>
<div class="certificate">
  <div class="cert-eyebrow">CERTIFICATE OF ACHIEVEMENT</div>
  <h1>Adaptive Literacy Learning Platform</h1>
  <p class="mb-0" style="color:var(--ink-600);">This certifies that</p>
  <div class="cert-name"><?= e($user['full_name']) ?></div>
  <p class="mb-0" style="color:var(--ink-600);">has successfully completed <?= $completed ?> lessons at the <strong style="text-transform:capitalize;"><?= e($user['literacy_level']) ?></strong> level with an average score of <?= $avgScore ?>%.</p>
  <p class="cert-meta" style="margin-top:24px;">Issued <?= date('d F Y') ?></p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
