<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/partials.php';

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

$cotizacionesStmt = ts_db()->prepare('SELECT * FROM cotizaciones WHERE consulta_id = ? ORDER BY created_at DESC');
$cotizacionesStmt->execute([$id]);
$cotizaciones = $cotizacionesStmt->fetchAll();

$notasStmt = ts_db()->prepare('SELECT * FROM notas WHERE consulta_id = ? ORDER BY created_at DESC');
$notasStmt->execute([$id]);
$notas = $notasStmt->fetchAll();

$statusOptions = ['nueva', 'contactada', 'cotizada', 'cerrada', 'descartada'];

ts_admin_layout_start($c['name'], $c['status']);
?>
<style>
  .info-grid { display: grid; grid-template-columns: 160px 1fr; gap: 9px 12px; margin: 0; font-size: .9rem; }
  .info-grid dt { color: var(--muted); }
  .info-grid dd { margin: 0; }
  ul.list { margin: 6px 0 0; padding-left: 18px; font-size: .85rem; }
  table.mini th, table.mini td { font-size: .85rem; }
  .status-form { display: flex; align-items: center; gap: 8px; }
  .status-form select { padding: 7px 10px; border-radius: 8px; border: 1px solid var(--line); font: inherit; }
  .note { border-bottom: 1px solid var(--line); padding: 12px 0; }
  .note:last-child { border-bottom: none; }
  .note-meta { font-size: .74rem; color: var(--muted); margin-bottom: 4px; }
  .note-message { font-size: .89rem; white-space: pre-wrap; }
  .note-form textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font: inherit; min-height: 70px; resize: vertical; margin-bottom: 8px; }
  .two-col { display: grid; grid-template-columns: 1.3fr 1fr; gap: 22px; align-items: start; }
  @media (max-width: 860px) { .two-col { grid-template-columns: 1fr; } }
</style>
<a class="back-link" href="index.php">&larr; Volver a consultas</a>
<div class="page-head">
  <div>
    <h1><?= htmlspecialchars($c['name']) ?></h1>
    <div class="sub">Recibida el <?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['created_at']))) ?></div>
  </div>
  <form class="status-form" method="post" action="update-status.php">
    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
    <select name="status" onchange="this.form.submit()">
      <?php foreach ($statusOptions as $s): ?>
      <option value="<?= $s ?>" <?= $s === $c['status'] ? 'selected' : '' ?>><?= ts_status_label($s) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="two-col">
  <div>
    <div class="card">
      <dl class="info-grid">
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
      <div style="margin-top:16px;"><a class="btn" href="quote.php?consulta_id=<?= (int) $c['id'] ?>">Generar cotización</a></div>
    </div>

    <?php if ($cotizaciones): ?>
    <div class="card">
      <h2 style="font-size:1rem;margin-top:0;">Cotizaciones enviadas</h2>
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
  </div>

  <div class="card" id="seguimiento">
    <h2 style="font-size:1rem;margin-top:0;">Seguimiento</h2>
    <form class="note-form" method="post" action="add-note.php">
      <input type="hidden" name="consulta_id" value="<?= (int) $c['id'] ?>">
      <textarea name="message" placeholder="Agregá una nota de seguimiento (ej. 'Llamé y quedó en confirmar el viernes')" required></textarea>
      <button class="btn" type="submit">Agregar nota</button>
    </form>
    <div style="margin-top:14px;">
      <?php if (!$notas): ?>
        <div class="empty" style="padding:20px;">Todavía no hay notas.</div>
      <?php else: foreach ($notas as $n): ?>
        <div class="note">
          <div class="note-meta"><?= htmlspecialchars($n['author']) ?> · <?= htmlspecialchars(date('d/m/Y H:i', strtotime($n['created_at']))) ?></div>
          <div class="note-message"><?= htmlspecialchars($n['message']) ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>
<?php ts_admin_layout_end(); ?>
