<?php
declare(strict_types=1);

function database(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('H2O2U_DB_HOST') ?: '127.0.0.1';
    $name = getenv('H2O2U_DB_NAME') ?: 'h2';
    $user = getenv('H2O2U_DB_USER') ?: 'root';
    $password = getenv('H2O2U_DB_PASSWORD') ?: '';

    $connection = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $connection;
}
