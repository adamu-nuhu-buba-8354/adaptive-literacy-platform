<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$lessons = $pdo->query('
    SELECT l.*, (SELECT COUNT(*) FROM quiz_questions q WHERE q.lesson_id = l.id) AS question_count
    FROM lessons l ORDER BY l.language, l.level, l.sort_order
')->fetchAll();

$pageTitle = 'Manage Lessons';
$activeAdminNav = 'lessons';
require_once __DIR__ . '/_header.php';
?>

<div class="section-head">
  <div>
    <h1 class="mb-0">Lessons</h1>
    <p>Create and manage interactive content and quizzes.</p>
  </div>
  <a href="<?= BASE_URL ?>/admin/add_lesson.php" class="btn btn-gold">+ New lesson</a>
</div>

<?php if (isset($_GET['deleted'])): ?>
  <div class="alert alert-success">Lesson deleted.</div>
<?php elseif (isset($_GET['saved'])): ?>
  <div class="alert alert-success">Lesson saved.</div>
<?php endif; ?>

<div class="card">
  <table class="data-table">
    <thead><tr><th>Title</th><th>Language</th><th>Level</th><th>Media</th><th>Questions</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($lessons as $lesson): ?>
        <tr>
          <td><?= e($lesson['title']) ?></td>
          <td style="text-transform:capitalize;"><?= e($lesson['language']) ?></td>
          <td><span class="badge badge-<?= e($lesson['level']) ?>"><?= e(ucfirst($lesson['level'])) ?></span></td>
          <td>
            <?php if ($lesson['audio_path']): ?><i class="fa-solid fa-headphones"></i><?php endif; ?>
            <?php if ($lesson['video_path']): ?> <i class="fa-solid fa-video"></i><?php endif; ?>
            <?php if (!$lesson['audio_path'] && !$lesson['video_path']): ?><span style="color:var(--ink-300);">Text only</span><?php endif; ?>
          </td>
          <td><?= (int)$lesson['question_count'] ?></td>
          <td style="white-space:nowrap;">
            <a href="<?= BASE_URL ?>/admin/edit_lesson.php?id=<?= (int)$lesson['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
            <a href="<?= BASE_URL ?>/admin/quiz_builder.php?lesson_id=<?= (int)$lesson['id'] ?>" class="btn btn-outline btn-sm">Quiz</a>
            <a href="<?= BASE_URL ?>/admin/delete_lesson.php?id=<?= (int)$lesson['id'] ?>" class="btn btn-outline btn-sm" style="border-color:var(--clay-600); color:var(--clay-600);" onclick="return confirm('Delete this lesson and all its quiz questions and progress records?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$lessons): ?>
        <tr><td colspan="6" class="empty-state">No lessons yet — create your first one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
