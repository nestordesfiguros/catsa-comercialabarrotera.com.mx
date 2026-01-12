<?php
// ajax/reportes/compras-por-producto.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
$idProducto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
$idProveedor = isset($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : 0;
$categoria = isset($_POST['categoria']) ? intval($_POST['categoria']) : 0;
$orden = isset($_POST['orden']) ? $_POST['orden'] : 'total_compras';
$direccion = isset($_POST['direccion']) ? $_POST['direccion'] : 'DESC';
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
            p.id_producto,
            p.clave as clave_producto,
            p.nombre as nombre_producto,
            c.nombre_categoria,
            pr.razon_social as nombre_proveedor,
            SUM(mc.cantidad) as cantidad_comprada,
            COALESCE(SUM(mc.cantidad * mc.precio), 0) as total_compras,
            MAX(cc.fecha) as ultima_compra
        FROM 
            cat_productos p
        LEFT JOIN 
            mov_compras mc ON p.id_producto = mc.id_producto
        LEFT JOIN 
            cab_compras cc ON mc.id_orden_compra = cc.id
            AND cc.fecha BETWEEN ? AND ?
        LEFT JOIN 
            cat_categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN 
            cat_proveedores pr ON cc.id_proveedor = pr.id
        WHERE 
            cc.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idProducto > 0) {
        $sql .= " AND p.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idProveedor > 0) {
        $sql .= " AND cc.id_proveedor = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    if ($categoria > 0) {
        $sql .= " AND p.id_categoria = ?";
        $params[] = $categoria;
        $types .= "i";
    }

    $sql .= " 
        GROUP BY 
            p.id_producto, p.clave, p.nombre, c.nombre_categoria, pr.razon_social
        HAVING 
            cantidad_comprada > 0
    ";

    // Validar y mapear campos de orden
    $ordenCampos = [
        'total_compras' => 'total_compras',
        'cantidad_comprada' => 'cantidad_comprada',
        'nombre_producto' => 'p.nombre'
    ];

    $campoOrden = $ordenCampos[$orden] ?? 'total_compras';
    $sql .= " ORDER BY $campoOrden $direccion";

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
    error_log("Error en reporte compras por producto: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
