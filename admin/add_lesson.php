<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$errors = [];
$lesson = null;

function handle_upload(string $field, string $subdir, array $allowedExt): ?string {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson = [
        'title'        => trim($_POST['title'] ?? ''),
        'language'     => $_POST['language'] ?? 'english',
        'level'        => $_POST['level'] ?? 'beginner',
        'topic'        => trim($_POST['topic'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
        'content_text' => trim($_POST['content_text'] ?? ''),
        'sort_order'   => (int)($_POST['sort_order'] ?? 0),
        'audio_path'   => '',
        'video_path'   => '',
    ];

    if ($lesson['title'] === '') $errors['title'] = 'Please enter a title.';

    if (!$errors) {
        $audioPath = handle_upload('audio_file', 'audio', ['mp3']);
        $videoPath = handle_upload('video_file', 'video', ['mp4']);

        $stmt = $pdo->prepare('
            INSERT INTO lessons (title, language, level, topic, description, content_text, audio_path, video_path, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $lesson['title'], $lesson['language'], $lesson['level'], $lesson['topic'],
            $lesson['description'], $lesson['content_text'], $audioPath, $videoPath, $lesson['sort_order'],
        ]);

        header('Location: ' . BASE_URL . '/admin/lessons.php?saved=1');
        exit;
    }
}

$pageTitle = 'New Lesson';
$activeAdminNav = 'lessons';
require_once __DIR__ . '/_header.php';
?>

<div class="section-head">
  <div>
    <h1 class="mb-0">New lesson</h1>
    <p>Add interactive content — you can attach a quiz afterwards.</p>
  </div>
</div>

<?php if ($errors): ?><div class="alert alert-error">Please fix the highlighted fields.</div><?php endif; ?>

<?php
$formAction = BASE_URL . '/admin/add_lesson.php';
require __DIR__ . '/_lesson_form.php';
?>

<?php require_once __DIR__ . '/_footer.php'; ?>
