<?php
// ajax/utilidad/obtener-fechas-corte.php
session_start();
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Buscar el último corte registrado
$sql_ultimo_corte = "SELECT MAX(fecha_fin) AS fecha_fin FROM cortes_utilidad";
$res_ultimo = $clsConsulta->consultaGeneral($sql_ultimo_corte);

$fecha_inicio = '';
if ($res_ultimo && !empty($res_ultimo[0]['fecha_fin']) && $res_ultimo[0]['fecha_fin'] != '0000-00-00') {
    // La fecha inicial es el día siguiente al último corte
    $fecha_inicio = date('Y-m-d', strtotime($res_ultimo[0]['fecha_fin'] . ' +1 day'));
} else {
    // Si nunca ha habido corte, buscar la fecha más antigua de remisión
    $sql_primera_remision = "SELECT MIN(fecha) AS min_fecha FROM cab_remisiones";
    $res_primera = $clsConsulta->consultaGeneral($sql_primera_remision);
    if ($res_primera && !empty($res_primera[0]['min_fecha']) && $res_primera[0]['min_fecha'] != '0000-00-00 00:00:00') {
        $fecha_inicio = substr($res_primera[0]['min_fecha'], 0, 10); // Solo la fecha
    } else {
        // Si no hay remisiones, toma hoy por defecto
        $fecha_inicio = date('Y-m-d');
    }
}

echo json_encode([
    'success' => true,
    'fecha_inicio' => $fecha_inicio
]);
