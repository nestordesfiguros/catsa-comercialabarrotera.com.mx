<?php
// ajax/reportes/compras-generales-por-periodo.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
$agrupacion = isset($_POST['agrupacion']) ? $_POST['agrupacion'] : 'mensual';
$estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
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
    // Determinar la agrupación según el parámetro
    $formatoFecha = '';
    $formatoMostrar = '';

    switch ($agrupacion) {
        case 'diario':
            $formatoFecha = '%Y-%m-%d';
            $formatoMostrar = '%d/%m/%Y';
            break;
        case 'semanal':
            $formatoFecha = '%Y-%u';
            $formatoMostrar = 'Semana %u de %Y';
            break;
        case 'mensual':
            $formatoFecha = '%Y-%m';
            $formatoMostrar = '%M %Y';
            break;
        case 'anual':
            $formatoFecha = '%Y';
            $formatoMostrar = '%Y';
            break;
        default:
            $formatoFecha = '%Y-%m';
            $formatoMostrar = '%M %Y';
    }

    $sql = "
        SELECT 
            DATE_FORMAT(c.fecha, ?) as periodo_fecha,
            DATE_FORMAT(c.fecha, ?) as periodo,
            COUNT(DISTINCT c.id) as cantidad_compras,
            COALESCE(SUM(c.total), 0) as total_compras,
            COALESCE(MIN(c.total), 0) as compra_minima,
            COALESCE(MAX(c.total), 0) as compra_maxima,
            GROUP_CONCAT(DISTINCT p.id) as proveedores_unicos,
            COUNT(DISTINCT p.id) as proveedores_unicos_count
        FROM 
            cab_compras c
        LEFT JOIN 
            cat_proveedores p ON c.id_proveedor = p.id
        WHERE 
            c.fecha BETWEEN ? AND ?
    ";

    $params = [$formatoFecha, $formatoMostrar, $fechaInicio, $fechaFin];
    $types = "ssss";

    if (!empty($estatus)) {
        $sql .= " AND c.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    } else {
        $sql .= " AND c.estatus IN ('procesada', 'pendiente')";
    }

    $sql .= " 
        GROUP BY 
            periodo_fecha, periodo
        ORDER BY 
            periodo_fecha ASC
    ";

    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    if ($resultados && count($resultados) > 0) {
        echo json_encode([
            'success' => true,
            'data' => $resultados,
            'total_registros' => count($resultados)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'No se encontraron compras con los criterios seleccionados'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en reporte compras generales por periodo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
