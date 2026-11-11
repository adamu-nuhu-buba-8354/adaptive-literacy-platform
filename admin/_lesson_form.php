<?php
/**
 * Reusable lesson form. Expects:
 *   $lesson       - array of existing values (or null for a new lesson)
 *   $formAction   - URL the form posts to
 *   $errors       - assoc array of field => message
 */
$lesson = $lesson ?? [
    'title' => '', 'language' => 'english', 'level' => 'beginner', 'topic' => '',
    'description' => '', 'content_text' => '', 'audio_path' => '', 'video_path' => '', 'sort_order' => 0,
];
$errors = $errors ?? [];
?>
<form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data" id="adminLessonForm" class="card">
  <div class="grid grid-2">
    <div class="form-field <?= isset($errors['title']) ? 'invalid' : '' ?>">
      <label for="title">Lesson title</label>
      <input type="text" id="title" name="title" required value="<?= e($lesson['title']) ?>">
      <span class="form-error"><?= e($errors['title'] ?? 'Please enter a title.') ?></span>
    </div>
    <div class="form-field">
      <label for="topic">Topic</label>
      <input type="text" id="topic" name="topic" required value="<?= e($lesson['topic']) ?>" placeholder="e.g. Greetings, Life Skills">
    </div>
  </div>

  <div class="grid grid-2">
    <div class="form-field">
      <label for="language">Language</label>
      <select id="language" name="language" required>
        <?php foreach (['english' => 'English', 'yoruba' => 'Yorùbá', 'pidgin' => 'Pidgin'] as $val => $label): ?>
          <option value="<?= $val ?>" <?= $lesson['language'] === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-field">
      <label for="level">Level</label>
      <select id="level" name="level" required>
        <?php foreach (['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'] as $val => $label): ?>
          <option value="<?= $val ?>" <?= $lesson['level'] === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-field">
    <label for="description">Short description</label>
    <textarea id="description" name="description" rows="2" required><?= e($lesson['description']) ?></textarea>
  </div>

  <div class="form-field">
    <label for="content_text">Lesson text content</label>
    <textarea id="content_text" name="content_text" rows="5" required><?= e($lesson['content_text']) ?></textarea>
    <span class="form-hint">This is what learners read on the lesson page.</span>
  </div>

  <div class="grid grid-2">
    <div class="form-field">
      <label for="audio_file">Audio file <span style="font-weight:400;color:var(--ink-300);">(mp3, optional)</span></label>
      <input type="file" id="audio_file" name="audio_file" accept="audio/mpeg,audio/mp3">
      <?php if (!empty($lesson['audio_path'])): ?><span class="form-hint">Current: <?= e($lesson['audio_path']) ?></span><?php endif; ?>
    </div>
    <div class="form-field">
      <label for="video_file">Video file <span style="font-weight:400;color:var(--ink-300);">(mp4, optional)</span></label>
      <input type="file" id="video_file" name="video_file" accept="video/mp4">
      <?php if (!empty($lesson['video_path'])): ?><span class="form-hint">Current: <?= e($lesson['video_path']) ?></span><?php endif; ?>
    </div>
  </div>

  <div class="form-field" style="max-width:160px;">
    <label for="sort_order">Sort order</label>
    <input type="number" id="sort_order" name="sort_order" value="<?= (int)$lesson['sort_order'] ?>">
  </div>

  <button type="submit" class="btn btn-primary">Save lesson</button>
  <a href="<?= BASE_URL ?>/admin/lessons.php" class="btn btn-outline">Cancel</a>
</form>
