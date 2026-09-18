<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';

ts_require_login();

$id = (int) ($_GET['id'] ?? 0);
$stmt = ts_db()->prepare('SELECT * FROM consultas WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    http_response_code(404);
    echo 'Consulta no encontrada.';
    exit;
}

$travelers = json_decode($c['travelers'] ?? '[]', true) ?: [];
$addons = json_decode($c['addons'] ?? '[]', true) ?: [];
$activities = json_decode($c['activities'] ?? '[]', true) ?: [];

$cotizaciones = ts_db()->prepare('SELECT * FROM cotizaciones WHERE consulta_id = ? ORDER BY created_at DESC');
$cotizaciones->execute([$id]);
$cotizaciones = $cotizaciones->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($c['name']) ?> | Back office Travel Support</title>
<style>
  :root { --navy: #10243e; --teal: #19a7a0; --cream: #f7f5ef; --line: #dce6e8; --ink: #17304c; --muted: #64748b; }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--cream); font-family: Inter, system-ui, sans-serif; color: var(--ink); }
  header { background: var(--navy); color: #fff; padding: 16px 28px; display: flex; align-items: center; justify-content: space-between; }
  header img { height: 32px; }
  header a { color: #fff; opacity: .85; font-size: .85rem; }
  main { padding: 28px; max-width: 800px; margin: 0 auto; }
  .back { display:inline-block; margin-bottom: 16px; color: var(--teal); font-weight:600; font-size:.85rem; }
  .card { background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(16,36,62,.08); padding: 24px; margin-bottom: 20px; }
  h1 { font-size: 1.3rem; margin: 0 0 4px; }
  .sub { color: var(--muted); font-size: .85rem; margin-bottom: 18px; }
  dl { display: grid; grid-template-columns: 160px 1fr; gap: 8px 12px; margin: 0; font-size: .9rem; }
  dt { color: var(--muted); }
  dd { margin: 0; }
  .btn { display: inline-block; padding: 10px 18px; border-radius: 8px; background: var(--teal); color: #fff; font-size: .9rem; font-weight: 700; margin-top: 6px; }
  ul.list { margin: 6px 0 0; padding-left: 18px; font-size: .85rem; }
  table.mini { width:100%; border-collapse: collapse; font-size:.85rem; }
  table.mini th, table.mini td { text-align:left; padding:6px 8px; border-bottom:1px solid var(--line); }
</style>
</head>
<body>
<header>
  <img src="../assets/logo.png" alt="Travel Support">
  <a href="logout.php">Cerrar sesión</a>
</header>
<main>
  <a class="back" href="index.php">&larr; Volver a consultas</a>
  <div class="card">
    <h1><?= htmlspecialchars($c['name']) ?></h1>
    <div class="sub">Recibida el <?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['created_at']))) ?></div>
    <dl>
      <dt>Email</dt><dd><?= htmlspecialchars($c['email']) ?></dd>
      <dt>Teléfono</dt><dd><?= htmlspecialchars($c['phone']) ?></dd>
      <dt>Destino</dt><dd><?= htmlspecialchars($c['destination']) ?></dd>
      <dt>Fechas</dt><dd><?= htmlspecialchars(date('d/m/Y', strtotime($c['start_date']))) ?> al <?= htmlspecialchars(date('d/m/Y', strtotime($c['return_date']))) ?></dd>
      <dt>Presupuesto</dt><dd><?= htmlspecialchars($c['budget']) ?></dd>
      <?php if ($travelers): ?>
      <dt>Viajeros</dt>
      <dd><ul class="list"><?php foreach ($travelers as $t): ?><li><?= htmlspecialchars($t['name'] ?? '') ?> (<?= htmlspecialchars($t['age'] ?? '') ?> años)</li><?php endforeach; ?></ul></dd>
      <?php endif; ?>
      <?php if ($addons): ?>
      <dt>Complementos</dt><dd><?= htmlspecialchars(implode(', ', $addons)) ?></dd>
      <?php endif; ?>
      <?php if ($activities): ?>
      <dt>Excursiones</dt><dd><?= htmlspecialchars(implode(', ', array_map(fn($a) => $a['name'] ?? '', $activities))) ?></dd>
      <?php endif; ?>
    </dl>
    <a class="btn" href="quote.php?consulta_id=<?= (int) $c['id'] ?>">Generar cotización</a>
  </div>

  <?php if ($cotizaciones): ?>
  <div class="card">
    <h1 style="font-size:1.05rem;">Cotizaciones enviadas</h1>
    <table class="mini">
      <thead><tr><th>Fecha</th><th>Destino</th><th>Válida hasta</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($cotizaciones as $q): ?>
        <tr>
          <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($q['created_at']))) ?></td>
          <td><?= htmlspecialchars($q['destination']) ?></td>
          <td><?= htmlspecialchars(date('d/m/Y', strtotime($q['valid_until']))) ?></td>
          <td><a href="quote-preview.php?id=<?= (int) $q['id'] ?>" target="_blank">Ver</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>
</body>
</html>
