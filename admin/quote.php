<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/destination-photos.php';
require_once __DIR__ . '/partials.php';

ts_require_login();

$consultaId = (int) ($_GET['consulta_id'] ?? 0);
$c = null;
if ($consultaId) {
    $stmt = ts_db()->prepare('SELECT * FROM consultas WHERE id = ?');
    $stmt->execute([$consultaId]);
    $c = $stmt->fetch();
}

$defaults = [
    'client_name' => $c['name'] ?? '',
    'client_email' => $c['email'] ?? '',
    'destination' => $c['destination'] ?? '',
    'start_date' => $c['start_date'] ?? '',
    'return_date' => $c['return_date'] ?? '',
    'passengers' => $c ? (json_decode($c['travelers'], true) ? count(json_decode($c['travelers'], true)) . ' pasajero(s)' : '') : '',
];
$suggestedPhoto = $defaults['destination'] ? ts_guess_destination_photo($defaults['destination']) : null;
$destinationPhotos = ts_destination_photos();

ts_admin_layout_start('Nueva cotización');
?>
<style>
  h2.section { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: var(--navy); border-bottom: 1px solid var(--line); padding-bottom: 8px; margin: 26px 0 14px; }
  .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .grid3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
  .field { margin-bottom: 4px; }
  label { display: block; font-size: .8rem; font-weight: 600; color: var(--muted); margin-bottom: 5px; }
  input, textarea { width: 100%; padding: 9px 11px; border: 1px solid var(--line); border-radius: 8px; font: inherit; }
  .opt-block { border: 1px solid var(--line); border-radius: 10px; padding: 14px; }
  .opt-block h3 { margin: 0 0 10px; font-size: .85rem; color: var(--navy); }
  .dyn-row { display: flex; gap: 8px; margin-bottom: 8px; }
  .dyn-row input { flex: 1; }
  .dyn-row button, .add-btn { border: none; background: #eef6f6; color: var(--teal); border-radius: 8px; padding: 8px 12px; cursor: pointer; font-weight: 700; }
  .submit-btn { display: block; width: 100%; padding: 13px; border: none; border-radius: 10px; background: var(--teal); color: #fff; font-weight: 700; font-size: 1rem; cursor: pointer; margin-top: 10px; }
  .submit-btn:hover { background: var(--teal-dark); }
  .cover-picker { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
  .cover-option { width: 84px; height: 56px; border-radius: 8px; background-size: cover; background-position: center; cursor: pointer; border: 2px solid transparent; position: relative; }
  .cover-option.selected { border-color: var(--teal); }
  .cover-option span { position: absolute; bottom: 2px; left: 4px; right: 4px; font-size: .6rem; color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,.8); }
  #cover-preview { margin-top: 10px; width: 100%; max-width: 320px; height: 130px; border-radius: 10px; background-size: cover; background-position: center; background-color: #e9eef0; }
</style>
<a class="back-link" href="<?= $consultaId ? 'consulta.php?id=' . $consultaId : 'index.php' ?>">&larr; Volver</a>
<div class="card">
  <div class="page-head"><h1>Generar cotización</h1></div>
  <form method="post" action="send-quote.php">
    <input type="hidden" name="consulta_id" value="<?= (int) $consultaId ?>">

    <h2 class="section">Cliente y viaje</h2>
    <div class="grid">
      <div class="field"><label>Nombre del cliente</label><input name="client_name" value="<?= htmlspecialchars($defaults['client_name']) ?>" required></div>
      <div class="field"><label>Email del cliente</label><input type="email" name="client_email" value="<?= htmlspecialchars($defaults['client_email']) ?>" required></div>
      <div class="field"><label>Destino</label><input id="destination-input" name="destination" value="<?= htmlspecialchars($defaults['destination']) ?>" required></div>
      <div class="field"><label>Noches</label><input name="nights" placeholder="Ej. 7" required></div>
      <div class="field"><label>Régimen</label><input name="regimen" placeholder="Ej. All Inclusive" required></div>
      <div class="field"><label>Pasajeros</label><input name="passengers" value="<?= htmlspecialchars($defaults['passengers']) ?>" placeholder="Ej. 2 adultos" required></div>
      <div class="field"><label>Fecha de salida</label><input type="date" name="start_date" value="<?= htmlspecialchars($defaults['start_date']) ?>" required></div>
      <div class="field"><label>Fecha de regreso</label><input type="date" name="return_date" value="<?= htmlspecialchars($defaults['return_date']) ?>" required></div>
    </div>

    <h2 class="section">Foto de portada</h2>
    <div class="field">
      <label>URL de la imagen (elegí una sugerida o pegá la tuya)</label>
      <input id="cover-photo-input" name="cover_photo_url" value="<?= htmlspecialchars($suggestedPhoto ?? '') ?>" placeholder="https://...">
      <div id="cover-preview" style="<?= $suggestedPhoto ? 'background-image:url(\'' . htmlspecialchars($suggestedPhoto) . '\')' : '' ?>"></div>
      <div class="cover-picker">
        <?php foreach ($destinationPhotos as $city => $url): ?>
        <div class="cover-option" data-url="<?= htmlspecialchars($url) ?>" style="background-image:url('<?= htmlspecialchars($url) ?>')"><span><?= htmlspecialchars($city) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>

    <h2 class="section">Incluye / No incluye</h2>
    <div class="grid">
      <div>
        <label>Incluye</label>
        <div class="dyn-list" data-list="includes">
          <div class="dyn-row"><input name="includes[]" placeholder="Ej. Vuelo / traslado"></div>
        </div>
        <button type="button" class="add-btn" data-add="includes">+ Agregar ítem</button>
      </div>
      <div>
        <label>No incluye</label>
        <div class="dyn-list" data-list="excludes">
          <div class="dyn-row"><input name="excludes[]" placeholder="Ej. Asistencia al viajero"></div>
        </div>
        <button type="button" class="add-btn" data-add="excludes">+ Agregar ítem</button>
      </div>
    </div>

    <h2 class="section">Opciones disponibles</h2>
    <div class="grid3">
      <div class="opt-block">
        <h3>Opción económica</h3>
        <div class="field"><label>Hotel / categoría</label><input name="opt_economica_hotel"></div>
        <div class="field"><label>Detalle breve</label><input name="opt_economica_detail"></div>
        <div class="field"><label>Monto USD por persona</label><input name="opt_economica_amount" inputmode="decimal"></div>
      </div>
      <div class="opt-block">
        <h3>Opción recomendada</h3>
        <div class="field"><label>Hotel / categoría</label><input name="opt_recomendada_hotel"></div>
        <div class="field"><label>Detalle breve</label><input name="opt_recomendada_detail"></div>
        <div class="field"><label>Monto USD por persona</label><input name="opt_recomendada_amount" inputmode="decimal"></div>
      </div>
      <div class="opt-block">
        <h3>Opción premium</h3>
        <div class="field"><label>Hotel / categoría</label><input name="opt_premium_hotel"></div>
        <div class="field"><label>Detalle breve</label><input name="opt_premium_detail"></div>
        <div class="field"><label>Monto USD por persona</label><input name="opt_premium_amount" inputmode="decimal"></div>
      </div>
    </div>

    <h2 class="section">Forma de pago y vigencia</h2>
    <div class="grid">
      <div class="field"><label>Seña</label><input name="deposit" placeholder="Ej. 30% al confirmar reserva"></div>
      <div class="field"><label>Saldo</label><input name="balance_terms" placeholder="Ej. 30 días antes del viaje"></div>
      <div class="field"><label>Tarifa válida hasta</label><input type="date" name="valid_until" required></div>
    </div>

    <button type="submit" class="submit-btn">Generar y enviar por email</button>
  </form>
</div>
<script>
  document.querySelectorAll('[data-add]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const key = btn.dataset.add;
      const list = document.querySelector(`[data-list="${key}"]`);
      const row = document.createElement('div');
      row.className = 'dyn-row';
      row.innerHTML = `<input name="${key}[]" placeholder="Nuevo ítem"><button type="button" aria-label="Quitar">×</button>`;
      row.querySelector('button').addEventListener('click', () => row.remove());
      list.appendChild(row);
    });
  });

  const coverInput = document.getElementById('cover-photo-input');
  const coverPreview = document.getElementById('cover-preview');
  function setCover(url) {
    coverInput.value = url;
    coverPreview.style.backgroundImage = url ? `url('${url}')` : '';
    document.querySelectorAll('.cover-option').forEach((el) => el.classList.toggle('selected', el.dataset.url === url));
  }
  document.querySelectorAll('.cover-option').forEach((el) => {
    el.addEventListener('click', () => setCover(el.dataset.url));
    if (el.dataset.url === coverInput.value) el.classList.add('selected');
  });
  coverInput.addEventListener('input', () => { coverPreview.style.backgroundImage = coverInput.value ? `url('${coverInput.value}')` : ''; });
</script>
<?php ts_admin_layout_end(); ?>
