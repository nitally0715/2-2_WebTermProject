<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    header('Location: /main.php');
    exit;
}

$idError = '';
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $success = register_user($username, $password, $formErrors);

    if ($success) {
        header('Location: /main.php');
        exit;
    }

    foreach ($formErrors as $message) {
        if (str_contains($message, 'ID')) {
            $idError = $message;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>정컴타자연습 - 회원가입</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="page-auth">
    <main class="auth-card">
        <h1 class="logo">회원가입</h1>
        <form method="post" class="form auth-form">
            <label>
                <span>ID</span>
                <input type="text" name="username" required minlength="3" autocomplete="username"
                       value="<?= isset($_POST['username']) ? h($_POST['username']) : '' ?>">
                <?php if ($idError): ?>
                    <small class="form-error"><?= h($idError) ?></small>
                <?php endif; ?>
            </label>
            <label>
                <span>PASSWORD</span>
                <input type="password" name="password" required minlength="6" autocomplete="new-password">
            </label>
            <?php if ($formErrors && !$idError): ?>
                <p class="form-error"><?= h($formErrors[0]) ?></p>
            <?php endif; ?>
            <button type="submit" class="primary">Register</button>
        </form>
        <a class="secondary-link" href="/index.php">이미 계정이 있으신가요?</a>
    </main>
</body>
</html>
