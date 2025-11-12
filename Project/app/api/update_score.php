<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$type = $data['type'] ?? '';
$profile = null;

if ($type === 'typing') {
    $typedCount = isset($data['typedSentences']) ? (int) $data['typedSentences'] : 0;
    $bestSpeed = isset($data['bestSpeed']) ? (int) $data['bestSpeed'] : 0;
    $profile = update_typing_progress((int) $user['id'], $typedCount, $bestSpeed);
} elseif ($type === 'quiz') {
    $correct = isset($data['correctAnswers']) ? (int) $data['correctAnswers'] : 0;
    $profile = update_quiz_progress((int) $user['id'], $correct);
} else {
    http_response_code(422);
    echo json_encode(['error' => 'Unknown update type']);
    exit;
}

if ($profile) {
    set_session_user($profile);
}

echo json_encode([
    'success' => (bool) $profile,
    'profile' => $profile,
]);
