<?php
// ajax/cxp/guardar-pago-cxp.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

$id_cxp         = isset($_POST['id_cxp']) ? (int)$_POST['id_cxp'] : 0;
$monto          = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
$fecha          = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
$id_metodo_pago = isset($_POST['metodo_pago']) ? (int)$_POST['metodo_pago'] : 0;

$referencia  = isset($_POST['referencia']) ? $clsConsulta->escape($_POST['referencia']) : '';
$observa     = isset($_POST['observaciones']) ? $clsConsulta->escape($_POST['observaciones']) : '';

if ($idEmpresa <= 0 || $id_cxp <= 0 || $monto <= 0 || $id_metodo_pago <= 0) {
    echo "error";
    exit;
}

// Traer el monto total y pagado actual (y validar empresa)
$cxp = $clsConsulta->consultaGeneral("SELECT monto_total, monto_pagado FROM cuentas_por_pagar WHERE id={$id_cxp} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo "error_cxp_not_found";
    exit;
}

$monto_total  = (float)$cxp[1]['monto_total'];
$monto_pagado = (float)$cxp[1]['monto_pagado'];
$nuevo_pagado = $monto_pagado + $monto;

if ($nuevo_pagado > $monto_total) {
    echo "error_monto_excede";
    exit;
}

$ref_sql = ($referencia !== '') ? "'" . $referencia . "'" : "NULL";
$obs_sql = ($observa !== '') ? "'" . $observa . "'" : "NULL";

$clsConsulta->guardarGeneral("
    INSERT INTO pagos_cxp (id_cxp, fecha, monto, id_metodo_pago, referencia, observaciones)
    VALUES ({$id_cxp}, '{$fecha}', {$monto}, {$id_metodo_pago}, {$ref_sql}, {$obs_sql})
");

$estatus = 'pendiente';
if ($nuevo_pagado >= $monto_total) $estatus = 'pagada';
elseif ($nuevo_pagado > 0) $estatus = 'parcial';

$clsConsulta->aplicaQuery("
    UPDATE cuentas_por_pagar
    SET monto_pagado = {$nuevo_pagado}, estatus = '{$estatus}'
    WHERE id = {$id_cxp} AND id_empresa = {$idEmpresa}
");

echo "success";
