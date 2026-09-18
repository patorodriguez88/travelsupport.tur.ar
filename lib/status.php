<?php

function ts_status_label(string $status): string
{
    return [
        'nueva' => 'Nueva',
        'contactada' => 'En contacto',
        'cotizada' => 'Cotizada',
        'cerrada' => 'Cerrada',
        'descartada' => 'Descartada',
    ][$status] ?? ucfirst($status);
}

function ts_status_client_message(string $status): string
{
    return [
        'nueva' => 'Recibimos tu consulta y ya la estamos revisando.',
        'contactada' => 'Un asesor se puso en contacto para conocer más detalles de tu viaje.',
        'cotizada' => '¡Tenés una propuesta de viaje esperándote! Revisá tu email.',
        'cerrada' => 'Este proceso ya se cerró. ¡Gracias por viajar con nosotros!',
        'descartada' => 'Este viaje no siguió adelante por ahora. ¡Escribinos cuando quieras retomarlo!',
    ][$status] ?? '';
}
