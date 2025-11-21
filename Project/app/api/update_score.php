<?php
// API logic: 게임 종료 시 점수 받아서 DB 갱신, 최신 프로필 세션에 재등록
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// GET으로 접근하면 405 [POST로만 호출]
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Login 안 된 상태에서는 점수 업데이트 불가 
$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

//JSON 파싱 실패시 400 Bad Request
$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$type = $data['type'] ?? '';
$profile = null;

// 타입에 따른 점수 업데이트 [타자연습 / 퀴즈] 이 외의 타입은 422
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

// 세션 최신화
if ($profile) {
    set_session_user($profile);
}

// 최종 응답
echo json_encode([
    'success' => (bool) $profile,
    'profile' => $profile,
]);
