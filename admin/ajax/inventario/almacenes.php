<?php
// ajax/inventario/almacenes.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$sql = "SELECT id, almacen FROM cat_almacenes WHERE estatus = 1 ORDER BY almacen";
$rs = $clsConsulta->consultaGeneral($sql);

$out = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $i => $row) {
        if ($i === 0) continue;
        $out[] = ['id' => (int)$row['id'], 'text' => $row['almacen']];
    }
}

echo json_encode(['success' => true, 'data' => $out]);
