<?php
// ajax/reportes/detallado-facturas.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$fechaInicio = $_POST['fecha_inicio'] ?? '';
$fechaFin = $_POST['fecha_fin'] ?? '';
$estatusFactura = $_POST['estatus_factura'] ?? '';
$timbrada = $_POST['timbrada'] ?? '';
$idCliente = $_POST['id_cliente'] ?? 0;
$serie = $_POST['serie'] ?? '';
$folio = $_POST['folio'] ?? '';
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
    // Construir consulta base
    $sql = "
        SELECT 
            cf.id,
            cf.serie,
            cf.folio,
            cf.fecha,
            cf.subtotal,
            cf.total,
            cf.estatus,
            cf.timbrada,
            cf.uuid,
            cc.razon_social,
            cc.rfc
        FROM 
            cab_facturas cf
        LEFT JOIN 
            cat_clientes cc ON cf.id_receptor = cc.id
        WHERE 
            cf.fecha BETWEEN ? AND ?
            AND cf.activo = 1
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    // Agregar filtros
    if (!empty($estatusFactura)) {
        $sql .= " AND cf.estatus = ?";
        $params[] = $estatusFactura;
        $types .= "s";
    }

    if ($timbrada !== '') {
        $sql .= " AND cf.timbrada = ?";
        $params[] = $timbrada;
        $types .= "i";
    }

    if ($idCliente > 0) {
        $sql .= " AND cf.id_receptor = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if (!empty($serie)) {
        $sql .= " AND cf.serie LIKE ?";
        $params[] = "%$serie%";
        $types .= "s";
    }

    if (!empty($folio)) {
        $sql .= " AND cf.folio LIKE ?";
        $params[] = "%$folio%";
        $types .= "s";
    }

    $sql .= " ORDER BY cf.fecha DESC, cf.id DESC";

    // Ejecutar consulta
    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen general
    $sqlResumen = "
        SELECT 
            COUNT(*) AS cantidad_facturas,
            COALESCE(SUM(cf.total), 0) AS total_facturado,
            COALESCE(AVG(cf.total), 0) AS promedio_factura,
            SUM(CASE WHEN cf.timbrada = 1 THEN 1 ELSE 0 END) AS facturas_timbradas
        FROM 
            cab_facturas cf
        WHERE 
            cf.fecha BETWEEN ? AND ?
            AND cf.activo = 1
    ";

    $paramsResumen = [$fechaInicio, $fechaFin];
    $typesResumen = "ss";

    if (!empty($estatusFactura)) {
        $sqlResumen .= " AND cf.estatus = ?";
        $paramsResumen[] = $estatusFactura;
        $typesResumen .= "s";
    }

    if ($timbrada !== '') {
        $sqlResumen .= " AND cf.timbrada = ?";
        $paramsResumen[] = $timbrada;
        $typesResumen .= "i";
    }

    if ($idCliente > 0) {
        $sqlResumen .= " AND cf.id_receptor = ?";
        $paramsResumen[] = $idCliente;
        $typesResumen .= "i";
    }

    if (!empty($serie)) {
        $sqlResumen .= " AND cf.serie LIKE ?";
        $paramsResumen[] = "%$serie%";
        $typesResumen .= "s";
    }

    if (!empty($folio)) {
        $sqlResumen .= " AND cf.folio LIKE ?";
        $paramsResumen[] = "%$folio%";
        $typesResumen .= "s";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : [
        'cantidad_facturas' => 0,
        'total_facturado' => 0,
        'promedio_factura' => 0,
        'facturas_timbradas' => 0
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
            'message' => 'No se encontraron facturas con los criterios seleccionados'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en reporte detallado de facturas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
