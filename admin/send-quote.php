<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../templates/quote-template.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

ts_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

function ts_clean(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

$consultaId = (int) ($_POST['consulta_id'] ?? 0) ?: null;

$includes = array_values(array_filter(array_map('trim', $_POST['includes'] ?? [])));
$excludes = array_values(array_filter(array_map('trim', $_POST['excludes'] ?? [])));

$options = [];
foreach (['economica', 'recomendada', 'premium'] as $key) {
    $options[$key] = [
        'hotel' => ts_clean("opt_{$key}_hotel"),
        'detail' => ts_clean("opt_{$key}_detail"),
        'amount' => ts_clean("opt_{$key}_amount"),
    ];
}

$q = [
    'client_name' => ts_clean('client_name'),
    'client_email' => ts_clean('client_email'),
    'quote_date' => date('Y-m-d'),
    'destination' => ts_clean('destination'),
    'nights' => ts_clean('nights'),
    'regimen' => ts_clean('regimen'),
    'start_date' => ts_clean('start_date'),
    'return_date' => ts_clean('return_date'),
    'passengers' => ts_clean('passengers'),
    'cover_photo_url' => ts_clean('cover_photo_url'),
    'includes' => $includes,
    'excludes' => $excludes,
    'options' => $options,
    'deposit' => ts_clean('deposit'),
    'balance_terms' => ts_clean('balance_terms'),
    'valid_until' => ts_clean('valid_until'),
];

if (!$q['client_name'] || !filter_var($q['client_email'], FILTER_VALIDATE_EMAIL) || !$q['destination'] || !$q['valid_until']) {
    http_response_code(422);
    echo 'Faltan datos obligatorios para generar la cotización.';
    exit;
}

$logoPath = __DIR__ . '/../assets/logo-dark.svg';
$htmlForEmail = ts_render_quote_html($q, 'cid:logo');

$smtp = ts_config()['smtp'];
$mail = new PHPMailer(true);
$sendError = null;

try {
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['user'];
    $mail->Password = $smtp['pass'];
    $mail->SMTPSecure = $smtp['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = (int) $smtp['port'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($smtp['user'], $smtp['from_name']);
    $mail->addAddress($q['client_email'], $q['client_name']);
    $mail->addReplyTo($smtp['user'], $smtp['from_name']);

    if (is_file($logoPath)) {
        $mail->addEmbeddedImage($logoPath, 'logo', 'logo.svg', PHPMailer::ENCODING_BASE64, 'image/svg+xml');
    }

    $mail->isHTML(true);
    $mail->Subject = 'Propuesta de viaje a ' . $q['destination'] . ' - Travel Support';
    $mail->Body = $htmlForEmail;
    $mail->AltBody = 'Te enviamos tu propuesta de viaje a ' . $q['destination'] . '. Abrí este correo en un cliente compatible con HTML para verla con el formato completo.';

    $mail->send();
    $sentAt = date('Y-m-d H:i:s');
} catch (PHPMailerException $e) {
    $sendError = $mail->ErrorInfo;
    $sentAt = null;
}

$stmt = ts_db()->prepare(
    'INSERT INTO cotizaciones (consulta_id, client_name, client_email, destination, cover_photo_url, nights, regimen, start_date, return_date, passengers, includes, excludes, options, payment_terms, valid_until, sent_at)
     VALUES (:consulta_id, :client_name, :client_email, :destination, :cover_photo_url, :nights, :regimen, :start_date, :return_date, :passengers, :includes, :excludes, :options, :payment_terms, :valid_until, :sent_at)'
);
$stmt->execute([
    'consulta_id' => $consultaId,
    'client_name' => $q['client_name'],
    'client_email' => $q['client_email'],
    'destination' => $q['destination'],
    'cover_photo_url' => $q['cover_photo_url'] ?: null,
    'nights' => $q['nights'],
    'regimen' => $q['regimen'],
    'start_date' => $q['start_date'],
    'return_date' => $q['return_date'],
    'passengers' => $q['passengers'],
    'includes' => json_encode($includes, JSON_UNESCAPED_UNICODE),
    'excludes' => json_encode($excludes, JSON_UNESCAPED_UNICODE),
    'options' => json_encode($options, JSON_UNESCAPED_UNICODE),
    'payment_terms' => $q['deposit'] . ' | ' . $q['balance_terms'],
    'valid_until' => $q['valid_until'],
    'sent_at' => $sentAt,
]);
$quoteId = ts_db()->lastInsertId();

if ($consultaId) {
    ts_db()->prepare("UPDATE consultas SET status = 'cotizada' WHERE id = ?")->execute([$consultaId]);
}

$backLink = $consultaId ? "consulta.php?id={$consultaId}" : 'index.php';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cotización <?= $sendError ? 'guardada' : 'enviada' ?> | Back office Travel Support</title>
<style>
  body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f7f5ef; font-family: Inter, system-ui, sans-serif; }
  .card { background:#fff; border-radius:16px; box-shadow:0 18px 50px rgba(16,36,62,.12); padding:36px; max-width:420px; text-align:center; }
  h1 { font-size:1.2rem; color:#10243e; }
  p { color:#64748b; font-size:.9rem; }
  .error { color:#c0392b; }
  a.btn { display:inline-block; margin-top:16px; padding:10px 18px; border-radius:8px; background:#19a7a0; color:#fff; font-weight:700; }
</style>
</head>
<body>
  <div class="card">
    <?php if ($sendError): ?>
      <h1>La cotización se guardó, pero no se pudo enviar el email</h1>
      <p class="error"><?= htmlspecialchars($sendError) ?></p>
      <p>Revisá los datos SMTP en <code>config.php</code>.</p>
    <?php else: ?>
      <h1>Cotización enviada a <?= htmlspecialchars($q['client_email']) ?></h1>
      <p>Se guardó una copia en el sistema.</p>
    <?php endif; ?>
    <a class="btn" href="quote-preview.php?id=<?= (int) $quoteId ?>" target="_blank">Ver cotización</a><br>
    <a class="btn" href="<?= htmlspecialchars($backLink) ?>">Volver</a>
  </div>
</body>
</html>
