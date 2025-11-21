<?php
// 공용함수
// 1. HTML 출력용 보안 처리 
// 2. 유저 정보 조회
// 3. 타자문장 / 퀴즈문제 불러오기
// 4. 타자 / 퀴즈 완료 후 DB 업데이트
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// HTML escape [XSS 보안 필수 함수]
function h(?string $value): string
{   // <script> 같은 악성 문자열 그대로 태그로 해석되지 않도록 막아줌
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 유저 프로필 가져오기
function fetch_user_profile(int $userId): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT id, username, total_typing, total_quiz, level, highest_speed, created_at
         FROM users WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

// 언어 목록 조회
function get_languages(): array
{
    $pdo = get_db();
    $stmt = $pdo->query('SELECT DISTINCT language FROM typing_sentences ORDER BY language');
    $languages = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    if ($languages) {
        return $languages;
    }

    $fallback = $pdo->query('SELECT DISTINCT language FROM quiz_questions ORDER BY language');

    return $fallback->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

// 타자 연습 문장 가져오기
function get_typing_sentences_indexed(): array
{
    $pdo = get_db();
    $stmt = $pdo->query('SELECT language, sentence FROM typing_sentences ORDER BY language, id');
    $result = [];

    foreach ($stmt as $row) {
        $language = $row['language'];
        if (!isset($result[$language])) {
            $result[$language] = [];
        }
        $result[$language][] = $row['sentence'];
    }

    return $result;
}

// 퀴즈 문제 가져오기
function get_quiz_questions_indexed(): array
{
    $pdo = get_db();
    $stmt = $pdo->query(
        'SELECT language, keyword, description FROM quiz_questions ORDER BY language, id'
    );
    $result = [];

    foreach ($stmt as $row) {
        $language = $row['language'];
        if (!isset($result[$language])) {
            $result[$language] = [];
        }
        $result[$language][] = [
            'keyword' => $row['keyword'],
            'description' => $row['description'],
        ];
    }

    return $result;
}

// 타자 진행도 업데이트
// logic: 
function update_typing_progress(int $userId, int $sentencesCompleted, int $bestSpeed): ?array
{
    $typed = max(0, $sentencesCompleted);
    $speed = max(0, $bestSpeed);

    $pdo = get_db();

    $stmt = $pdo->prepare(
        'UPDATE users
         SET total_typing = total_typing + :typed,
             highest_speed = GREATEST(highest_speed, :speed)
         WHERE id = :id'
    );
    $stmt->execute([
        'typed' => $typed,
        'speed' => $speed,
        'id' => $userId,
    ]);

    recalculate_level($pdo, $userId);

    return fetch_user_profile($userId);
}

// 퀴즈 진행도 업데이트
// logic: 퀴즈 1문제 맞추면 total_quiz += 1
function update_quiz_progress(int $userId, int $correctAnswers): ?array
{
    $correct = max(0, $correctAnswers);
    $pdo = get_db();

    $stmt = $pdo->prepare(
        'UPDATE users
         SET total_quiz = total_quiz + :correct
         WHERE id = :id'
    );
    $stmt->execute([
        'correct' => $correct,
        'id' => $userId,
    ]);

    recalculate_level($pdo, $userId);

    return fetch_user_profile($userId);
}

// 레벨 재계산
// logic: 타자 + 퀴즈 합 50 당 레벨 1 상승
function recalculate_level(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare(
        'UPDATE users
         SET level = FLOOR((total_typing + total_quiz) / 50)
         WHERE id = :id'
    );
    $stmt->execute(['id' => $userId]);
}

function get_level_ranking(int $limit = 3): array
{
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT id, username, level, highest_speed
         FROM users
         ORDER BY level DESC, highest_speed DESC, id ASC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

function get_speed_ranking(int $limit = 3): array
{
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT id, username, highest_speed, level
         FROM users
         ORDER BY highest_speed DESC, level DESC, id ASC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}
