<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$filterLanguage = $_GET['language'] ?? 'all';
$filterLevel    = $_GET['level'] ?? 'all';

$sql = 'SELECT * FROM lessons WHERE 1=1';
$params = [];
if (in_array($filterLanguage, ['english', 'yoruba', 'pidgin'], true)) {
    $sql .= ' AND language = ?';
    $params[] = $filterLanguage;
}
if (in_array($filterLevel, ['beginner', 'intermediate', 'advanced'], true)) {
    $sql .= ' AND level = ?';
    $params[] = $filterLevel;
}
$sql .= ' ORDER BY language, level, sort_order';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lessons = $stmt->fetchAll();

$progressByLesson = [];
if (is_logged_in()) {
    $p = $pdo->prepare('SELECT lesson_id, status, best_score FROM user_progress WHERE user_id = ?');
    $p->execute([$_SESSION['user_id']]);
    foreach ($p->fetchAll() as $row) {
        $progressByLesson[$row['lesson_id']] = $row;
    }
}

$pageTitle = 'Lessons';
$activeNav = 'lessons';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-bottom:20px;">
  <div class="container">
    <div class="section-head">
      <div>
        <h1 class="mb-0"><?= t('lessons') ?></h1>
        <p>Filter by language or level to find where you should start.</p>
      </div>
    </div>

    <form method="GET" action="" class="card" style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end; margin-bottom:32px;">
      <div class="form-field mb-0" style="min-width:180px;">
        <label for="language">Language</label>
        <select id="language" name="language" onchange="this.form.submit()">
          <option value="all" <?= $filterLanguage === 'all' ? 'selected' : '' ?>>All languages</option>
          <option value="english" <?= $filterLanguage === 'english' ? 'selected' : '' ?>>English</option>
          <option value="yoruba" <?= $filterLanguage === 'yoruba' ? 'selected' : '' ?>>Yorùbá</option>
          <option value="pidgin" <?= $filterLanguage === 'pidgin' ? 'selected' : '' ?>>Pidgin</option>
        </select>
      </div>
      <div class="form-field mb-0" style="min-width:180px;">
        <label for="level">Level</label>
        <select id="level" name="level" onchange="this.form.submit()">
          <option value="all" <?= $filterLevel === 'all' ? 'selected' : '' ?>>All levels</option>
          <option value="beginner" <?= $filterLevel === 'beginner' ? 'selected' : '' ?>>Beginner</option>
          <option value="intermediate" <?= $filterLevel === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
          <option value="advanced" <?= $filterLevel === 'advanced' ? 'selected' : '' ?>>Advanced</option>
        </select>
      </div>
      <noscript><button type="submit" class="btn btn-outline btn-sm">Apply</button></noscript>
    </form>

    <?php if (!$lessons): ?>
      <div class="empty-state card">
        <h3>No lessons match those filters</h3>
        <p class="mb-0">Try a different language or level.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($lessons as $lesson):
          $prog = $progressByLesson[$lesson['id']] ?? null;
        ?>
          <div class="lesson-card">
            <div class="lesson-top">
              <div>
                <span class="badge badge-<?= e($lesson['level']) ?>"><?= e(ucfirst($lesson['level'])) ?></span>
                <span class="badge badge-lang"><?= e(ucfirst($lesson['language'])) ?></span>
              </div>
              <div class="lesson-media-icons" title="Includes audio & video">
                <?php if ($lesson['audio_path']): ?><span class="icon-chip"><i class="fa-solid fa-headphones"></i></span><?php endif; ?>
                <?php if ($lesson['video_path']): ?><span class="icon-chip"><i class="fa-solid fa-clapperboard"></i></span><?php endif; ?>
              </div>
            </div>
            <h3 class="mb-0"><?= e($lesson['title']) ?></h3>
            <p><?= e($lesson['description']) ?></p>

            <?php if ($prog): ?>
              <div class="progress-row">
                <div class="progress-track"><div class="progress-fill" style="width:<?= $prog['status'] === 'completed' ? 100 : ($prog['best_score']) ?>%"></div></div>
                <span class="progress-label"><?= $prog['status'] === 'completed' ? '<i class="fa-solid fa-circle-check" style="color:var(--leaf-600); font-size:0.85em;"></i> Done' : $prog['best_score'] . '%' ?></span>
              </div>
            <?php endif; ?>

            <div class="lesson-footer">
              <span style="font-size:0.8rem; color:var(--ink-300);"><?= e($lesson['topic']) ?></span>
              <a href="<?= BASE_URL ?>/lesson.php?id=<?= (int)$lesson['id'] ?>" class="btn btn-primary btn-sm"><?= $prog ? t('continue') : t('start_lesson') ?></a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
