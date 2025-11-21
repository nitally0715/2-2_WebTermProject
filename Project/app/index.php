<?php
// [화면 1.1: 가장 시작 화면]
declare(strict_types=1);

// auto.php 세션 시작, DB 접근, 로그인 상태 확인 등 인증 관련 함수 로딩
require_once __DIR__ . '/includes/auth.php';

// 이미 로그인한 유저는 main.php로 redirect 
if (current_user()) {
    header('Location: /main.php');
    exit;
}

// Login 처리 [폼 제출된 경우]
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login_user($username, $password)) {
        header('Location: /main.php');
        exit;
    }

    $error = 'ID 또는 비밀번호가 올바르지 않습니다.';
}
?>

<!DOCTYPE html> 
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>정컴타자연습 - 로그인</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="page-auth">
    <main class="auth-card">
        <h1 class="logo">정컴타자연습</h1>
        <form method="post" class="form auth-form">
            <label>
                <span>USERNAME</span>
                <input type="text" name="username" required autocomplete="username">
            </label>
            <label>
                <span>PASSWORD</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <?php if ($error): ?>
                <p class="form-error"><?= h($error) ?></p>
            <?php endif; ?>
            <button type="submit" class="primary">Login</button>
        </form>
        <a class="secondary-link" href="/register.php">Register</a>
    </main>
</body>
</html>
