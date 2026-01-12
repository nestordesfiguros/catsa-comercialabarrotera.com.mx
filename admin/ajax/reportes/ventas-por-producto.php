<?php
// ajax/reportes/ventas-por-producto.php

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
$idProducto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
$idCategoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
$idAlmacen = isset($_POST['id_almacen']) ? intval($_POST['id_almacen']) : 0;
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
            p.id_producto,
            p.clave AS clave_producto,
            p.nombre AS nombre_producto,
            c.nombre_categoria,
            a.almacen AS nombre_almacen,
            COALESCE(SUM(mr.cantidad), 0) AS cantidad_vendida,
            COALESCE(AVG(mr.precio_unitario), 0) AS precio_promedio,
            COALESCE(SUM(mr.cantidad * mr.precio_unitario), 0) AS total_ventas,
            COALESCE(SUM(mr.cantidad * p.precio_compra), 0) AS costo_total
        FROM 
            cat_productos p
        LEFT JOIN 
            mov_remisiones mr ON p.id_producto = mr.id_producto
        LEFT JOIN 
            cab_remisiones cr ON mr.id_remision = cr.id
        LEFT JOIN 
            cat_categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN 
            cat_almacenes a ON cr.id_almacen = a.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    // Agregar filtros
    if ($idProducto > 0) {
        $sql .= " AND p.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND p.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    // Agrupar
    $sql .= " GROUP BY p.id_producto, p.clave, p.nombre, c.nombre_categoria, a.almacen";

    // Ordenar
    $ordenValido = in_array($orden, ['total_ventas', 'cantidad_vendida', 'nombre', 'utilidad']) ? $orden : 'total_ventas';
    $direccionValida = strtoupper($direccion) === 'ASC' ? 'ASC' : 'DESC';

    if ($ordenValido === 'utilidad') {
        $sql .= " ORDER BY (total_ventas - costo_total) $direccionValida";
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
    error_log("Error en reporte ventas por producto: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
