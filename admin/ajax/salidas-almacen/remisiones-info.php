<?php
// ajax/salidas-almacen/remisiones-info.php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$ids = $_POST['ids'] ?? [];  // array de IDs de remisión
if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Sin remisiones']);
    exit;
}

$ids_int = array_map('intval', $ids);
$in = implode(',', $ids_int);

$sql = "SELECT r.id, r.id_almacen, a.almacen AS almacen_nombre,
               r.id_cliente, c.razon_social, c.nombre_comercial
        FROM cab_remisiones r
        LEFT JOIN cat_almacenes a ON a.id = r.id_almacen
        LEFT JOIN cat_clientes  c ON c.id = r.id_cliente
        WHERE r.id IN ($in)";

$rows = $clsConsulta->consultaGeneral($sql);
$remisiones = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rows as $i => $row) {
        if ($i === 0) continue;
        $remisiones[] = [
            'id'            => (int)$row['id'],
            'id_almacen'    => (int)$row['id_almacen'],
            'almacen'       => $row['almacen_nombre'],
            'id_cliente'    => (int)$row['id_cliente'],
            'cliente'       => trim($row['razon_social'] .
                ($row['nombre_comercial'] ? ' / ' . $row['nombre_comercial'] : ''))
        ];
    }
}

echo json_encode([
    'success'    => true,
    'remisiones' => $remisiones
]);
