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

$languages = get_languages();
$sentences = get_typing_sentences_indexed();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>타자연습</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="page-activity" data-page="typing">
    <header class="app-header slim">
        <div>
            <h1 class="logo">타자연습</h1>
        </div>
        <div class="user-meta">
            <a class="primary nav-button" href="/main.php">Main</a>
        </div>
    </header>

    <main id="typing-app">
        <section data-section="select">
            <h2>언어 선택</h2>
            <p>학습할 언어를 최소 1개 선택하세요.</p>
            <div class="chip-list" id="typing-languages">
                <?php foreach ($languages as $language): ?>
                    <label class="chip">
                        <input type="checkbox" name="typing-language" value="<?= h($language) ?>">
                        <span><?= h($language) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="form-error hidden" id="typing-language-error">언어를 하나 이상 선택해야 합니다.</p>
            <div class="action-row">
                <button type="button" class="primary" id="typing-start">게임 시작</button>
                <button type="button" class="ghost-button" onclick="window.location.href='/main.php'">나가기</button>
            </div>
        </section>

        <section data-section="game" class="hidden">
            <div class="game-stats">
                <div>
                    <span>현재 타수</span>
                    <p id="typing-current">0</p>
                </div>
                <div>
                    <span>최고 타수</span>
                    <p id="typing-best">0</p>
                </div>
                <div>
                    <span>진행도</span>
                    <p id="typing-progress">0/0</p>
                </div>
            </div>
            <div class="typing-card">
                <p class="language-label" id="typing-language-label"></p>
                <p class="prompt" id="typing-prompt"></p>
                <small id="typing-counter"></small>
                <input type="text" id="typing-input" autocomplete="off" placeholder="문장을 그대로 입력 후 Enter">
                <p class="form-error hidden" id="typing-input-error">입력값이 일치하지 않습니다.</p>
            </div>
            <div class="action-row">
                <button type="button" class="secondary" id="typing-exit">나가기</button>
            </div>
        </section>

        <section data-section="summary" class="hidden">
            <h2>Well Done!</h2>
            <p>평균 타수 <strong id="typing-summary-average">0</strong></p>
            <p>최고 타수 <strong id="typing-summary-best">0</strong></p>
            <div class="action-row">
                <button type="button" class="primary" id="typing-restart">다시하기</button>
                <a class="secondary-link" href="/main.php">메인화면</a>
            </div>
        </section>
    </main>

    <script>
        window.typingData = {
            sentences: <?= json_encode($sentences, JSON_UNESCAPED_UNICODE) ?>,
        };
    </script>
    <script src="/assets/script.js" defer></script>
</body>
</html>
