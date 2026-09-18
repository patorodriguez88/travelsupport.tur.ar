<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';

ts_require_login();

$id = (int) ($_POST['consulta_id'] ?? 0);
$message = trim((string) ($_POST['message'] ?? ''));

if ($id && $message !== '') {
    ts_db()->prepare('INSERT INTO notas (consulta_id, author, message) VALUES (?, ?, ?)')
        ->execute([$id, $_SESSION['ts_admin'] ?? 'agente', $message]);
}

header('Location: consulta.php?id=' . $id . '#seguimiento');
exit;
