<?php
// 1. 세션관리
// 2. 로그인 처리(login_user)
// 3. 회원가입 처리(register_user)
// 4. 유저 정보 세션에 기록/갱신(set_session_user / refresh_session_user)
// 5. 로그인 보호(require_login), 로그아웃(logout_user)

// 타입 자동 변환 막아줌 [안전성]
declare(strict_types=1);
// 시간 한국 기준으로 설정
date_default_timezone_set('Asia/Seoul');
// 공용 함수 
require_once __DIR__ . '/functions.php';

// 세션 중복 호출 방지
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그인 처리
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

// 회원가입처리
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

    // 비밀번호 암호화 후 insert
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

// 세션에 사용자 정보 저장
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

// 현재 로그인된 유저 정보 가져오는 함수
function current_user(): ?array
{
    /* @var array<string, mixed>|null $user */
    $user = $_SESSION['user'] ?? null;

    return $user;
}

// 로그인 필수 보호[로그인 안 되어 있으면 index.php로 강제 이동]
function require_login(): void
{
    if (!current_user()) {
        header('Location: /index.php');
        exit;
    }
}

// 로그아웃 처리 [세션 데이터 삭제, 쿠키 기반 세션 ID 무효화, 세션 종료]
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

// 유저 정보 최신화 [레벨, 최고타수, 누적타수 등]
function refresh_session_user(int $userId): void
{
    $profile = fetch_user_profile($userId);
    if ($profile) {
        set_session_user($profile);
    }
}
