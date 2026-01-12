<?php
// ajax/utilidad/tabla-utilidad.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 25;
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');

// Obtener todos los productos vendidos en el periodo
$sql = "
SELECT
    p.clave AS clave,
    p.nombre AS producto,
    p.unidad_medida AS unidad,
    SUM(mr.cantidad) AS cantidad,
    -- Último precio de compra antes de la venta
    (
        SELECT mc.precio
        FROM mov_compras mc
        WHERE mc.id_producto = mr.id_producto
          AND mc.id <= (
            SELECT MAX(id)
            FROM mov_compras
            WHERE id_producto = mr.id_producto
              AND id <= (
                SELECT MAX(id)
                FROM mov_remisiones
                WHERE id_producto = mr.id_producto
                  AND id <= mr.id
              )
          )
        ORDER BY mc.id DESC
        LIMIT 1
    ) AS precio_compra,
    mr.precio AS precio_venta,
    SUM(mr.cantidad * mr.precio) AS importe_venta,
    SUM(mr.cantidad * (
        SELECT mc.precio
        FROM mov_compras mc
        WHERE mc.id_producto = mr.id_producto
          AND mc.id <= (
            SELECT MAX(id)
            FROM mov_compras
            WHERE id_producto = mr.id_producto
              AND id <= (
                SELECT MAX(id)
                FROM mov_remisiones
                WHERE id_producto = mr.id_producto
                  AND id <= mr.id
              )
          )
        ORDER BY mc.id DESC
        LIMIT 1
    )) AS costo_total,
    (SUM(mr.cantidad * mr.precio) - 
    SUM(mr.cantidad * (
        SELECT mc.precio
        FROM mov_compras mc
        WHERE mc.id_producto = mr.id_producto
          AND mc.id <= (
            SELECT MAX(id)
            FROM mov_compras
            WHERE id_producto = mr.id_producto
              AND id <= (
                SELECT MAX(id)
                FROM mov_remisiones
                WHERE id_producto = mr.id_producto
                  AND id <= mr.id
              )
          )
        ORDER BY mc.id DESC
        LIMIT 1
    ))) AS utilidad
FROM mov_remisiones mr
INNER JOIN cab_remisiones cr ON mr.id_remision = cr.id
INNER JOIN cat_productos p ON mr.id_producto = p.id_producto
WHERE cr.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'
GROUP BY mr.id_producto, mr.precio
ORDER BY producto ASC
LIMIT $start, $length
";

$datos = $clsConsulta->consultaGeneral($sql);

// Para recordsTotal y recordsFiltered, solo cuenta las filas obtenidas (sin hacer otra consulta)
$recordsTotal = is_array($datos) ? count($datos) : 0;
$recordsFiltered = $recordsTotal;

$data = [];
if ($datos) {
    foreach ($datos as $row) {
        $precio_compra = is_null($row['precio_compra']) ? 0 : round($row['precio_compra'], 2);
        $precio_venta = is_null($row['precio_venta']) ? 0 : round($row['precio_venta'], 2);
        $importe_venta = is_null($row['importe_venta']) ? 0 : round($row['importe_venta'], 2);
        $costo_total = is_null($row['costo_total']) ? 0 : round($row['costo_total'], 2);
        $utilidad = is_null($row['utilidad']) ? 0 : round($row['utilidad'], 2);

        $data[] = [
            'clave'         => $row['clave'],
            'producto'      => $row['producto'],
            'unidad'        => $row['unidad'],
            'cantidad'      => $row['cantidad'],
            'precio_compra' => number_format($precio_compra, 2),
            'precio_venta'  => number_format($precio_venta, 2),
            'importe_venta' => number_format($importe_venta, 2),
            'costo_total'   => number_format($costo_total, 2),
            'utilidad'      => number_format($utilidad, 2)
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);
