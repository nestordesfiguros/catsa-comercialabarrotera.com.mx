<?php
// ajax/cxp/eliminar-pago-cxp.php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($idEmpresa <= 0 || $id <= 0) {
    echo json_encode(['status' => 'error']);
    exit;
}

// 1) Obtener pago (y su CxP) + validar empresa vía CxP
$pago = $clsConsulta->consultaGeneral("
    SELECT p.id_cxp, p.monto
    FROM pagos_cxp p
    INNER JOIN cuentas_por_pagar cxp ON cxp.id = p.id_cxp
    WHERE p.id = {$id}
      AND cxp.id_empresa = {$idEmpresa}
    LIMIT 1
");

if ($clsConsulta->numrows < 1) {
    echo json_encode(['status' => 'error']);
    exit;
}

$row = $pago[1];
$id_cxp = (int)$row['id_cxp'];
$monto_eliminar = (float)$row['monto'];

$clsConsulta->aplicaQuery("START TRANSACTION");

// 2) Eliminar pago
$eliminado = $clsConsulta->aplicaQuery("DELETE FROM pagos_cxp WHERE id = {$id}");
if (!$eliminado) {
    $clsConsulta->aplicaQuery("ROLLBACK");
    echo json_encode(['status' => 'error']);
    exit;
}

// 3) Recalcular montos
$actual = $clsConsulta->consultaGeneral("SELECT monto_total, monto_pagado FROM cuentas_por_pagar WHERE id={$id_cxp} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows < 1) {
    $clsConsulta->aplicaQuery("ROLLBACK");
    echo json_encode(['status' => 'error']);
    exit;
}

$monto_total = (float)$actual[1]['monto_total'];
$monto_pagado = (float)$actual[1]['monto_pagado'] - $monto_eliminar;
if ($monto_pagado < 0) $monto_pagado = 0;

// 4) Estatus
if ($monto_pagado >= $monto_total) $nuevo_estatus = 'pagada';
elseif ($monto_pagado > 0) $nuevo_estatus = 'parcial';
else $nuevo_estatus = 'pendiente';

// 5) Actualizar
$clsConsulta->aplicaQuery("
    UPDATE cuentas_por_pagar
    SET monto_pagado = {$monto_pagado}, estatus = '{$nuevo_estatus}'
    WHERE id = {$id_cxp} AND id_empresa = {$idEmpresa}
");

$clsConsulta->aplicaQuery("COMMIT");

echo json_encode([
    'status' => 'success',
    'nuevo_estatus' => $nuevo_estatus
]);
