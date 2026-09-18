<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/status.php';
require_once __DIR__ . '/../lib/destination-photos.php';

ts_client_require_login();

$email = $_SESSION['ts_client_email'];
$phone = $_SESSION['ts_client_phone'];

$stmt = ts_db()->prepare("SELECT * FROM consultas WHERE email = ? AND phone = ? AND status != 'descartada' ORDER BY created_at DESC");
$stmt->execute([$email, $phone]);
$consultas = $stmt->fetchAll();

$cotizacionesByConsulta = [];
if ($consultas) {
    $ids = array_column($consultas, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $qStmt = ts_db()->prepare("SELECT * FROM cotizaciones WHERE consulta_id IN ($placeholders) ORDER BY created_at DESC");
    $qStmt->execute($ids);
    foreach ($qStmt->fetchAll() as $row) {
        $cotizacionesByConsulta[$row['consulta_id']][] = $row;
    }
}

$firstName = trim(explode(' ', $consultas[0]['name'] ?? '')[0] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tus viajes | Travel Support</title>
<style>
  :root { --navy: #10243e; --navy-deep: #0a192d; --teal: #19a7a0; --coral: #f2826b; --cream: #f7f5ef; --line: #e3e9ea; --ink: #17304c; --muted: #64748b; }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--cream); font-family: Inter, system-ui, sans-serif; color: var(--ink); }
  header { background: linear-gradient(120deg, var(--navy), var(--navy-deep)); color: #fff; padding: 22px 0; }
  header .wrap { max-width: 900px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; }
  header img { height: 30px; }
  header a.logout { color: #cfe0e6; font-size: .82rem; }
  .hero-msg { max-width: 900px; margin: 0 auto; padding: 34px 24px 6px; }
  .hero-msg h1 { font-size: 1.6rem; margin: 0 0 6px; }
  .hero-msg p { color: var(--muted); margin: 0; }
  main { max-width: 900px; margin: 0 auto; padding: 20px 24px 60px; }
  .trip-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 34px rgba(16,36,62,.09); margin-bottom: 24px; }
  .trip-cover { height: 160px; background-size: cover; background-position: center; position: relative; }
  .trip-cover .scrim { position:absolute; inset:0; background: linear-gradient(180deg, rgba(10,25,45,0) 40%, rgba(10,25,45,.75) 100%); display:flex; align-items:flex-end; padding: 16px 22px; }
  .trip-cover h2 { color: #fff; margin: 0; font-size: 1.3rem; }
  .trip-body { padding: 20px 22px 22px; }
  .status-pill { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: .74rem; font-weight: 700; margin-bottom: 10px; }
  .status-nueva { background: #fff3d8; color: #8a5a00; }
  .status-contactada { background: #dbeafe; color: #1d4ed8; }
  .status-cotizada { background: #dcfce7; color: #15803d; }
  .status-cerrada { background: #e5e7eb; color: #374151; }
  .status-msg { font-size: .92rem; color: var(--ink); margin-bottom: 14px; }
  .trip-meta { display: flex; flex-wrap: wrap; gap: 18px; font-size: .84rem; color: var(--muted); margin-bottom: 16px; }
  .trip-meta strong { color: var(--ink); }
  .quotes { border-top: 1px solid var(--line); padding-top: 14px; }
  .quotes h3 { font-size: .82rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); margin: 0 0 10px; }
  .quote-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: .88rem; }
  .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; background: var(--teal); color: #fff; font-weight: 700; font-size: .82rem; }
  .empty { text-align: center; padding: 60px 20px; color: var(--muted); }
</style>
</head>
<body>
<header>
  <div class="wrap">
    <img src="../assets/logo-light.svg" alt="Travel Support">
    <a class="logout" href="logout.php">Cerrar sesión</a>
  </div>
</header>
<div class="hero-msg">
  <h1>¡Hola<?= $firstName ? ', ' . htmlspecialchars($firstName) : '' ?>! 🌍</h1>
  <p>Acá vas a ir viendo el estado de tus consultas y las propuestas de viaje que te preparamos.</p>
</div>
<main>
  <?php if (!$consultas): ?>
    <div class="empty">Todavía no encontramos consultas asociadas a esta cuenta.</div>
  <?php else: foreach ($consultas as $c): ?>
    <?php $cover = ts_guess_destination_photo($c['destination']) ?? 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1200&q=70'; ?>
    <div class="trip-card">
      <div class="trip-cover" style="background-image:url('<?= htmlspecialchars($cover) ?>')">
        <div class="scrim"><h2><?= htmlspecialchars($c['destination']) ?></h2></div>
      </div>
      <div class="trip-body">
        <span class="status-pill status-<?= htmlspecialchars($c['status']) ?>"><?= ts_status_label($c['status']) ?></span>
        <div class="status-msg"><?= htmlspecialchars(ts_status_client_message($c['status'])) ?></div>
        <div class="trip-meta">
          <span><strong>Fechas:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($c['start_date']))) ?> al <?= htmlspecialchars(date('d/m/Y', strtotime($c['return_date']))) ?></span>
          <span><strong>Presupuesto:</strong> <?= htmlspecialchars($c['budget']) ?></span>
          <span><strong>Consulta recibida:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($c['created_at']))) ?></span>
        </div>
        <?php if (!empty($cotizacionesByConsulta[$c['id']])): ?>
        <div class="quotes">
          <h3>Propuestas de viaje</h3>
          <?php foreach ($cotizacionesByConsulta[$c['id']] as $q): ?>
          <div class="quote-row">
            <span>Enviada el <?= htmlspecialchars(date('d/m/Y', strtotime($q['created_at']))) ?> · válida hasta <?= htmlspecialchars(date('d/m/Y', strtotime($q['valid_until']))) ?></span>
            <a class="btn" href="quote.php?id=<?= (int) $q['id'] ?>" target="_blank">Ver propuesta</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</main>
</body>
</html>
