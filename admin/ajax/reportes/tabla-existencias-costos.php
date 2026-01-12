<?php
// ajax/reportes/tabla-existencias-costos.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Parámetros de DataTables
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 25;
$draw = $_POST['draw'] ?? 1;

// Filtros
$idAlmacen = $_POST['id_almacen'] ?? 0;
$idCategoria = $_POST['id_categoria'] ?? 0;
$idProveedor = $_POST['id_proveedor'] ?? 0;
$estatusStock = $_POST['estatus_stock'] ?? '';
$ordenarPor = $_POST['ordenar_por'] ?? 'nombre';
$direccionOrden = $_POST['direccion_orden'] ?? 'asc';
$mostrarSolo = $_POST['mostrar_solo'] ?? '';

try {
    // Consulta base para existencias y costos
    $sql = "
        SELECT 
            cp.id_producto,
            cp.clave,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            ca.almacen,
            cprov.razon_social AS proveedor,
            COALESCE(i.cantidad, 0) AS existencia,
            cp.stock_minimo,
            cp.stock_maximo,
            cp.precio_compra AS costo_unitario,
            cp.precio_venta,
            (COALESCE(i.cantidad, 0) * cp.precio_compra) AS valor_total,
            cp.estado,
            cp.fecha_registro AS ultima_actualizacion
        FROM 
            cat_productos cp
        LEFT JOIN 
            inventarios i ON cp.id_producto = i.id_producto
        LEFT JOIN 
            cat_categorias cc ON cp.id_categoria = cc.id_categoria
        LEFT JOIN 
            cat_almacenes ca ON i.id_almacen = ca.id
        LEFT JOIN 
            cat_proveedores cprov ON cp.id_proveedor = cprov.id
        WHERE 
            1 = 1
    ";

    $params = [];
    $types = "";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND i.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND cp.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if ($idProveedor > 0) {
        $sql .= " AND cp.id_proveedor = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    // Filtro por estado de stock
    if (!empty($estatusStock)) {
        switch ($estatusStock) {
            case 'stock_minimo':
                $sql .= " AND COALESCE(i.cantidad, 0) <= cp.stock_minimo AND COALESCE(i.cantidad, 0) > 0";
                break;
            case 'stock_maximo':
                $sql .= " AND COALESCE(i.cantidad, 0) >= cp.stock_maximo";
                break;
            case 'sin_existencia':
                $sql .= " AND COALESCE(i.cantidad, 0) = 0";
                break;
            case 'con_existencia':
                $sql .= " AND COALESCE(i.cantidad, 0) > 0";
                break;
        }
    }

    // Filtro por estado del producto
    if ($mostrarSolo === 'activos') {
        $sql .= " AND cp.estado = 'activo'";
    } elseif ($mostrarSolo === 'inactivos') {
        $sql .= " AND cp.estado = 'inactivo'";
    }

    // Consulta para el total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $totalResult = $clsConsulta->consultaPreparada($sqlCount, $params, $types);
    $recordsTotal = $totalResult && count($totalResult) > 0 ? $totalResult[0]['total'] : 0;

    // Ordenamiento
    $orderColumn = 'cp.nombre'; // Valor por defecto
    switch ($ordenarPor) {
        case 'clave':
            $orderColumn = 'cp.clave';
            break;
        case 'existencia':
            $orderColumn = 'existencia';
            break;
        case 'costo':
            $orderColumn = 'cp.precio_compra';
            break;
        case 'valor':
            $orderColumn = 'valor_total';
            break;
        default:
            $orderColumn = 'cp.nombre';
    }

    $orderDirection = strtoupper($direccionOrden) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY $orderColumn $orderDirection, cp.clave ASC";

    // Agregar paginación
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $length;
    $params[] = $start;
    $types .= "ii";

    // Ejecutar consulta principal
    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Procesar datos para DataTables
    $data = [];
    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $existencia = intval($item['existencia']);
            $stockMinimo = intval($item['stock_minimo']);
            $stockMaximo = intval($item['stock_maximo']);

            // Determinar estado del stock
            $estadoStock = '';
            $estadoClass = '';

            if ($existencia === 0) {
                $estadoStock = 'Sin Existencia';
                $estadoClass = 'badge badge-danger';
            } elseif ($existencia <= $stockMinimo) {
                $estadoStock = 'Stock Mínimo';
                $estadoClass = 'badge badge-warning';
            } elseif ($existencia >= $stockMaximo && $stockMaximo > 0) {
                $estadoStock = 'Stock Máximo';
                $estadoClass = 'badge badge-info';
            } else {
                $estadoStock = 'Normal';
                $estadoClass = 'badge badge-success';
            }

            $data[] = [
                'clave' => $item['clave'],
                'producto' => $item['producto'],
                'categoria' => $item['categoria'] ?? 'Sin categoría',
                'almacen' => $item['almacen'] ?? 'N/A',
                'proveedor' => $item['proveedor'] ?? 'N/A',
                'existencia' => number_format($existencia),
                'stock_minimo' => number_format($stockMinimo),
                'stock_maximo' => $stockMaximo > 0 ? number_format($stockMaximo) : 'N/A',
                'estado' => '<span class="' . $estadoClass . '">' . $estadoStock . '</span>',
                'costo_unitario' => '$' . number_format($item['costo_unitario'] ?? 0, 2),
                'precio_venta' => '$' . number_format($item['precio_venta'] ?? 0, 2),
                'valor_total' => '$' . number_format($item['valor_total'] ?? 0, 2),
                'ultima_actualizacion' => $item['ultima_actualizacion'] ? date('d/m/Y', strtotime($item['ultima_actualizacion'])) : 'N/A'
            ];
        }
    }

    // Respuesta para DataTables
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data
    ]);
} catch (Exception $e) {
    error_log("Error en tabla-existencias-costos: " . $e->getMessage());
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error al generar el reporte'
    ]);
}
