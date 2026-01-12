<?php
// ajax/reportes/ventas-por-cliente.php

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
$idCliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
$idVendedor = isset($_POST['id_vendedor']) ? intval($_POST['id_vendedor']) : 0;
$tipoVenta = isset($_POST['tipo_venta']) ? $_POST['tipo_venta'] : '';
$orden = isset($_POST['orden']) ? $_POST['orden'] : 'total_ventas';
$direccion = isset($_POST['direccion']) ? $_POST['direccion'] : 'DESC';
$limite = isset($_POST['limite']) ? intval($_POST['limite']) : 0;
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
            c.id,
            c.no_cliente,
            COALESCE(c.nombre_comercial, c.razon_social) AS nombre_cliente,
            CONCAT(v.nombre, ' ', v.apellido1, ' ', COALESCE(v.apellido2, '')) AS nombre_vendedor,
            cr.tipo_venta,
            COUNT(DISTINCT cr.id) AS cantidad_compras,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COALESCE(MIN(cr.total), 0) AS compra_minima,
            COALESCE(MAX(cr.total), 0) AS compra_maxima,
            MAX(cr.fecha) AS ultima_compra
        FROM 
            cat_clientes c
        LEFT JOIN 
            cab_remisiones cr ON c.id = cr.id_cliente
        LEFT JOIN 
            cat_vendedores v ON cr.id_vendedor = v.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    // Agregar filtros
    if ($idCliente > 0) {
        $sql .= " AND c.id = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    // Agrupar
    $sql .= " GROUP BY c.id, c.no_cliente, c.razon_social, c.nombre_comercial, v.nombre, v.apellido1, v.apellido2, cr.tipo_venta";

    // Ordenar
    $ordenValido = in_array($orden, ['total_ventas', 'cantidad_compras', 'nombre_cliente', 'ticket_promedio']) ? $orden : 'total_ventas';
    $direccionValida = strtoupper($direccion) === 'ASC' ? 'ASC' : 'DESC';

    if ($ordenValido === 'ticket_promedio') {
        $sql .= " ORDER BY (total_ventas / NULLIF(cantidad_compras, 0)) $direccionValida";
    } else {
        $sql .= " ORDER BY $ordenValido $direccionValida";
    }

    // Límite
    if ($limite > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limite;
        $types .= "i";
    }

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
    error_log("Error en reporte ventas por cliente: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
