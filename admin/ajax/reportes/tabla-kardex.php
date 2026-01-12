<?php
// ajax/reportes/tabla-kardex.php

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
$idProducto = $_POST['id_producto'] ?? 0;
$idAlmacen = $_POST['id_almacen'] ?? 0;
$tipoMovimiento = $_POST['tipo_movimiento'] ?? '';
$referencia = $_POST['referencia'] ?? '';
$agruparPor = $_POST['agrupar_por'] ?? 'producto';

try {
    // Consulta base para kárdex
    $sql = "
        SELECT 
            ib.fecha,
            cp.clave,
            cp.nombre AS producto,
            ca.almacen,
            ib.tipo_movimiento,
            ib.referencia,
            CASE 
                WHEN ib.tipo_movimiento LIKE '%entrada%' OR ib.tipo_movimiento LIKE '%compra%' THEN ib.cantidad
                ELSE 0 
            END AS entrada,
            CASE 
                WHEN ib.tipo_movimiento LIKE '%salida%' OR ib.tipo_movimiento LIKE '%venta%' THEN ib.cantidad
                ELSE 0 
            END AS salida,
            ib.cantidad AS movimiento,
            (SELECT precio_compra FROM cat_productos WHERE id_producto = ib.id_producto) AS costo_unitario,
            (ib.cantidad * (SELECT precio_compra FROM cat_productos WHERE id_producto = ib.id_producto)) AS costo_total,
            u.nombre AS usuario
        FROM 
            inventario_bitacora ib
        INNER JOIN 
            cat_productos cp ON ib.id_producto = cp.id_producto
        LEFT JOIN 
            cat_almacenes ca ON ib.id_almacen = ca.id
        LEFT JOIN 
            usuarios u ON ib.id_usuario = u.id
        WHERE 
            ib.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    // Aplicar filtros
    if ($idProducto > 0) {
        $sql .= " AND ib.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND ib.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if (!empty($tipoMovimiento)) {
        $sql .= " AND ib.tipo_movimiento LIKE ?";
        $params[] = "%$tipoMovimiento%";
        $types .= "s";
    }

    if (!empty($referencia)) {
        $sql .= " AND ib.referencia LIKE ?";
        $params[] = "%$referencia%";
        $types .= "s";
    }

    // Consulta para el total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $totalResult = $clsConsulta->consultaPreparada($sqlCount, $params, $types);
    $recordsTotal = $totalResult && count($totalResult) > 0 ? $totalResult[0]['total'] : 0;

    // Agregar ordenamiento y paginación
    $sql .= " ORDER BY ib.fecha DESC, ib.id DESC LIMIT ? OFFSET ?";
    $params[] = $length;
    $params[] = $start;
    $types .= "ii";

    // Ejecutar consulta principal
    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Procesar datos para DataTables
    $data = [];
    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            // Calcular saldo acumulado (esto es simplificado, en un sistema real se calcularía el saldo histórico)
            $saldo = $item['entrada'] - $item['salida'];

            $data[] = [
                'fecha' => date('d/m/Y', strtotime($item['fecha'])),
                'producto' => $item['clave'] . ' - ' . $item['producto'],
                'almacen' => $item['almacen'] ?? 'N/A',
                'tipo_movimiento' => ucfirst($item['tipo_movimiento']),
                'referencia' => $item['referencia'] ?? 'N/A',
                'entrada' => $item['entrada'] > 0 ? number_format($item['entrada']) : '',
                'salida' => $item['salida'] > 0 ? number_format($item['salida']) : '',
                'saldo' => number_format($saldo),
                'costo_unitario' => '$' . number_format($item['costo_unitario'] ?? 0, 2),
                'costo_total' => '$' . number_format($item['costo_total'] ?? 0, 2),
                'usuario' => $item['usuario'] ?? 'Sistema'
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
    error_log("Error en tabla-kardex: " . $e->getMessage());
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error al generar el reporte'
    ]);
}
