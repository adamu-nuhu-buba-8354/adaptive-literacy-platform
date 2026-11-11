<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = current_user($pdo)['id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC');
    $stmt->execute([$userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Auto-welcome message if none exist
    if (empty($messages)) {
        $messages[] = [
            'id' => 0,
            'message' => 'Hello! How can we help you with your learning today?',
            'is_admin_reply' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    echo json_encode(['status' => 'success', 'messages' => $messages]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message = trim($data['message'] ?? '');
    
    if ($message === '') {
        echo json_encode(['error' => 'Message is empty']);
        exit;
    }
    
    $stmt = $pdo->prepare('INSERT INTO chat_messages (user_id, message) VALUES (?, ?)');
    $stmt->execute([$userId, $message]);
    
    echo json_encode(['status' => 'success', 'message' => 'Message saved']);
    exit;
}

echo json_encode(['error' => 'Invalid method']);
