<?php
// ajax/reportes/tabla-stock-minimo-maximo.php

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
$tipoAlerta = $_POST['tipo_alerta'] ?? '';
$ordenarPor = $_POST['ordenar_por'] ?? 'diferencia_min';
$mostrarSolo = $_POST['mostrar_solo'] ?? '';
$nivelCritico = $_POST['nivel_critico'] ?? 20;

try {
    // Consulta base para stock mínimo y máximo
    $sql = "
        SELECT 
            cp.id_producto,
            cp.clave,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            ca.almacen,
            COALESCE(i.cantidad, 0) AS existencia,
            cp.stock_minimo,
            cp.stock_maximo,
            (COALESCE(i.cantidad, 0) - cp.stock_minimo) AS diferencia_min,
            (COALESCE(i.cantidad, 0) - cp.stock_maximo) AS diferencia_max,
            CASE 
                WHEN cp.stock_minimo > 0 THEN 
                    (COALESCE(i.cantidad, 0) / cp.stock_minimo) * 100 
                ELSE 100 
            END AS porcentaje_min,
            cp.precio_compra AS costo_unitario,
            (cp.stock_minimo - COALESCE(i.cantidad, 0)) * cp.precio_compra AS valor_riesgo,
            cp.estado
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
            cp.estado = 'activo'
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

    // Filtro por tipo de alerta
    if (!empty($tipoAlerta)) {
        switch ($tipoAlerta) {
            case 'stock_minimo':
                $sql .= " AND COALESCE(i.cantidad, 0) <= cp.stock_minimo";
                break;
            case 'stock_maximo':
                $sql .= " AND COALESCE(i.cantidad, 0) >= cp.stock_maximo AND cp.stock_maximo > 0";
                break;
            case 'sin_existencia':
                $sql .= " AND COALESCE(i.cantidad, 0) = 0";
                break;
            case 'critico':
                $sql .= " AND COALESCE(i.cantidad, 0) > 0 AND COALESCE(i.cantidad, 0) <= cp.stock_minimo AND cp.stock_minimo > 0";
                break;
        }
    }

    // Filtro por mostrar solo
    if ($mostrarSolo === 'con_alerta') {
        $sql .= " AND (COALESCE(i.cantidad, 0) <= cp.stock_minimo OR COALESCE(i.cantidad, 0) >= cp.stock_maximo OR COALESCE(i.cantidad, 0) = 0)";
    } elseif ($mostrarSolo === 'sin_alerta') {
        $sql .= " AND COALESCE(i.cantidad, 0) > cp.stock_minimo AND (COALESCE(i.cantidad, 0) < cp.stock_maximo OR cp.stock_maximo = 0)";
    } elseif ($mostrarSolo === 'activos') {
        $sql .= " AND cp.estado = 'activo'";
    }

    // Consulta para el total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $totalResult = $clsConsulta->consultaPreparada($sqlCount, $params, $types);
    $recordsTotal = $totalResult && count($totalResult) > 0 ? $totalResult[0]['total'] : 0;

    // Ordenamiento
    switch ($ordenarPor) {
        case 'diferencia_max':
            $orderColumn = 'diferencia_max';
            break;
        case 'producto':
            $orderColumn = 'cp.nombre';
            break;
        case 'existencia':
            $orderColumn = 'existencia';
            break;
        case 'nivel_critico':
            $orderColumn = 'porcentaje_min';
            break;
        default:
            $orderColumn = 'diferencia_min';
    }

    $sql .= " ORDER BY $orderColumn ASC, cp.nombre ASC";

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
            $diferenciaMin = intval($item['diferencia_min']);
            $diferenciaMax = intval($item['diferencia_max']);
            $porcentajeMin = floatval($item['porcentaje_min']);
            $valorRiesgo = floatval($item['valor_riesgo']);

            // Determinar nivel de stock
            $nivel = '';
            $nivelClass = '';
            $alertas = [];

            if ($existencia === 0) {
                $nivel = 'Sin Existencia';
                $nivelClass = 'nivel-critico';
                $alertas[] = 'sin_existencia';
            } elseif ($existencia <= $stockMinimo) {
                $nivel = 'Stock Mínimo';
                $nivelClass = 'nivel-critico';
                $alertas[] = 'stock_minimo';

                // Verificar si es stock crítico
                if ($stockMinimo > 0 && $porcentajeMin <= $nivelCritico) {
                    $alertas[] = 'critico';
                    $nivel = 'Crítico';
                }
            } elseif ($stockMaximo > 0 && $existencia >= $stockMaximo) {
                $nivel = 'Stock Máximo';
                $nivelClass = 'nivel-exceso';
                $alertas[] = 'stock_maximo';
            } else {
                $nivel = 'Normal';
                $nivelClass = 'nivel-normal';
            }

            // Generar badges de alertas
            $alertasHTML = '';
            foreach ($alertas as $alerta) {
                $badgeClass = '';
                $badgeText = '';

                switch ($alerta) {
                    case 'sin_existencia':
                        $badgeClass = 'badge badge-danger';
                        $badgeText = 'Sin Stock';
                        break;
                    case 'stock_minimo':
                        $badgeClass = 'badge badge-warning';
                        $badgeText = 'Mínimo';
                        break;
                    case 'stock_maximo':
                        $badgeClass = 'badge badge-info';
                        $badgeText = 'Máximo';
                        break;
                    case 'critico':
                        $badgeClass = 'badge badge-danger';
                        $badgeText = 'Crítico';
                        break;
                }

                $alertasHTML .= '<span class="badge-alerta ' . $badgeClass . '">' . $badgeText . '</span>';
            }

            $data[] = [
                'producto' => $item['clave'] . ' - ' . $item['producto'],
                'categoria' => $item['categoria'] ?? 'Sin categoría',
                'almacen' => $item['almacen'] ?? 'N/A',
                'existencia' => number_format($existencia),
                'stock_minimo' => number_format($stockMinimo),
                'stock_maximo' => $stockMaximo > 0 ? number_format($stockMaximo) : 'N/A',
                'diferencia_min' => $diferenciaMin < 0 ? '<span class="text-danger">' . $diferenciaMin . '</span>' : ($diferenciaMin > 0 ? '<span class="text-success">+' . $diferenciaMin . '</span>' : '0'),
                'diferencia_max' => $stockMaximo > 0 ?
                    ($diferenciaMax > 0 ? '<span class="text-warning">+' . $diferenciaMax . '</span>' : ($diferenciaMax < 0 ? '<span class="text-success">' . $diferenciaMax . '</span>' : '0')) : 'N/A',
                'porcentaje_min' => number_format($porcentajeMin, 1) . '%',
                'nivel' => '<span class="' . $nivelClass . '">' . $nivel . '</span>',
                'alertas' => $alertasHTML,
                'valor_riesgo' => '$' . number_format(max(0, $valorRiesgo), 2)
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
    error_log("Error en tabla-stock-minimo-maximo: " . $e->getMessage());
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error al generar el reporte'
    ]);
}
