<?php

require_once __DIR__ . '/../lib/config.php';

session_start();

function ts_require_login(): void
{
    if (empty($_SESSION['ts_admin'])) {
        header('Location: login.php');
        exit;
    }
}

function ts_attempt_login(string $user, string $pass): bool
{
    $admin = ts_config()['admin'];
    if (hash_equals($admin['user'], $user) && password_verify($pass, $admin['pass_hash'])) {
        session_regenerate_id(true);
        $_SESSION['ts_admin'] = $user;
        return true;
    }
    return false;
}
