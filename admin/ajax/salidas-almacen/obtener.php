<?php
require_once '../../lib/clsConsultas.php';
header('Content-Type: application/json');
$clsConsulta = new Consultas();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

/* Cabecera + cliente vía remisión */
$cab = $clsConsulta->consultaGeneral("
  SELECT 
    s.id AS id_salida,
    s.id_almacen,
    s.fecha,
    s.total,
    s.estatus,
    COALESCE(c.razon_social,'') AS cliente
  FROM cab_salidas_almacen s
  LEFT JOIN rel_salidas_remisiones rs ON rs.id_salida = s.id
  LEFT JOIN cab_remisiones r         ON r.id = rs.id_remision
  LEFT JOIN cat_clientes c           ON c.id = r.id_cliente
  WHERE s.id = $id
  GROUP BY s.id
");
if (!$cab || !isset($cab[1])) {
    echo json_encode(['success' => false, 'message' => 'No encontrada']);
    exit;
}

/* Detalle: usa columnas reales de mov_salidas_almacen (no hay id_remision ni precio_unitario) */
$detRS = $clsConsulta->consultaGeneral("
  SELECT d.id_producto, d.cantidad, d.precio, p.nombre
  FROM mov_salidas_almacen d
  LEFT JOIN cat_productos p ON p.id_producto = d.id_producto
  WHERE d.id_salida = $id
");

$detalle = [];
if ($detRS && $clsConsulta->numrows > 0) {
    foreach ($detRS as $k => $d) {
        if ($k === 0) continue;
        $detalle[] = [
            'id_producto'     => intval($d['id_producto']),
            'nombre'          => $d['nombre'],
            'cantidad'        => (float)$d['cantidad'],
            'precio_unitario' => (float)$d['precio'], // mapea a precio_unitario para la UI
            'id_remision'     => null,                // no existe en esta tabla
        ];
    }
}

/* Remisiones relacionadas (chips) */
$rels = $clsConsulta->consultaGeneral("
  SELECT id_remision FROM rel_salidas_remisiones WHERE id_salida = $id
");
$remisiones = [];
if ($rels && $clsConsulta->numrows > 0) {
    foreach ($rels as $k => $r) {
        if ($k === 0) continue;
        $remisiones[] = intval($r['id_remision']);
    }
}

echo json_encode([
    'success'    => true,
    'cabecera'   => $cab[1],
    'detalle'    => $detalle,
    'remisiones' => $remisiones
]);
