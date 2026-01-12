<?php
// ajax/salidas-almacen/existencias-producto.php
require_once '../../lib/clsConsultas.php';
header('Content-Type: application/json');
$clsConsulta = new Consultas();

$id_producto = intval($_GET['id_producto'] ?? 0);
if ($id_producto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto inválido']);
    exit;
}

$sql = "SELECT i.id_almacen, a.almacen, IFNULL(i.cantidad,0) AS cantidad
        FROM inventarios i
        LEFT JOIN cat_almacenes a ON a.id = i.id_almacen
        WHERE i.id_producto = $id_producto
        ORDER BY a.almacen";
$rs = $clsConsulta->consultaGeneral($sql);

$out = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $i => $r) {
        if ($i === 0) continue;
        $out[] = ['id_almacen' => intval($r['id_almacen']), 'almacen' => $r['almacen'], 'cantidad' => (float)$r['cantidad']];
    }
}
echo json_encode(['success' => true, 'existencias' => $out]);
