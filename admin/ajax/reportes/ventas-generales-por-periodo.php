<?php
// ajax/reportes/ventas-generales-por-periodo.php

// Incluir la clase de consultas
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener y validar parámetros
$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
$tipoPeriodo = isset($_POST['tipo_periodo']) ? $_POST['tipo_periodo'] : 'mes';
$tipoVenta = isset($_POST['tipo_venta']) ? $_POST['tipo_venta'] : '';
$idVendedor = isset($_POST['id_vendedor']) ? intval($_POST['id_vendedor']) : 0;
$idAlmacen = isset($_POST['id_almacen']) ? intval($_POST['id_almacen']) : 0;
$estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
$orden = isset($_POST['orden']) ? $_POST['orden'] : 'periodo';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action !== 'generar') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

if (empty($fechaInicio) || empty($fechaFin)) {
    echo json_encode(['success' => false, 'message' => 'Fechas requeridas']);
    exit;
}

try {
    // Determinar la agrupación por periodo
    $groupBy = '';
    $periodFormat = '';

    switch ($tipoPeriodo) {
        case 'dia':
            $groupBy = 'DATE(cr.fecha)';
            $periodFormat = "DATE_FORMAT(cr.fecha, '%d/%m/%Y')";
            break;
        case 'semana':
            $groupBy = 'YEARWEEK(cr.fecha)';
            $periodFormat = "CONCAT('Sem ', YEARWEEK(cr.fecha))";
            break;
        case 'mes':
            $groupBy = 'YEAR(cr.fecha), MONTH(cr.fecha)';
            $periodFormat = "DATE_FORMAT(cr.fecha, '%M %Y')";
            break;
        case 'anio':
            $groupBy = 'YEAR(cr.fecha)';
            $periodFormat = "YEAR(cr.fecha)";
            break;
        default:
            $groupBy = 'YEAR(cr.fecha), MONTH(cr.fecha)';
            $periodFormat = "DATE_FORMAT(cr.fecha, '%M %Y')";
    }

    // Construir consulta base para datos agrupados
    $sql = "
        SELECT 
            $periodFormat AS periodo,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COALESCE(AVG(cr.total), 0) AS ticket_promedio,
            COALESCE(MIN(cr.total), 0) AS venta_minima,
            COALESCE(MAX(cr.total), 0) AS venta_maxima,
            (SELECT COALESCE(SUM(total), 0) FROM cab_remisiones WHERE fecha BETWEEN ? AND ?) AS total_general
        FROM 
            cab_remisiones cr
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin];
    $types = "ssss";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND cr.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    }

    // Agrupar y ordenar
    $sql .= " GROUP BY $groupBy";

    $ordenValido = in_array($orden, ['periodo', 'total_ventas', 'cantidad_ventas']) ? $orden : 'periodo';
    $sql .= " ORDER BY $ordenValido " . ($ordenValido === 'periodo' ? 'ASC' : 'DESC');

    // Ejecutar consulta para datos agrupados
    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen general
    $sqlResumen = "
        SELECT 
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(AVG(cr.total), 0) AS ticket_promedio,
            COUNT(DISTINCT DATE(cr.fecha)) AS dias_con_ventas,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'contado' THEN cr.total ELSE 0 END), 0) AS ventas_contado,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'credito' THEN cr.total ELSE 0 END), 0) AS ventas_credito
        FROM 
            cab_remisiones cr
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $paramsResumen = [$fechaInicio, $fechaFin];
    $typesResumen = "ss";

    if (!empty($tipoVenta)) {
        $sqlResumen .= " AND cr.tipo_venta = ?";
        $paramsResumen[] = $tipoVenta;
        $typesResumen .= "s";
    }

    if ($idVendedor > 0) {
        $sqlResumen .= " AND cr.id_vendedor = ?";
        $paramsResumen[] = $idVendedor;
        $typesResumen .= "i";
    }

    if ($idAlmacen > 0) {
        $sqlResumen .= " AND cr.id_almacen = ?";
        $paramsResumen[] = $idAlmacen;
        $typesResumen .= "i";
    }

    if (!empty($estatus)) {
        $sqlResumen .= " AND cr.estatus = ?";
        $paramsResumen[] = $estatus;
        $typesResumen .= "s";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : [
        'total_ventas' => 0,
        'cantidad_ventas' => 0,
        'ticket_promedio' => 0,
        'dias_con_ventas' => 0,
        'ventas_contado' => 0,
        'ventas_credito' => 0
    ];

    if ($resultados && count($resultados) > 0) {
        echo json_encode([
            'success' => true,
            'data' => $resultados,
            'resumen' => $resumenData,
            'total_registros' => count($resultados)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [],
            'resumen' => $resumenData,
            'message' => 'No se encontraron ventas con los criterios seleccionados'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en reporte ventas generales por periodo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
