<?php

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_json']);
    exit;
}

function ts_required_string(array $body, string $key): ?string
{
    $value = trim((string) ($body[$key] ?? ''));
    return $value === '' ? null : $value;
}

$name = ts_required_string($body, 'name');
$email = ts_required_string($body, 'email');
$phone = ts_required_string($body, 'phone');
$destination = ts_required_string($body, 'destination');
$startDate = ts_required_string($body, 'startDate');
$returnDate = ts_required_string($body, 'returnDate');
$budget = ts_required_string($body, 'budget');

$missing = [];
foreach (['name' => $name, 'email' => $email, 'phone' => $phone, 'destination' => $destination, 'startDate' => $startDate, 'returnDate' => $returnDate, 'budget' => $budget] as $key => $value) {
    if ($value === null) {
        $missing[] = $key;
    }
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $missing[] = 'email (formato inválido)';
}
if ($missing) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'missing_fields', 'fields' => $missing]);
    exit;
}

$travelers = is_array($body['travelers'] ?? null) ? $body['travelers'] : [];
$addons = $body['selectedAddons'] ?? [];
$activities = $body['selectedActivities'] ?? [];
if (is_string($addons)) {
    $addons = json_decode($addons, true) ?: [];
}
if (is_string($activities)) {
    $activities = json_decode($activities, true) ?: [];
}
$source = ts_required_string($body, 'source') ?? 'guided-planner';

try {
    $stmt = ts_db()->prepare(
        'INSERT INTO consultas (name, email, phone, destination, start_date, return_date, budget, travelers, addons, activities, source)
         VALUES (:name, :email, :phone, :destination, :start_date, :return_date, :budget, :travelers, :addons, :activities, :source)'
    );
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'destination' => $destination,
        'start_date' => $startDate,
        'return_date' => $returnDate,
        'budget' => $budget,
        'travelers' => json_encode($travelers, JSON_UNESCAPED_UNICODE),
        'addons' => json_encode($addons, JSON_UNESCAPED_UNICODE),
        'activities' => json_encode($activities, JSON_UNESCAPED_UNICODE),
        'source' => $source,
    ]);

    echo json_encode(['ok' => true, 'id' => ts_db()->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server_error']);
}
