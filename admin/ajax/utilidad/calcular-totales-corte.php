<?php
// ajax/utilidad/calcular-totales-corte.php
session_start();
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
$fecha_fin    = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';

if (!$fecha_inicio || !$fecha_fin || $fecha_inicio > $fecha_fin) {
    echo json_encode(['success' => false, 'msg' => 'Rango de fechas inválido.']);
    exit;
}

// Consulta los totales como el reporte principal (solo ventas en remisiones)
$sql = "
SELECT
    SUM(mr.cantidad * mr.precio) AS total_ventas,
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
    )) AS total_costo,
    (
        SUM(mr.cantidad * mr.precio) -
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
        ))
    ) AS utilidad
FROM mov_remisiones mr
INNER JOIN cab_remisiones cr ON mr.id_remision = cr.id
WHERE cr.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'
";
