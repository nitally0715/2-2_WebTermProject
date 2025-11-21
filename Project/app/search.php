<?php
// [화면 3.3: 검색 기능]
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

require_login();

$sessionUser = current_user();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>검색</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="page-activity" data-page="search">
    <header class="app-header slim">
        <div>
            <h1 class="logo">위키검색</h1>
        </div>
        <div class="user-meta">
            <a class="primary nav-button" href="/main.php">Main</a>
        </div>
    </header>

    <main class="search-panel">
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="검색어를 입력하세요" autocomplete="off">
            <button type="button" class="primary" id="search-button">검색</button>
        </div>
        <p class="form-error hidden" id="search-error">검색어를 입력해주세요.</p>
        <section id="search-results" class="search-results"></section>
    </main>

    <script src="/assets/script.js" defer></script>
</body>
</html>
