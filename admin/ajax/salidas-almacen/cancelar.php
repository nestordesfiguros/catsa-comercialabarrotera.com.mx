<?php
// ajax/salidas-almacen/cancelar.php
require_once '../../lib/clsConsultas.php';
header('Content-Type: application/json');
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$cab = $clsConsulta->consultaGeneral("SELECT id_almacen, estatus FROM cab_salidas_almacen WHERE id=$id");
if (!$cab || !isset($cab[1])) {
    echo json_encode(['success' => false, 'message' => 'No encontrada']);
    exit;
}
if ($cab[1]['estatus'] === 'cancelada') {
    echo json_encode(['success' => false, 'message' => 'Ya cancelada']);
    exit;
}

$id_almacen = intval($cab[1]['id_almacen']);

$det = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad FROM mov_salidas_almacen WHERE id_salida=$id");
if ($det) {
    foreach ($det as $k => $d) {
        if ($k === 0) continue;
        $idp = intval($d['id_producto']);
        $cant = (float)$d['cantidad'];
        $clsConsulta->aplicaquery("
          UPDATE inventarios SET cantidad = IFNULL(cantidad,0) + $cant
          WHERE id_almacen=$id_almacen AND id_producto=$idp
        ");
    }
}

$clsConsulta->aplicaquery("UPDATE cab_salidas_almacen SET estatus='cancelada' WHERE id=$id");
echo json_encode(['success' => true, 'message' => 'Salida cancelada']);
