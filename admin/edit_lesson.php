<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$lessonId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    header('Location: ' . BASE_URL . '/admin/lessons.php');
    exit;
}

function handle_upload_edit(string $field, string $subdir, array $allowedExt): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) return null;

    $destDir = __DIR__ . '/../uploads/' . $subdir . '/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $filename = uniqid($subdir . '_') . '.' . $ext;
    move_uploaded_file($_FILES[$field]['tmp_name'], $destDir . $filename);

    return BASE_URL . '/uploads/' . $subdir . '/' . $filename;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $language    = $_POST['language'] ?? 'english';
    $level       = $_POST['level'] ?? 'beginner';
    $topic       = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $contentText = trim($_POST['content_text'] ?? '');
    $sortOrder   = (int)($_POST['sort_order'] ?? 0);

    if ($title === '') $errors['title'] = 'Please enter a title.';

    if (!$errors) {
        $audioPath = handle_upload_edit('audio_file', 'audio', ['mp3']) ?? $lesson['audio_path'];
        $videoPath = handle_upload_edit('video_file', 'video', ['mp4']) ?? $lesson['video_path'];

        $stmt = $pdo->prepare('
            UPDATE lessons SET title=?, language=?, level=?, topic=?, description=?, content_text=?, audio_path=?, video_path=?, sort_order=?
            WHERE id = ?
        ');
        $stmt->execute([$title, $language, $level, $topic, $description, $contentText, $audioPath, $videoPath, $sortOrder, $lessonId]);

        header('Location: ' . BASE_URL . '/admin/lessons.php?saved=1');
        exit;
    }
    $lesson = array_merge($lesson, compact('title', 'language', 'level', 'topic', 'description', 'sortOrder'));
    $lesson['content_text'] = $contentText;
}

$pageTitle = 'Edit Lesson';
$activeAdminNav = 'lessons';
require_once __DIR__ . '/_header.php';
?>

<div class="section-head">
  <div>
    <h1 class="mb-0">Edit lesson</h1>
    <p>Update content, media or difficulty level.</p>
  </div>
  <a href="<?= BASE_URL ?>/admin/quiz_builder.php?lesson_id=<?= (int)$lessonId ?>" class="btn btn-outline btn-sm">Manage quiz &rarr;</a>
</div>

<?php if ($errors): ?><div class="alert alert-error">Please fix the highlighted fields.</div><?php endif; ?>

<?php
$formAction = BASE_URL . '/admin/edit_lesson.php?id=' . $lessonId;
require __DIR__ . '/_lesson_form.php';
?>

<?php require_once __DIR__ . '/_footer.php'; ?>
