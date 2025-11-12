<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$sessionUser = current_user();
if ($sessionUser) {
    refresh_session_user((int) $sessionUser['id']);
    $sessionUser = current_user();
}

$questions = get_quiz_questions_indexed();
$quizLanguages = array_keys($questions);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>퀴즈</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="page-activity" data-page="quiz">
    <header class="app-header slim">
        <div>
            <h1 class="logo">퀴즈</h1>
        </div>
        <div class="user-meta">
            <a class="primary nav-button" href="/main.php">Main</a>
        </div>
    </header>

    <main id="quiz-app">
        <section data-section="select">
            <h2>언어 선택</h2>
            <p>학습할 언어를 선택하세요.</p>
            <div class="chip-list" id="quiz-languages">
                <?php foreach ($quizLanguages as $language): ?>
                    <label class="chip">
                        <input type="checkbox" name="quiz-language" value="<?= h($language) ?>">
                        <span><?= h($language) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="form-error hidden" id="quiz-language-error">언어를 하나 이상 선택해야 합니다.</p>
            <div class="action-row">
                <button type="button" class="primary" id="quiz-start">게임 시작</button>
                <button type="button" class="ghost-button" onclick="window.location.href='/main.php'">나가기</button>
            </div>
        </section>

        <section data-section="game" class="hidden">
            <div class="game-stats">
                <div>
                    <span>정답</span>
                    <p id="quiz-correct">0</p>
                </div>
                <div>
                    <span>오답</span>
                    <p id="quiz-wrong">0</p>
                </div>
                <div>
                    <span>진행도</span>
                    <p id="quiz-progress">0/0</p>
                </div>
            </div>
            <div class="typing-card">
                <p class="language-label" id="quiz-language-label"></p>
                <p class="prompt" id="quiz-prompt"></p>
                <small id="quiz-counter"></small>
                <input type="text" id="quiz-input" autocomplete="off" placeholder="정답을 입력하고 Enter">
                <p class="form-error hidden" id="quiz-feedback"></p>
            </div>
            <div class="action-row">
                <button type="button" class="secondary" id="quiz-exit">나가기</button>
            </div>
        </section>

        <section data-section="summary" class="hidden">
            <h2>Well Done!</h2>
            <p>정답 <strong id="quiz-summary-correct">0</strong> · 오답 <strong id="quiz-summary-wrong">0</strong></p>
            <div class="action-row">
                <button type="button" class="primary" id="quiz-restart">다시하기</button>
                <a class="secondary-link" href="/main.php">메인화면</a>
            </div>
        </section>
    </main>

    <script>
        window.quizData = {
            questions: <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>
        };
    </script>
    <script src="/assets/script.js" defer></script>
</body>
</html>
