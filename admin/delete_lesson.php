<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$lessonId = (int)($_GET['id'] ?? 0);
if ($lessonId) {
    $stmt = $pdo->prepare('DELETE FROM lessons WHERE id = ?');
    $stmt->execute([$lessonId]);
}

header('Location: ' . BASE_URL . '/admin/lessons.php?deleted=1');
exit;
