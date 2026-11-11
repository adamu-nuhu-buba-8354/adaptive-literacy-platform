<?php
/**
 * Admin-only AI Quiz Generator API
 * Uses Groq (free, fast, reliable) with Llama 3.3 70B
 * POST  { lesson_id: int }
 * Returns JSON array of MCQ objects on success.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

header('Content-Type: application/json');

// ── 1. Validate input ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$lessonId = (int)($input['lesson_id'] ?? 0);

if (!$lessonId) {
    http_response_code(400);
    echo json_encode(['error' => 'lesson_id is required']);
    exit;
}

// ── 2. Load lesson content ─────────────────────────────────────────────────
$stmt = $pdo->prepare('SELECT title, content_text FROM lessons WHERE id = ?');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    http_response_code(404);
    echo json_encode(['error' => 'Lesson not found']);
    exit;
}

$contentText = trim($lesson['content_text'] ?? '');
if (strlen($contentText) < 20) {
    http_response_code(422);
    echo json_encode(['error' => 'Lesson content is too short to generate questions. Please add more text to the lesson first.']);
    exit;
}

// ── 3. Check API key ───────────────────────────────────────────────────────
if (!defined('GROQ_API_KEY') || GROQ_API_KEY === '') {
    http_response_code(500);
    echo json_encode(['error' => 'GROQ_API_KEY is not configured in config/db.php']);
    exit;
}

// ── 4. Build prompt ────────────────────────────────────────────────────────
$prompt = <<<PROMPT
You are a literacy-education expert. Read the lesson text below and generate exactly 5 multiple-choice questions to test the learner's comprehension.

Rules:
- Each question must be clearly answerable from the lesson text alone.
- Keep language simple for adult literacy learners.
- Each option (a, b, c, d) must be a short, distinct answer.
- Reply ONLY with a valid JSON array — no markdown fences, no explanation, no extra text.
- Each array element must have exactly these keys: question, option_a, option_b, option_c, option_d, correct_option
- correct_option must be one of: "a", "b", "c", "d"

Lesson title: {$lesson['title']}

Lesson text:
{$contentText}
PROMPT;

$requestBody = json_encode([
    'model'       => 'openai/gpt-oss-120b',
    'messages'    => [
        [
            'role'    => 'system',
            'content' => 'You are a literacy education expert that generates quiz questions. Always respond with valid JSON only — no markdown, no explanation.',
        ],
        [
            'role'    => 'user',
            'content' => $prompt,
        ],
    ],
    'temperature' => 0.4,
    'max_tokens'  => 1024,
]);

// ── 5. Call Groq API ───────────────────────────────────────────────────────
$apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $requestBody,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
    ],
    CURLOPT_TIMEOUT => 30,
]);

$rawResponse = curl_exec($ch);
$httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError   = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    echo json_encode(['error' => 'Network error: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    $decoded = json_decode($rawResponse, true);
    $msg = $decoded['error']['message'] ?? "API returned HTTP {$httpCode}";
    http_response_code(502);
    echo json_encode(['error' => $msg]);
    exit;
}

// ── 6. Parse response ──────────────────────────────────────────────────────
$groqData = json_decode($rawResponse, true);
$rawText  = $groqData['choices'][0]['message']['content'] ?? '';

// Strip any accidental markdown fences
$rawText = preg_replace('/^```(?:json)?\s*/i', '', trim($rawText));
$rawText = preg_replace('/\s*```$/', '', $rawText);
$rawText = trim($rawText);

$questions = json_decode($rawText, true);

if (!is_array($questions) || count($questions) === 0) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Could not parse questions from AI response. Please try again.',
        'raw'   => substr($rawText, 0, 300),
    ]);
    exit;
}

// ── 7. Validate & sanitise each question ──────────────────────────────────
$valid = [];
foreach ($questions as $q) {
    if (
        !empty($q['question'])  &&
        !empty($q['option_a'])  &&
        !empty($q['option_b'])  &&
        !empty($q['option_c'])  &&
        !empty($q['option_d'])  &&
        in_array($q['correct_option'] ?? '', ['a','b','c','d'], true)
    ) {
        $valid[] = [
            'question'       => substr(trim($q['question']), 0, 500),
            'option_a'       => substr(trim($q['option_a']), 0, 255),
            'option_b'       => substr(trim($q['option_b']), 0, 255),
            'option_c'       => substr(trim($q['option_c']), 0, 255),
            'option_d'       => substr(trim($q['option_d']), 0, 255),
            'correct_option' => $q['correct_option'],
        ];
    }
}

if (empty($valid)) {
    http_response_code(502);
    echo json_encode(['error' => 'AI returned questions in an unexpected format. Try again.']);
    exit;
}

echo json_encode(['questions' => $valid]);
