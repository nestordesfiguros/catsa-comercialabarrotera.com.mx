<?php
// ajax/salidas-almacen/productos-remision.php
require_once '../../lib/clsConsultas.php';
header('Content-Type: application/json');
$clsConsulta = new Consultas();

$id_rem = intval($_GET['id_remision'] ?? 0);
if ($id_rem <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql = "
  SELECT mr.id_producto, p.nombre,
         IFNULL(mr.cantidad,0) AS cantidad,
         IFNULL(mr.precio_unitario,0) AS precio_unitario
  FROM mov_remisiones mr
  INNER JOIN cat_productos p ON p.id_producto = mr.id_producto
  WHERE mr.id_remision = $id_rem
";
$rs = $clsConsulta->consultaGeneral($sql);

$productos = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $i => $r) {
        if ($i === 0) continue;
        $productos[] = [
            'id_producto'     => intval($r['id_producto']),
            'nombre'          => $r['nombre'],
            'cantidad'        => (float)$r['cantidad'],
            'precio_unitario' => (float)$r['precio_unitario']
        ];
    }
}
echo json_encode(['success' => true, 'productos' => $productos]);
