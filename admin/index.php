<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/partials.php';

ts_require_login();

$validStatuses = ['nueva', 'contactada', 'cotizada', 'cerrada', 'descartada'];
$status = $_GET['status'] ?? '';
$status = in_array($status, $validStatuses, true) ? $status : '';

if ($status) {
    $stmt = ts_db()->prepare('SELECT * FROM consultas WHERE status = ? ORDER BY created_at DESC');
    $stmt->execute([$status]);
} else {
    $stmt = ts_db()->query('SELECT * FROM consultas ORDER BY created_at DESC');
}
$consultas = $stmt->fetchAll();

ts_admin_layout_start($status ? ts_status_label($status) : 'Todas las consultas', $status);
?>
  <div class="page-head">
    <div>
      <h1><?= $status ? htmlspecialchars(ts_status_label($status)) : 'Todas las consultas' ?></h1>
      <div class="sub"><?= count($consultas) ?> resultado(s)</div>
    </div>
  </div>

  <div class="card" style="padding:0;">
  <?php if (!$consultas): ?>
    <div class="empty">No hay consultas en este estado todavía.</div>
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
        <td><a class="btn btn-ghost" href="consulta.php?id=<?= (int) $c['id'] ?>">Ver</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  </div>
<?php ts_admin_layout_end(); ?>
