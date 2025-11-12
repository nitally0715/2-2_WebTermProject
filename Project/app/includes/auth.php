<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function login_user(string $username, string $password): bool
{
    $name = trim($username);
    if ($name === '' || $password === '') {
        return false;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $name]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    set_session_user($user);

    return true;
}

function register_user(string $username, string $password, array &$errors = []): bool
{
    $errors = [];
    $name = trim($username);

    if ($name === '' || strlen($name) < 3) {
        $errors[] = 'ID는 최소 3자 이상이어야 합니다.';
    }

    if (strlen($password) < 6) {
        $errors[] = '비밀번호는 최소 6자 이상이어야 합니다.';
    }

    if ($errors) {
        return false;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $name]);

    if ($stmt->fetch()) {
        $errors[] = '이미 존재하는 ID입니다.';
        return false;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare(
        'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)'
    );
    $insert->execute([
        'username' => $name,
        'password_hash' => $hash,
    ]);

    $profile = fetch_user_profile((int) $pdo->lastInsertId());
    if ($profile) {
        set_session_user($profile);
    }

    return true;
}

function set_session_user(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'total_typing' => (int) $user['total_typing'],
        'total_quiz' => (int) $user['total_quiz'],
        'level' => (int) $user['level'],
        'highest_speed' => (int) $user['highest_speed'],
        'created_at' => $user['created_at'],
    ];
    session_regenerate_id(true);
}

function current_user(): ?array
{
    /** @var array<string, mixed>|null $user */
    $user = $_SESSION['user'] ?? null;

    return $user;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /index.php');
        exit;
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function refresh_session_user(int $userId): void
{
    $profile = fetch_user_profile($userId);
    if ($profile) {
        set_session_user($profile);
    }
}
