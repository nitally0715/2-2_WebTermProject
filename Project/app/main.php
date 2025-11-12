<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    logout_user();
    header('Location: /index.php');
    exit;
}

$sessionUser = current_user();
if ($sessionUser) {
    refresh_session_user((int) $sessionUser['id']);
    $sessionUser = current_user();
}

if (!$sessionUser) {
    header('Location: /index.php');
    exit;
}

$joined = (new DateTime($sessionUser['created_at']))->format('Y-m-d H:i');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>정컴타자연습 - 메인</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="page-main">
    <header class="app-header">
        <div>
            <h1 class="logo">정컴타자연습</h1>
        </div>
        <div class="user-meta">
            <form method="post">
                <button type="submit" name="logout" value="1" class="primary logout-button">Logout</button>
            </form>
        </div>
    </header>

    <main class="dashboard">
        <section class="profile-card">
            <div class="profile-header">
                <h2>Hello, <?= h($sessionUser['username']) ?>!</h2>
                <p class="profile-subtitle">가입일: <?= h($joined) ?></p>
            </div>
            <div class="profile-metrics">
                <div class="profile-row">
                    <article>
                        <span>레벨</span>
                        <p class="metric large"><?= h((string) $sessionUser['level']) ?></p>
                    </article>
                    <article>
                        <span>최고 타수</span>
                        <p class="metric highlight"><?= h((string) $sessionUser['highest_speed']) ?></p>
                    </article>
                </div>
                <div class="profile-row">
                    <article>
                        <span>누적 타수</span>
                        <p class="metric"><?= h((string) $sessionUser['total_typing']) ?></p>
                    </article>
                    <article>
                        <span>누적 퀴즈 수</span>
                        <p class="metric"><?= h((string) $sessionUser['total_quiz']) ?></p>
                    </article>
                </div>
            </div>
        </section>
        <section class="menu-list">
            <a class="menu-card" href="/typing.php">
                <h3>타자연습</h3>
                <p>언어별 문장을 따라 치며 최고 타수를 갱신하세요.</p>
            </a>
            <a class="menu-card" href="/quiz.php">
                <h3>퀴즈</h3>
                <p>함수 설명을 보고 언어별 키워드를 맞춰보세요.</p>
            </a>
            <a class="menu-card" href="/search.php">
                <h3>검색</h3>
                <p>궁금한 내용을 위키에서 빠르게 찾아보세요.</p>
            </a>
        </section>
    </main>
    <script src="/assets/script.js" defer></script>
</body>
</html>
