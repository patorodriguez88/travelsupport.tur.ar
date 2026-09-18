<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';

ts_require_login();

$consultas = ts_db()->query('SELECT * FROM consultas ORDER BY created_at DESC')->fetchAll();

function ts_status_label(string $status): string
{
    return [
        'nueva' => 'Nueva',
        'contactada' => 'Contactada',
        'cotizada' => 'Cotizada',
        'cerrada' => 'Cerrada',
    ][$status] ?? $status;
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consultas | Back office Travel Support</title>
<style>
  :root { --navy: #10243e; --teal: #19a7a0; --cream: #f7f5ef; --line: #dce6e8; --ink: #17304c; --muted: #64748b; }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--cream); font-family: Inter, system-ui, sans-serif; color: var(--ink); }
  header { background: var(--navy); color: #fff; padding: 16px 28px; display: flex; align-items: center; justify-content: space-between; }
  header img { height: 32px; }
  header a { color: #fff; opacity: .85; font-size: .85rem; }
  main { padding: 28px; max-width: 1200px; margin: 0 auto; }
  h1 { font-size: 1.3rem; margin: 0 0 20px; }
  table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(16,36,62,.08); }
  th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--line); font-size: .9rem; }
  th { background: #f0f4f4; color: var(--muted); font-weight: 700; text-transform: uppercase; font-size: .72rem; letter-spacing: .04em; }
  tr:last-child td { border-bottom: none; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; }
  .badge-nueva { background: #fff3d8; color: #8a5a00; }
  .badge-contactada { background: #dbeafe; color: #1d4ed8; }
  .badge-cotizada { background: #dcfce7; color: #15803d; }
  .badge-cerrada { background: #e5e7eb; color: #374151; }
  .btn { display: inline-block; padding: 6px 12px; border-radius: 8px; background: var(--teal); color: #fff; font-size: .8rem; font-weight: 700; }
  .empty { padding: 40px; text-align: center; color: var(--muted); }
</style>
</head>
<body>
<header>
  <img src="../assets/logo.png" alt="Travel Support">
  <a href="logout.php">Cerrar sesión</a>
</header>
<main>
  <h1>Consultas recibidas (<?= count($consultas) ?>)</h1>
  <?php if (!$consultas): ?>
    <div class="empty">Todavía no llegaron consultas desde el formulario.</div>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Nombre</th>
        <th>Contacto</th>
        <th>Destino</th>
        <th>Viaje</th>
        <th>Presupuesto</th>
        <th>Estado</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($consultas as $c): ?>
      <tr>
        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?><br><span style="color:var(--muted)"><?= htmlspecialchars($c['phone']) ?></span></td>
        <td><?= htmlspecialchars($c['destination']) ?></td>
        <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['start_date']))) ?> – <?= htmlspecialchars(date('d/m/Y', strtotime($c['return_date']))) ?></td>
        <td><?= htmlspecialchars($c['budget']) ?></td>
        <td><span class="badge badge-<?= htmlspecialchars($c['status']) ?>"><?= ts_status_label($c['status']) ?></span></td>
        <td><a class="btn" href="consulta.php?id=<?= (int) $c['id'] ?>">Ver</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</main>
</body>
</html>
