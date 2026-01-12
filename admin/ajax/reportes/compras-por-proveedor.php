<?php
// ajax/reportes/compras-por-proveedor.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
$idProveedor = isset($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : 0;
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
    $sql = "
        SELECT 
            p.id AS id_proveedor,
            CONCAT(p.razon_social, ' - ', p.nombre_Comercial) AS nombre_proveedor,
            COUNT(DISTINCT c.id) AS cantidad_compras,
            COALESCE(SUM(c.total), 0) AS total_compras,
            COALESCE(MIN(c.total), 0) AS compra_minima,
            COALESCE(MAX(c.total), 0) AS compra_maxima,
            MAX(c.fecha) AS ultima_compra
        FROM 
            cat_proveedores p
        LEFT JOIN 
            cab_compras c ON p.id = c.id_proveedor 
            AND c.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idProveedor > 0) {
        $sql .= " AND p.id = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND c.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    } else {
        $sql .= " AND c.estatus IN ('procesada', 'pendiente')";
    }

    $sql .= " 
        GROUP BY 
            p.id, p.razon_social, p.nombre_Comercial
        HAVING 
            cantidad_compras > 0 OR ? = 1
        ORDER BY 
            total_compras DESC
    ";

    $params[] = ($idProveedor > 0) ? 0 : 1;
    $types .= "i";

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
    error_log("Error en reporte compras por proveedor: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
