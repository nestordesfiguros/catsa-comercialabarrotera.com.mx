<?php
// ajax/reportes/cuentas-por-pagar.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$fechaCorte = isset($_POST['fecha_corte']) ? $_POST['fecha_corte'] : '';
$idProveedor = isset($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : 0;
$estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
$diasVencimiento = isset($_POST['dias_vencimiento']) ? $_POST['dias_vencimiento'] : '';
$montoMinimo = isset($_POST['monto_minimo']) ? floatval($_POST['monto_minimo']) : 0;
$montoMaximo = isset($_POST['monto_maximo']) ? floatval($_POST['monto_maximo']) : 0;
$orden = isset($_POST['orden']) ? $_POST['orden'] : 'fecha_asc';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action !== 'generar') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

if (empty($fechaCorte)) {
    echo json_encode(['success' => false, 'message' => 'Fecha de corte requerida']);
    exit;
}

try {
    // Consulta principal para cuentas por pagar
    $sql = "
        SELECT 
            cxp.id,
            cxp.id_proveedor,
            cxp.id_compra,
            cxp.fecha,
            cxp.monto_total,
            cxp.monto_pagado,
            cxp.estatus,
            cxp.observaciones,
            p.razon_social as nombre_proveedor,
            p.nombre_Comercial,
            cc.fecha as fecha_compra,
            DATEDIFF(?, cxp.fecha) as dias_transcurridos
        FROM 
            cuentas_por_pagar cxp
        INNER JOIN 
            cat_proveedores p ON cxp.id_proveedor = p.id
        LEFT JOIN 
            cab_compras cc ON cxp.id_compra = cc.id
        WHERE 
            cxp.monto_total > cxp.monto_pagado
    ";

    $params = [$fechaCorte];
    $types = "s";

    if ($idProveedor > 0) {
        $sql .= " AND cxp.id_proveedor = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND cxp.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    }

    if ($montoMinimo > 0) {
        $sql .= " AND (cxp.monto_total - cxp.monto_pagado) >= ?";
        $params[] = $montoMinimo;
        $types .= "d";
    }

    if ($montoMaximo > 0) {
        $sql .= " AND (cxp.monto_total - cxp.monto_pagado) <= ?";
        $params[] = $montoMaximo;
        $types .= "d";
    }

    // Filtro por días de vencimiento
    if (!empty($diasVencimiento)) {
        $diasActual = "DATEDIFF(?, cxp.fecha)";
        $params[] = $fechaCorte;
        $types .= "s";

        switch ($diasVencimiento) {
            case '1-30':
                $sql .= " AND $diasActual BETWEEN 1 AND 30";
                break;
            case '31-60':
                $sql .= " AND $diasActual BETWEEN 31 AND 60";
                break;
            case '61-90':
                $sql .= " AND $diasActual BETWEEN 61 AND 90";
                break;
            case '91+':
                $sql .= " AND $diasActual > 90";
                break;
        }
    }

    // Ordenamiento
    $ordenSql = "";
    switch ($orden) {
        case 'fecha_asc':
            $ordenSql = "cxp.fecha ASC";
            break;
        case 'fecha_desc':
            $ordenSql = "cxp.fecha DESC";
            break;
        case 'monto_asc':
            $ordenSql = "(cxp.monto_total - cxp.monto_pagado) ASC";
            break;
        case 'monto_desc':
            $ordenSql = "(cxp.monto_total - cxp.monto_pagado) DESC";
            break;
        case 'proveedor':
            $ordenSql = "p.razon_social ASC";
            break;
        case 'dias_vencimiento':
            $ordenSql = "dias_transcurridos DESC";
            break;
        default:
            $ordenSql = "cxp.fecha ASC";
    }
    $sql .= " ORDER BY $ordenSql";

    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen
    $sqlResumen = "
        SELECT 
            COUNT(DISTINCT cxp.id_proveedor) as total_proveedores,
            SUM(CASE WHEN cxp.estatus = 'pendiente' THEN (cxp.monto_total - cxp.monto_pagado) ELSE 0 END) as total_pendiente,
            SUM(CASE WHEN cxp.estatus = 'vencida' THEN (cxp.monto_total - cxp.monto_pagado) ELSE 0 END) as total_vencido,
            SUM(cxp.monto_pagado) as total_pagado
        FROM 
            cuentas_por_pagar cxp
        WHERE 
            cxp.monto_total > cxp.monto_pagado
    ";

    $paramsResumen = [];
    $typesResumen = "";

    if ($idProveedor > 0) {
        $sqlResumen .= " AND cxp.id_proveedor = ?";
        $paramsResumen[] = $idProveedor;
        $typesResumen .= "i";
    }

    if (!empty($estatus)) {
        $sqlResumen .= " AND cxp.estatus = ?";
        $paramsResumen[] = $estatus;
        $typesResumen .= "s";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : null;

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
            'message' => 'No se encontraron cuentas por pagar con los criterios seleccionados'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en reporte cuentas por pagar: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
