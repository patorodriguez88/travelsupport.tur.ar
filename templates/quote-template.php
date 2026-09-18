<?php

function ts_render_quote_html(array $q, string $logoSrc): string
{
    $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

    $includesRows = '';
    foreach ($q['includes'] as $item) {
        $includesRows .= '<li><span class="ok">&#10003;</span> ' . $esc($item) . '</li>';
    }
    $excludesRows = '';
    foreach ($q['excludes'] as $item) {
        $excludesRows .= '<li><span class="no">&#10007;</span> ' . $esc($item) . '</li>';
    }

    $optionLabels = ['economica' => 'Económica', 'recomendada' => 'Recomendada', 'premium' => 'Premium'];
    $optionsCols = '';
    foreach (['economica', 'recomendada', 'premium'] as $key) {
        $opt = $q['options'][$key] ?? null;
        if (!$opt || $opt['hotel'] === '') {
            continue;
        }
        $highlight = $key === 'recomendada';
        $optionsCols .= '<td class="opt-col' . ($highlight ? ' opt-highlight' : '') . '">'
            . '<div class="opt-head">' . $esc($optionLabels[$key]) . ($highlight ? '<div class="opt-tag">★ Nuestra sugerencia</div>' : '') . '</div>'
            . '<div class="opt-hotel">' . $esc($opt['hotel']) . '</div>'
            . '<div class="opt-detail">' . $esc($opt['detail']) . '</div>'
            . '<div class="opt-amount">USD ' . $esc($opt['amount']) . '</div>'
            . '<div class="opt-pp">por persona</div>'
            . '</td>';
    }

    $dateFmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '';
    $firstName = trim(explode(' ', trim($q['client_name']))[0] ?? '');

    $coverPhoto = trim((string) ($q['cover_photo_url'] ?? ''));
    $coverBlock = $coverPhoto
        ? '<div class="cover" style="background-image:url(\'' . $esc($coverPhoto) . '\');">'
            . '<div class="cover-scrim">'
            . '<div class="cover-dest">' . $esc($q['destination']) . '</div>'
            . '<div class="cover-meta">' . $esc($q['nights']) . ' noches · ' . $esc($q['regimen']) . ' · ' . $dateFmt($q['start_date']) . ' al ' . $dateFmt($q['return_date']) . '</div>'
            . '</div></div>'
        : '<div class="bar">' . $esc($q['destination']) . ' · ' . $esc($q['nights']) . ' noches · ' . $esc($q['regimen']) . '</div>';

    return <<<HTML
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Propuesta de viaje - {$esc($q['destination'])}</title>
<style>
  body { margin:0; padding:24px; background:#f0ede4; font-family: Georgia, 'Times New Roman', serif; color:#17304c; }
  .sheet { max-width: 760px; margin: 0 auto; background:#fff; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 60px rgba(16,36,62,.15); }
  .inner { padding: 30px 34px 34px; font-family: Arial, Helvetica, sans-serif; }
  .head { display:flex; justify-content:space-between; align-items:center; margin-bottom: 18px; }
  .head img { height: 36px; }
  .head .company { text-align:right; font-size: 11px; color:#6b7280; line-height:1.5; }
  .head .company strong { color:#10243e; font-size:12px; }
  .greeting { font-size: 20px; color:#10243e; margin: 0 0 4px; font-weight: 800; }
  .meta { font-size: 12.5px; color:#6b7280; margin-bottom: 18px; }
  .cover { position: relative; height: 220px; background-size: cover; background-position: center; }
  .cover-scrim { position:absolute; inset:0; background: linear-gradient(180deg, rgba(10,25,45,0) 35%, rgba(10,25,45,.82) 100%); display:flex; flex-direction:column; justify-content:flex-end; padding: 20px 26px; }
  .cover-dest { color:#fff; font-family: Georgia, serif; font-size: 30px; font-weight: 700; letter-spacing:-.01em; text-shadow: 0 2px 10px rgba(0,0,0,.3); }
  .cover-meta { color:#e8f0ee; font-size: 13px; margin-top: 4px; font-family: Arial, sans-serif; }
  .bar { background:#10243e; color:#fff; padding: 16px 26px; font-weight:bold; font-size:16px; }
  .body-pad { padding: 26px 34px 34px; }
  table.cols { width:100%; border-collapse: collapse; margin-bottom: 22px; }
  table.cols td { vertical-align: top; width:50%; padding: 0; }
  .box-head { padding: 9px 14px; font-weight:bold; font-size: 11px; letter-spacing:.05em; color:#fff; text-transform: uppercase; }
  .box-head.inc { background:#2e7d46; }
  .box-head.exc { background:#8a93a3; }
  ul.checklist { list-style:none; margin:0; padding:12px 14px; font-size: 13px; background:#fafbfb; }
  ul.checklist li { margin-bottom: 7px; }
  ul.checklist .ok { color:#2e7d46; font-weight:bold; margin-right: 6px; }
  ul.checklist .no { color:#c0392b; font-weight:bold; margin-right: 6px; }
  h2.section { font-size: 12px; text-transform:uppercase; letter-spacing:.06em; color:#10243e; border-bottom:2px solid #f0ede4; padding-bottom:7px; margin: 26px 0 14px; }
  table.opts { width:100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 20px; }
  .opt-col { width:33.33%; border:1px solid #dce6e8; border-radius: 10px; padding: 16px 12px; text-align:center; vertical-align:top; }
  .opt-highlight { background:#eef8f7; border-color:#19a7a0; border-width: 2px; }
  .opt-head { font-weight:bold; font-size:12px; color:#10243e; margin-bottom:10px; text-transform:uppercase; letter-spacing: .03em; }
  .opt-tag { font-size:10px; color:#19a7a0; font-weight: 700; margin-top: 3px; }
  .opt-hotel { font-size:13.5px; font-weight:bold; margin-bottom:4px; color:#10243e; }
  .opt-detail { font-size:12px; color:#64748b; margin-bottom:12px; min-height: 32px; }
  .opt-amount { font-size:19px; font-weight:bold; color:#10243e; }
  .opt-pp { font-size:11px; color:#64748b; }
  .payment { font-size: 13px; margin-bottom: 6px; background: #fafbfb; border-radius: 10px; padding: 14px 18px; }
  .payment li { margin-bottom: 5px; list-style: none; }
  .note { font-size: 10.5px; color:#8a93a3; line-height:1.6; border-top: 1px solid #eef0ef; padding-top: 14px; margin-top: 22px; }
  .note p.important { font-style: italic; color:#10243e; margin-bottom: 8px; font-size: 11.5px; }
  .closing { margin-top: 22px; font-size: 14px; text-align: center; padding-top: 18px; border-top: 1px dashed #dce6e8; }
  .closing .emoji { font-size: 22px; display:block; margin-bottom: 6px; }
</style>
</head>
<body>
<div class="sheet">
  <div class="inner" style="padding-bottom:0;">
    <div class="head">
      <img src="{$logoSrc}" alt="Travel Support">
      <div class="company">
        <strong>Karina Hongn</strong><br>
        Tel: 0351 - 6481380 · E.V.T. Leg. 15067
      </div>
    </div>
    <div class="greeting">¡Hola {$esc($firstName)}! Preparamos tu propuesta de viaje ✈</div>
    <div class="meta">Presupuesto para {$esc($q['client_name'])} · {$dateFmt($q['quote_date'])}</div>
  </div>

  {$coverBlock}

  <div class="body-pad">
  <div class="meta" style="margin-bottom:20px;"><strong style="color:#10243e;">Pasajeros:</strong> {$esc($q['passengers'])}</div>

  <table class="cols">
    <tr>
      <td style="padding-right:8px;">
        <div class="box-head inc">Incluye</div>
        <ul class="checklist">{$includesRows}</ul>
      </td>
      <td style="padding-left:8px;">
        <div class="box-head exc">No incluye</div>
        <ul class="checklist">{$excludesRows}</ul>
      </td>
    </tr>
  </table>

  <h2 class="section">Opciones disponibles</h2>
  <table class="opts"><tr>{$optionsCols}</tr></table>

  <h2 class="section">Forma de pago y vigencia</h2>
  <ul class="payment">
    <li>• Seña: {$esc($q['deposit'])} al confirmar reserva.</li>
    <li>• Saldo: {$esc($q['balance_terms'])}.</li>
    <li>• Tarifa válida hasta: {$dateFmt($q['valid_until'])}, sujeta a disponibilidad.</li>
  </ul>

  <div class="note">
    <p class="important">La siguiente cotización no implica reserva ni bloqueo de lugares.</p>
    <p>• Las tarifas están sujetas a disponibilidad y cambios hasta el momento de la seña.<br>
    • No incluye servicios que no sean debidamente especificados.<br>
    • Todo ticket aéreo, una vez emitido, está sujeto a penalidades y diferencias de tarifas estipuladas por las líneas aéreas en caso de modificaciones (cambios de fechas, rutas, devoluciones, no presentaciones y/o anulaciones totales o parciales).<br>
    • Condiciones especiales rigen para vuelos contratados y pre comprados por los operadores turísticos, con restricciones inalterables y pérdida total del importe pagado en caso de no viajar.<br>
    • Se sugiere contratar Asistencia al viajero; solicite su cotización. Es obligatoria para el ingreso a países europeos, Cuba y EEUU.<br>
    • Verifique la validez de su pasaporte, ya que muchos destinos solicitan que sea válido por 6 meses posteriores al viaje.<br>
    • Si viaja a países limítrofes, verifique que su DNI se encuentre en buenas condiciones.<br>
    • Si viaja con menores, consulte los requerimientos para salir del país.<br>
    • La documentación para salir del país es responsabilidad exclusiva de los señores pasajeros.</p>
  </div>

  <div class="closing">
    <span class="emoji">🌍 ✈ 🧳</span>
    <em>Quedamos a disposición por cualquier consulta. ¡Empecemos a armar este viaje juntos!</em><br>
    <strong>Karina Hongn — Travel Support</strong>
  </div>
  </div>
</div>
</body>
</html>
HTML;
}
