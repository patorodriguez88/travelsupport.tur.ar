<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';

ts_require_login();

$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$valid = ['nueva', 'contactada', 'cotizada', 'cerrada', 'descartada'];

if ($id && in_array($status, $valid, true)) {
    ts_db()->prepare('UPDATE consultas SET status = ? WHERE id = ?')->execute([$status, $id]);
}

header('Location: consulta.php?id=' . $id);
exit;
