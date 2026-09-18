<?php

session_start();

function ts_client_require_login(): void
{
    if (empty($_SESSION['ts_client_email'])) {
        header('Location: login.php');
        exit;
    }
}
