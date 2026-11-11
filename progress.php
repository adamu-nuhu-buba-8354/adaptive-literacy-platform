<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('
    SELECT l.id, l.title, l.language, l.level, l.topic,
           COALESCE(up.status, "not_started") AS status,
           COALESCE(up.best_score, 0) AS best_score,
           COALESCE(up.attempts, 0) AS attempts
    FROM lessons l
    LEFT JOIN user_progress up ON up.lesson_id = l.id AND up.user_id = ?
    ORDER BY l.language, l.level, l.sort_order
');
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();

$total = count($rows);
$completed = count(array_filter($rows, fn($r) => $r['status'] === 'completed'));
$inProgress = count(array_filter($rows, fn($r) => $r['status'] === 'in_progress'));
$notStarted = $total - $completed - $inProgress;
$overallPercent = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

$pageTitle = 'My Progress';
$activeNav = 'progress';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h1 class="mb-0"><?= t('progress') ?></h1>
        <p>A full breakdown of every lesson and how you're doing.</p>
      </div>
      <a href="<?= BASE_URL ?>/report.php" class="btn btn-outline btn-sm"><?= t('report') ?> &rarr;</a>
    </div>

    <div class="card" style="margin-bottom:28px;">
      <div class="progress-row" style="margin-bottom:14px;">
        <div class="progress-track"><div class="progress-fill" style="width:<?= $overallPercent ?>%"></div></div>
        <span class="progress-label"><?= $overallPercent ?>%</span>
      </div>
      <div class="flex gap-16" style="flex-wrap:wrap; font-size:0.9rem; color:var(--ink-600);">
        <span><i class="fa-solid fa-circle-check" style="color:var(--leaf-600);"></i> <?= $completed ?> completed</span>
        <span><i class="fa-solid fa-circle" style="color:var(--gold-500); font-size:0.75em;"></i> <?= $inProgress ?> in progress</span>
        <span><i class="fa-solid fa-circle" style="color:var(--ink-300); font-size:0.75em;"></i> <?= $notStarted ?> not started</span>
      </div>
    </div>

    <div class="card">
      <table class="data-table">
        <thead>
          <tr><th>Lesson</th><th>Language</th><th>Level</th><th>Status</th><th>Best score</th><th>Attempts</th></tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><a href="<?= BASE_URL ?>/lesson.php?id=<?= (int)$r['id'] ?>" style="font-weight:600; color:var(--indigo-900);"><?= e($r['title']) ?></a></td>
              <td><?= e(ucfirst($r['language'])) ?></td>
              <td><span class="badge badge-<?= e($r['level']) ?>"><?= e(ucfirst($r['level'])) ?></span></td>
              <td>
                <?php if ($r['status'] === 'completed'): ?><i class="fa-solid fa-circle-check" style="color:var(--leaf-600);"></i> Completed
                <?php elseif ($r['status'] === 'in_progress'): ?><i class="fa-solid fa-circle" style="color:var(--gold-500); font-size:0.75em;"></i> In progress
                <?php else: ?><i class="fa-solid fa-circle" style="color:var(--ink-300); font-size:0.75em;"></i> Not started
                <?php endif; ?>
              </td>
              <td><?= (int)$r['best_score'] ?>%</td>
              <td><?= (int)$r['attempts'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
