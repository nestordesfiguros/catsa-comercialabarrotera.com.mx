<?php
// ajax/reportes/tabla-productos-mas-vendidos.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Parámetros de DataTables
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 25;
$draw = $_POST['draw'] ?? 1;

// Filtros
$fechaInicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_POST['fecha_fin'] ?? date('Y-m-d');
$idAlmacen = $_POST['id_almacen'] ?? 0;
$idCategoria = $_POST['id_categoria'] ?? 0;
$tipoVenta = $_POST['tipo_venta'] ?? '';
$topN = $_POST['top_n'] ?? 10;
$ordenarPor = $_POST['ordenar_por'] ?? 'cantidad';

try {
    // Consulta base para productos más vendidos
    $sql = "
        SELECT 
            cp.id_producto,
            cp.clave,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            SUM(mr.cantidad) AS cantidad_vendida,
            SUM(mr.cantidad * mr.precio) AS ventas_totales,
            SUM(mr.cantidad * cp.precio_compra) AS costo_total,
            SUM(mr.cantidad * mr.precio) - SUM(mr.cantidad * cp.precio_compra) AS utilidad,
            CASE 
                WHEN SUM(mr.cantidad * mr.precio) > 0 THEN 
                    ((SUM(mr.cantidad * mr.precio) - SUM(mr.cantidad * cp.precio_compra)) / SUM(mr.cantidad * mr.precio)) * 100 
                ELSE 0 
            END AS margen,
            (SUM(mr.cantidad * mr.precio) / (SELECT SUM(mr2.cantidad * mr2.precio) 
                                           FROM mov_remisiones mr2 
                                           INNER JOIN cab_remisiones cr2 ON mr2.id_remision = cr2.id 
                                           WHERE cr2.fecha BETWEEN ? AND ?)) * 100 AS porcentaje_total
        FROM 
            mov_remisiones mr
        INNER JOIN 
            cab_remisiones cr ON mr.id_remision = cr.id
        INNER JOIN 
            cat_productos cp ON mr.id_producto = cp.id_producto
        LEFT JOIN 
            cat_categorias cc ON cp.id_categoria = cc.id_categoria
        LEFT JOIN 
            cat_almacenes ca ON cr.id_almacen = ca.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin];
    $types = "ssss";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND cp.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    $sql .= " GROUP BY cp.id_producto, cp.clave, cp.nombre, cc.nombre_categoria";

    // Ordenamiento
    switch ($ordenarPor) {
        case 'ventas':
            $orderColumn = 'ventas_totales';
            break;
        case 'utilidad':
            $orderColumn = 'utilidad';
            break;
        case 'margen':
            $orderColumn = 'margen';
            break;
        default:
            $orderColumn = 'cantidad_vendida';
    }

    $sql .= " ORDER BY $orderColumn DESC";

    // Limitar resultados si se solicita un top N
    if ($topN > 0) {
        $sql .= " LIMIT ?";
        $params[] = $topN;
        $types .= "i";
    }

    // Consulta para el total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $totalResult = $clsConsulta->consultaPreparada($sqlCount, $params, $types);
    $recordsTotal = $totalResult && count($totalResult) > 0 ? $totalResult[0]['total'] : 0;

    // Agregar paginación (solo si no hay límite de top N o si el límite es mayor que la paginación)
    if ($topN == 0 || $topN > $length) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;
        $types .= "ii";
    }

    // Ejecutar consulta principal
    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Procesar datos para DataTables
    $data = [];
    if ($datos && count($datos) > 0) {
        // Calcular totales para porcentajes
        $totalVentas = 0;
        foreach ($datos as $item) {
            $totalVentas += floatval($item['ventas_totales']);
        }

        foreach ($datos as $index => $item) {
            $cantidadVendida = intval($item['cantidad_vendida']);
            $ventasTotales = floatval($item['ventas_totales']);
            $costoTotal = floatval($item['costo_total']);
            $utilidad = floatval($item['utilidad']);
            $margen = floatval($item['margen']);
            $porcentajeTotal = $totalVentas > 0 ? ($ventasTotales / $totalVentas) * 100 : 0;

            // Determinar tendencia (simulada - en un sistema real se compararía con periodo anterior)
            $tendencia = '';
            if ($index < 3) {
                $tendencia = '<i class="fas fa-arrow-up tendencia-up" title="Alta demanda"></i>';
            } elseif ($index < count($datos) * 0.7) {
                $tendencia = '<i class="fas fa-minus tendencia-stable" title="Demanda estable"></i>';
            } else {
                $tendencia = '<i class="fas fa-arrow-down tendencia-down" title="Baja demanda"></i>';
            }

            $data[] = [
                'producto' => $item['clave'] . ' - ' . $item['producto'],
                'categoria' => $item['categoria'] ?? 'Sin categoría',
                'cantidad_vendida' => number_format($cantidadVendida),
                'ventas_totales' => '$' . number_format($ventasTotales, 2),
                'costo_total' => '$' . number_format($costoTotal, 2),
                'utilidad' => '$' . number_format($utilidad, 2),
                'margen' => number_format($margen, 1) . '%',
                'porcentaje_total' => number_format($porcentajeTotal, 1) . '%',
                'tendencia' => $tendencia
            ];
        }
    }

    // Respuesta para DataTables
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data
    ]);
} catch (Exception $e) {
    error_log("Error en tabla-productos-mas-vendidos: " . $e->getMessage());
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error al generar el reporte'
    ]);
}
