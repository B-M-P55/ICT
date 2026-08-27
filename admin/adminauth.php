<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_admin(): void
{
    start_session();

    if (empty($_SESSION['adminID'])) {
        header('Location: ../php/admin_login.php');
        exit;
    }
}

function current_admin_id(): int
{
    start_session();
    return (int) ($_SESSION['adminID'] ?? 0);
}
