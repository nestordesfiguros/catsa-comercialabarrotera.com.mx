<?php
// ajax/reportes/resumen-notas-venta.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$fechaInicio = $_POST['fecha_inicio'] ?? '';
$fechaFin = $_POST['fecha_fin'] ?? '';
$tipoVenta = $_POST['tipo_venta'] ?? '';
$estatus = $_POST['estatus'] ?? '';
$idVendedor = $_POST['id_vendedor'] ?? 0;
$idCliente = $_POST['id_cliente'] ?? 0;
$idAlmacen = $_POST['id_almacen'] ?? 0;
$action = $_POST['action'] ?? '';

if ($action !== 'generar') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

if (empty($fechaInicio) || empty($fechaFin)) {
    echo json_encode(['success' => false, 'message' => 'Fechas requeridas']);
    exit;
}

try {
    // Construir consulta base para notas de venta
    // NOTA: cab_remisiones no tiene columna 'subtotal', usamos 'total' directamente
    $sql = "
        SELECT 
            cr.id,
            cr.fecha,
            cr.total,
            cr.estatus,
            cr.tipo_venta,
            cc.razon_social AS cliente,
            CONCAT(cv.nombre, ' ', cv.apellido1, ' ', COALESCE(cv.apellido2, '')) AS vendedor,
            ca.almacen,
            (SELECT COUNT(*) FROM mov_remisiones mr WHERE mr.id_remision = cr.id) AS total_productos,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr WHERE mr.id_remision = cr.id) AS cantidad_total
        FROM 
            cab_remisiones cr
        LEFT JOIN 
            cat_clientes cc ON cr.id_cliente = cc.id
        LEFT JOIN 
            cat_vendedores cv ON cr.id_vendedor = cv.id
        LEFT JOIN 
            cat_almacenes ca ON cr.id_almacen = ca.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if (!empty($estatus)) {
        $sql .= " AND cr.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idCliente > 0) {
        $sql .= " AND cr.id_cliente = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " ORDER BY cr.fecha DESC, cr.id DESC";

    // Ejecutar consulta
    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen general
    $sqlResumen = "
        SELECT 
            COUNT(*) AS cantidad_notas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COALESCE(AVG(cr.total), 0) AS ticket_promedio,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'contado' THEN cr.total ELSE 0 END), 0) AS ventas_contado,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'credito' THEN cr.total ELSE 0 END), 0) AS ventas_credito,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE cr2.fecha BETWEEN ? AND ?) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            cr.fecha BETWEEN ? AND ?
    ";

    $paramsResumen = [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin];
    $typesResumen = "ssss";

    if (!empty($tipoVenta)) {
        $sqlResumen .= " AND cr.tipo_venta = ?";
        $paramsResumen[] = $tipoVenta;
        $typesResumen .= "s";
    }

    if (!empty($estatus)) {
        $sqlResumen .= " AND cr.estatus = ?";
        $paramsResumen[] = $estatus;
        $typesResumen .= "s";
    }

    if ($idVendedor > 0) {
        $sqlResumen .= " AND cr.id_vendedor = ?";
        $paramsResumen[] = $idVendedor;
        $typesResumen .= "i";
    }

    if ($idCliente > 0) {
        $sqlResumen .= " AND cr.id_cliente = ?";
        $paramsResumen[] = $idCliente;
        $typesResumen .= "i";
    }

    if ($idAlmacen > 0) {
        $sqlResumen .= " AND cr.id_almacen = ?";
        $paramsResumen[] = $idAlmacen;
        $typesResumen .= "i";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : [
        'cantidad_notas' => 0,
        'total_ventas' => 0,
        'ticket_promedio' => 0,
        'ventas_contado' => 0,
        'ventas_credito' => 0,
        'total_productos' => 0
    ];

    // Consulta para top vendedores
    $sqlTopVendedores = "
        SELECT 
            CONCAT(cv.nombre, ' ', cv.apellido1, ' ', COALESCE(cv.apellido2, '')) AS vendedor,
            COUNT(cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas
        FROM 
            cab_remisiones cr
        INNER JOIN 
            cat_vendedores cv ON cr.id_vendedor = cv.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $paramsTop = [$fechaInicio, $fechaFin];
    $typesTop = "ss";

    if (!empty($tipoVenta)) {
        $sqlTopVendedores .= " AND cr.tipo_venta = ?";
        $paramsTop[] = $tipoVenta;
        $typesTop .= "s";
    }

    if (!empty($estatus)) {
        $sqlTopVendedores .= " AND cr.estatus = ?";
        $paramsTop[] = $estatus;
        $typesTop .= "s";
    }

    if ($idAlmacen > 0) {
        $sqlTopVendedores .= " AND cr.id_almacen = ?";
        $paramsTop[] = $idAlmacen;
        $typesTop .= "i";
    }

    $sqlTopVendedores .= " GROUP BY cv.id, vendedor ORDER BY total_ventas DESC LIMIT 5";

    $topVendedores = $clsConsulta->consultaPreparada($sqlTopVendedores, $paramsTop, $typesTop);

    if ($resultados && count($resultados) > 0) {
        echo json_encode([
            'success' => true,
            'data' => $resultados,
            'resumen' => $resumenData,
            'top_vendedores' => $topVendedores ?: [],
            'total_registros' => count($resultados)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [],
            'resumen' => $resumenData,
            'top_vendedores' => [],
            'message' => 'No se encontraron notas de venta con los criterios seleccionados'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en reporte resumen notas venta: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
