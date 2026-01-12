<?php
// ajax/reportes/ventas-por-vendedor.php

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
$idVendedor = isset($_POST['id_vendedor']) ? intval($_POST['id_vendedor']) : 0;
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
    // Construir consulta base
    $sql = "
        SELECT 
            v.id AS id_vendedor,
            CONCAT(v.nombre, ' ', v.apellido1, ' ', COALESCE(v.apellido2, '')) AS nombre_vendedor,
            v.comision AS porcentaje_comision,
            COUNT(DISTINCT r.id) AS cantidad_ventas,
            COALESCE(SUM(r.total), 0) AS total_ventas,
            COALESCE(MIN(r.total), 0) AS venta_minima,
            COALESCE(MAX(r.total), 0) AS venta_maxima
        FROM 
            cat_vendedores v
        LEFT JOIN 
            cab_remisiones r ON v.id = r.id_vendedor 
            AND r.fecha BETWEEN ? AND ?
    ";

    // Agregar condiciones según filtros
    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idVendedor > 0) {
        $sql .= " AND v.id = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND r.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    } else {
        $sql .= " AND r.estatus IN ('procesada', 'pendiente')";
    }

    // Agrupar y ordenar
    $sql .= " 
        GROUP BY 
            v.id, v.nombre, v.apellido1, v.apellido2, v.comision
        HAVING 
            cantidad_ventas > 0 OR ? = 1
        ORDER BY 
            total_ventas DESC
    ";

    // Si no se filtró por vendedor específico, incluir todos los vendedores
    $params[] = ($idVendedor > 0) ? 0 : 1;
    $types .= "i";

    // Ejecutar consulta preparada
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
            'message' => 'No se encontraron ventas con los criterios seleccionados'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en reporte ventas por vendedor: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
