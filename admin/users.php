<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$users = $pdo->query('
    SELECT u.*,
      (SELECT COUNT(*) FROM user_progress up WHERE up.user_id = u.id AND up.status = "completed") AS completed_count,
      (SELECT COALESCE(AVG(best_score),0) FROM user_progress up WHERE up.user_id = u.id AND up.attempts > 0) AS avg_score
    FROM users u ORDER BY u.created_at DESC
')->fetchAll();

$pageTitle = 'Learners';
$activeAdminNav = 'users';
require_once __DIR__ . '/_header.php';
?>

<div class="section-head">
  <div>
    <h1 class="mb-0">Learners</h1>
    <p>Everyone registered on the platform and how they're progressing.</p>
  </div>
</div>

<div class="card">
  <table class="data-table">
    <thead><tr><th>Name</th><th>Email</th><th>Language</th><th>Level</th><th>Completed</th><th>Avg score</th><th>Joined</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['full_name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td style="text-transform:capitalize;"><?= e($u['preferred_language']) ?></td>
          <td><span class="badge badge-<?= e($u['literacy_level']) ?>"><?= e(ucfirst($u['literacy_level'])) ?></span></td>
          <td><?= (int)$u['completed_count'] ?></td>
          <td><?= (int)round($u['avg_score']) ?>%</td>
          <td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?>
        <tr><td colspan="7" class="empty-state">No learners have registered yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
