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

    $optionLabels = ['economica' => 'Opción económica', 'recomendada' => 'Opción recomendada', 'premium' => 'Opción premium'];
    $optionsCols = '';
    foreach (['economica', 'recomendada', 'premium'] as $key) {
        $opt = $q['options'][$key] ?? null;
        if (!$opt || $opt['hotel'] === '') {
            continue;
        }
        $highlight = $key === 'recomendada';
        $optionsCols .= '<td class="opt-col' . ($highlight ? ' opt-highlight' : '') . '">'
            . '<div class="opt-head">' . $esc($optionLabels[$key]) . ($highlight ? '<div class="opt-tag">RECOMENDADA</div>' : '') . '</div>'
            . '<div class="opt-hotel">' . $esc($opt['hotel']) . '</div>'
            . '<div class="opt-detail">' . $esc($opt['detail']) . '</div>'
            . '<div class="opt-amount">USD ' . $esc($opt['amount']) . '</div>'
            . '<div class="opt-pp">por persona</div>'
            . '</td>';
    }

    $dateFmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '';

    return <<<HTML
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Propuesta de viaje - {$esc($q['destination'])}</title>
<style>
  body { margin:0; padding:24px; background:#f7f5ef; font-family: Arial, Helvetica, sans-serif; color:#17304c; }
  .sheet { max-width: 760px; margin: 0 auto; background:#fff; padding: 32px; border-radius: 8px; }
  .head { display:flex; justify-content:space-between; align-items:flex-start; border-bottom: 2px solid #10243e; padding-bottom: 16px; margin-bottom: 20px; }
  .head img { height: 42px; }
  .head .company { text-align:right; font-size: 12px; color:#374151; line-height:1.5; }
  .head .company strong { color:#10243e; font-size:13px; }
  h1 { font-size: 20px; color:#10243e; margin: 0 0 6px; }
  .meta { font-size: 13px; margin-bottom: 16px; }
  .bar { background:#10243e; color:#fff; padding: 10px 14px; border-radius: 6px; font-weight:bold; font-size:14px; margin-bottom: 8px; }
  .subline { font-size: 13px; margin-bottom: 20px; }
  table.cols { width:100%; border-collapse: collapse; margin-bottom: 22px; }
  table.cols td { vertical-align: top; width:50%; padding: 0; }
  .box-head { padding: 8px 12px; font-weight:bold; font-size: 12px; letter-spacing:.03em; color:#fff; }
  .box-head.inc { background:#2e7d46; }
  .box-head.exc { background:#6b7280; }
  ul.checklist { list-style:none; margin:0; padding:10px 12px; font-size: 13px; }
  ul.checklist li { margin-bottom: 6px; }
  ul.checklist .ok { color:#2e7d46; font-weight:bold; margin-right: 6px; }
  ul.checklist .no { color:#c0392b; font-weight:bold; margin-right: 6px; }
  h2.section { font-size: 13px; text-transform:uppercase; letter-spacing:.04em; color:#10243e; border-bottom:1px solid #dce6e8; padding-bottom:6px; margin: 24px 0 12px; }
  table.opts { width:100%; border-collapse: collapse; margin-bottom: 20px; }
  .opt-col { width:33.33%; border:1px solid #dce6e8; padding: 12px; text-align:center; vertical-align:top; }
  .opt-highlight { background:#eef6f6; border-color:#19a7a0; }
  .opt-head { font-weight:bold; font-size:12px; color:#10243e; margin-bottom:8px; text-transform:uppercase; }
  .opt-tag { font-size:10px; color:#19a7a0; }
  .opt-hotel { font-size:13px; font-weight:bold; margin-bottom:4px; }
  .opt-detail { font-size:12px; color:#64748b; margin-bottom:10px; min-height: 32px; }
  .opt-amount { font-size:17px; font-weight:bold; color:#10243e; }
  .opt-pp { font-size:11px; color:#64748b; }
  .payment { font-size: 13px; margin-bottom: 20px; }
  .payment li { margin-bottom: 4px; }
  .note { font-size: 11px; color:#64748b; line-height:1.6; border-top: 1px solid #dce6e8; padding-top: 14px; }
  .note p.important { font-style: italic; color:#10243e; margin-bottom: 8px; }
  .closing { margin-top: 18px; font-size: 13px; }
</style>
</head>
<body>
<div class="sheet">
  <div class="head">
    <img src="{$logoSrc}" alt="Travel Support">
    <div class="company">
      <strong>Karina Hongn</strong><br>
      Travel Support - Viajes y Turismo<br>
      Tel: 0351 - 6481380<br>
      E.V.T. Leg. 15067
    </div>
  </div>

  <h1>PROPUESTA DE VIAJE</h1>
  <div class="meta"><strong>Presupuesto para:</strong> {$esc($q['client_name'])} &nbsp;&nbsp; <strong>Fecha:</strong> {$dateFmt($q['quote_date'])}</div>

  <div class="bar">{$esc($q['destination'])} · {$esc($q['nights'])} noches · {$esc($q['regimen'])}</div>
  <div class="subline"><strong>Fechas:</strong> {$dateFmt($q['start_date'])} al {$dateFmt($q['return_date'])} &nbsp;&nbsp; <strong>Pasajeros:</strong> {$esc($q['passengers'])}</div>

  <table class="cols">
    <tr>
      <td style="padding-right:8px;">
        <div class="box-head inc">INCLUYE</div>
        <ul class="checklist">{$includesRows}</ul>
      </td>
      <td style="padding-left:8px;">
        <div class="box-head exc">NO INCLUYE</div>
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
    <em>Quedamos a disposición por cualquier consulta. ¡Muchas gracias!</em><br>
    <strong>Karina Hongn — Travel Support</strong>
  </div>
</div>
</body>
</html>
HTML;
}
