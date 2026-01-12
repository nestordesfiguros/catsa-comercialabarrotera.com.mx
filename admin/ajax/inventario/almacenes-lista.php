<?php
// ajax/inventario/almacenes-lista.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$sql = "SELECT id, almacen AS nombre FROM cat_almacenes ORDER BY almacen";
$rows = $clsConsulta->consultaGeneral($sql);

$data = [];
if ($clsConsulta->numrows > 0) {
    for ($i = 1; $i <= $clsConsulta->numrows; $i++) {
        $r = $rows[$i];
        $data[] = ['id' => $r['id'], 'nombre' => $r['nombre']];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['data' => $data]);
