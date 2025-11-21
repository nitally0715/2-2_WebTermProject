<?php
// [화면 2: login시 사용자 화면]
declare(strict_types=1);

// 로그인 여부 확인, 세션 유저, 로그아웃 기능 및 h() 등 공용 함수 가져오기
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// 로그인 안 되어 있으면 index.php로 redirect
require_login();

// logout 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    logout_user();
    header('Location: /index.php');
    exit;
}

// user 정보 로딩(점수 업데이트 등 DB 최신 정보 실시간으로 세션에 반영)
$sessionUser = current_user();
if ($sessionUser) {
    refresh_session_user((int) $sessionUser['id']);
    $sessionUser = current_user();
}

if (!$sessionUser) {
    header('Location: /index.php');
    exit;
}

// 가입 날짜
$joined = (new DateTime($sessionUser['created_at']))->format('Y-m-d H:i');
$levelRanking = get_level_ranking(3);
$speedRanking = get_speed_ranking(3);
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
                <div class="profile-title">
                    <h2>Hello, <?= h($sessionUser['username']) ?>!</h2>
                    <p class="profile-subtitle">가입일: <?= h($joined) ?></p>
                </div>
                <button type="button" class="secondary ghost-button" id="open-ranking">랭킹 보기</button>
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
    <div class="ranking-overlay hidden" id="ranking-overlay" aria-hidden="true">
        <div class="ranking-modal" role="dialog" aria-modal="true" aria-labelledby="ranking-title">
            <div class="ranking-modal__header">
                <h3 id="ranking-title">랭킹</h3>
                <button type="button" class="icon-button" id="ranking-close" aria-label="랭킹 닫기">&times;</button>
            </div>
            <div class="ranking-columns">
                <div class="ranking-group">
                    <h4>레벨 Top 3</h4>
                    <ol class="ranking-list">
                        <?php if ($levelRanking): ?>
                            <?php foreach ($levelRanking as $i => $row): ?>
                                <?php $isMe = (int) $row['id'] === (int) $sessionUser['id']; ?>
                                <li class="<?= $isMe ? 'is-me' : '' ?>">
                                    <span class="rank-num"><?= $i + 1 ?></span>
                                    <span class="rank-name"><?= h($row['username']) ?></span>
                                    <span class="rank-score"><?= h((string) $row['level']) ?> 레벨</span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="empty">랭킹 정보가 없습니다.</li>
                        <?php endif; ?>
                    </ol>
                </div>
                <div class="ranking-group">
                    <h4>최고 타수 Top 3</h4>
                    <ol class="ranking-list">
                        <?php if ($speedRanking): ?>
                            <?php foreach ($speedRanking as $i => $row): ?>
                                <?php $isMe = (int) $row['id'] === (int) $sessionUser['id']; ?>
                                <li class="<?= $isMe ? 'is-me' : '' ?>">
                                    <span class="rank-num"><?= $i + 1 ?></span>
                                    <span class="rank-name"><?= h($row['username']) ?></span>
                                    <span class="rank-score"><?= h((string) $row['highest_speed']) ?> CPM</span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="empty">랭킹 정보가 없습니다.</li>
                        <?php endif; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/script.js" defer></script>
</body>
</html>
