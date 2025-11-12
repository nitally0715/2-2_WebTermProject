<?php
declare(strict_types=1);

/**
 * Returns a singleton PDO instance configured from environment variables.
 */
function get_db(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('MYSQL_HOST') ?: 'db';
    $port = (int) (getenv('MYSQL_PORT') ?: 3306);
    $database = getenv('MYSQL_DATABASE') ?: 'jctyping';
    $user = getenv('MYSQL_USER') ?: 'jctyping';
    $password = getenv('MYSQL_PASSWORD') ?: 'secret';

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $database
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);

    return $pdo;
}
