<?php

define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'shifaa_dizad');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int) (getenv('DB_PORT') ?: 3306));
define('DB_SOCK', getenv('DB_SOCK') ?: '/home/runner/mysql-run/mysql.sock');

function dbUseSocket(): bool
{
    return DB_SOCK !== '' && @file_exists(DB_SOCK);
}

function getDB(): mysqli
{
    if (dbUseSocket()) {
        $conn = new mysqli(null, DB_USER, DB_PASS, DB_NAME, null, DB_SOCK);
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    }

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

function getDbConnection(): PDO
{
    try {
        if (dbUseSocket()) {
            $dsn = 'mysql:unix_socket=' . DB_SOCK . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        }

        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}