<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalLessons = (int)$pdo->query('SELECT COUNT(*) FROM lessons')->fetchColumn();
$totalAttempts = (int)$pdo->query('SELECT COUNT(*) FROM quiz_attempts')->fetchColumn();
$avgScore = (int)round((float)$pdo->query('SELECT COALESCE(AVG(score_percent),0) FROM quiz_attempts')->fetchColumn());

$byLanguage = $pdo->query('
    SELECT language, COUNT(*) as cnt FROM lessons GROUP BY language
')->fetchAll();

$topLessons = $pdo->query('
    SELECT l.title, COUNT(up.id) AS learners, COALESCE(AVG(up.best_score),0) AS avg_score
    FROM lessons l LEFT JOIN user_progress up ON up.lesson_id = l.id
    GROUP BY l.id ORDER BY learners DESC LIMIT 5
')->fetchAll();

$recentUsers = $pdo->query('SELECT full_name, email, preferred_language, literacy_level, created_at FROM users ORDER BY created_at DESC LIMIT 6')->fetchAll();

$pageTitle = 'Admin Dashboard';
$activeAdminNav = 'dashboard';
require_once __DIR__ . '/_header.php';
?>

<div class="section-head">
  <div>
    <h1 class="mb-0">Welcome back, <?= e($_SESSION['admin_username']) ?></h1>
    <p>An overview of how the platform is being used.</p>
  </div>
  <a href="<?= BASE_URL ?>/admin/add_lesson.php" class="btn btn-gold">+ New lesson</a>
</div>

<div class="grid grid-3" style="margin-bottom:32px;">
  <div class="card stat-card"><strong><?= $totalUsers ?></strong><span>Registered learners</span></div>
  <div class="card stat-card"><strong><?= $totalLessons ?></strong><span>Published lessons</span></div>
  <div class="card stat-card"><strong><?= $totalAttempts ?></strong><span>Quiz attempts (avg score <?= $avgScore ?>%)</span></div>
</div>

<div class="grid grid-2" style="margin-bottom:32px; align-items:start;">
  <div class="card">
    <h3>Lessons by language</h3>
    <?php foreach ($byLanguage as $row):
      $pct = $totalLessons > 0 ? round(($row['cnt'] / $totalLessons) * 100) : 0;
    ?>
      <div style="margin-bottom:12px;">
        <div class="flex justify-between" style="font-size:0.9rem; margin-bottom:4px;">
          <span style="text-transform:capitalize;"><?= e($row['language']) ?></span>
          <span><?= (int)$row['cnt'] ?></span>
        </div>
        <div class="progress-track"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h3>Most-engaged lessons</h3>
    <?php if (!$topLessons): ?>
      <p class="mb-0">No learner activity yet.</p>
    <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Lesson</th><th>Learners</th><th>Avg score</th></tr></thead>
        <tbody>
          <?php foreach ($topLessons as $l): ?>
            <tr>
              <td><?= e($l['title']) ?></td>
              <td><?= (int)$l['learners'] ?></td>
              <td><?= (int)round($l['avg_score']) ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <h3>Newest learners</h3>
  <table class="data-table">
    <thead><tr><th>Name</th><th>Email</th><th>Language</th><th>Level</th><th>Joined</th></tr></thead>
    <tbody>
      <?php foreach ($recentUsers as $u): ?>
        <tr>
          <td><?= e($u['full_name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td style="text-transform:capitalize;"><?= e($u['preferred_language']) ?></td>
          <td style="text-transform:capitalize;"><?= e($u['literacy_level']) ?></td>
          <td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
