<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../templates/quote-template.php';

ts_require_login();

$id = (int) ($_GET['id'] ?? 0);
$stmt = ts_db()->prepare('SELECT * FROM cotizaciones WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo 'Cotización no encontrada.';
    exit;
}

[$deposit, $balance] = array_pad(explode(' | ', $row['payment_terms'], 2), 2, '');

$q = [
    'client_name' => $row['client_name'],
    'quote_date' => substr($row['created_at'], 0, 10),
    'destination' => $row['destination'],
    'nights' => $row['nights'],
    'regimen' => $row['regimen'],
    'start_date' => $row['start_date'],
    'return_date' => $row['return_date'],
    'passengers' => $row['passengers'],
    'includes' => json_decode($row['includes'], true) ?: [],
    'excludes' => json_decode($row['excludes'], true) ?: [],
    'options' => json_decode($row['options'], true) ?: [],
    'deposit' => $deposit,
    'balance_terms' => $balance,
    'valid_until' => $row['valid_until'],
];

echo ts_render_quote_html($q, '../assets/logo.png');
