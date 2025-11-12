<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

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

function recalculate_level(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare(
        'UPDATE users
         SET level = FLOOR((total_typing + total_quiz) / 100)
         WHERE id = :id'
    );
    $stmt->execute(['id' => $userId]);
}
