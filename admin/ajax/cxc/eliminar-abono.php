<?php
session_start();
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo 'ID inválido';
    exit;
}

// 1. Obtener el abono antes de eliminarlo
$sqlGet = "SELECT id_cxc, abono FROM mov_cxc WHERE id = $id LIMIT 1";
$abono = $clsConsulta->consultaGeneral($sqlGet);

if (!$clsConsulta->numrows || !$abono[1]) {
    echo 'Abono no encontrado';
    exit;
}

$id_cxc = intval($abono[1]['id_cxc']);
$monto_abono = floatval($abono[1]['abono']);

// 2. Eliminar el abono
$sqlDel = "DELETE FROM mov_cxc WHERE id = $id LIMIT 1";
$ok = $clsConsulta->aplicaquery($sqlDel);

if ($ok) {
    // 3. Recalcular el saldo
    $sqlUpdateSaldo = "
        UPDATE cab_cxc 
        SET saldo = saldo + $monto_abono,
            estatus = IF(saldo + $monto_abono <= 0, 'Pagada', 'Pendiente')
        WHERE id_cxc = $id_cxc
    ";
    $clsConsulta->aplicaquery($sqlUpdateSaldo);
    echo 'success';
} else {
    echo 'Error al eliminar abono';
}
