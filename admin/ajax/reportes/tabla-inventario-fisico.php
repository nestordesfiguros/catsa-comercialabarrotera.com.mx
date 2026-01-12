<?php
// ajax/reportes/tabla-inventario-fisico.php

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
$fechaConteo = $_POST['fecha_conteo'] ?? date('Y-m-d');
$idAlmacen = $_POST['id_almacen'] ?? 0;
$idProducto = $_POST['id_producto'] ?? 0;
$estatusConteo = $_POST['estatus_conteo'] ?? '';
$responsable = $_POST['responsable'] ?? '';
$tipoReporte = $_POST['tipo_reporte'] ?? 'detallado';
$ordenarPor = $_POST['ordenar_por'] ?? 'producto';

try {
    // Consulta base para inventario físico
    $sql = "
        SELECT 
            ic.fecha_conteo AS fecha,
            cp.clave,
            cp.nombre AS producto,
            ca.almacen,
            COALESCE(i.cantidad, 0) AS existencia_sistema,
            ic.cantidad AS conteo_fisico,
            (ic.cantidad - COALESCE(i.cantidad, 0)) AS diferencia,
            CASE 
                WHEN COALESCE(i.cantidad, 0) > 0 THEN 
                    ABS((ic.cantidad - COALESCE(i.cantidad, 0)) / COALESCE(i.cantidad, 0)) * 100 
                ELSE 100 
            END AS porcentaje_diferencia,
            cp.precio_compra AS costo_unitario,
            (ABS(ic.cantidad - COALESCE(i.cantidad, 0)) * cp.precio_compra) AS valor_diferencia,
            ic.responsable,
            CASE 
                WHEN ic.aplicado = 1 THEN 'aplicado' 
                ELSE 'pendiente' 
            END AS estatus,
            ic.observaciones,
            ic.fecha_conteo_d
        FROM 
            inv_conteos ic
        INNER JOIN 
            cat_productos cp ON ic.id_producto = cp.id_producto
        LEFT JOIN 
            inventarios i ON ic.id_producto = i.id_producto AND ic.id_almacen = i.id_almacen
        LEFT JOIN 
            cat_almacenes ca ON ic.id_almacen = ca.id
        WHERE 
            ic.fecha_conteo_d = ?
    ";

    $params = [$fechaConteo];
    $types = "s";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND ic.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idProducto > 0) {
        $sql .= " AND ic.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if (!empty($estatusConteo)) {
        if ($estatusConteo === 'aplicado') {
            $sql .= " AND ic.aplicado = 1";
        } elseif ($estatusConteo === 'pendiente') {
            $sql .= " AND ic.aplicado = 0";
        } elseif ($estatusConteo === 'con_diferencias') {
            $sql .= " AND ic.cantidad != COALESCE(i.cantidad, 0)";
        }
    }

    if (!empty($responsable)) {
        $sql .= " AND ic.responsable LIKE ?";
        $params[] = "%$responsable%";
        $types .= "s";
    }

    // Consulta para el total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $totalResult = $clsConsulta->consultaPreparada($sqlCount, $params, $types);
    $recordsTotal = $totalResult && count($totalResult) > 0 ? $totalResult[0]['total'] : 0;

    // Ordenamiento
    $orderColumn = 'cp.nombre';
    switch ($ordenarPor) {
        case 'diferencia':
            $orderColumn = 'ABS(diferencia)';
            break;
        case 'fecha':
            $orderColumn = 'ic.fecha_conteo';
            break;
        case 'almacen':
            $orderColumn = 'ca.almacen';
            break;
        default:
            $orderColumn = 'cp.nombre';
    }

    $sql .= " ORDER BY $orderColumn DESC, cp.nombre ASC";

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
            $diferencia = intval($item['diferencia']);
            $porcentajeDiferencia = floatval($item['porcentaje_diferencia']);

            // Determinar clase CSS para la diferencia
            $diferenciaClass = $diferencia > 0 ? 'diferencia-positiva' : ($diferencia < 0 ? 'diferencia-negativa' : '');
            $diferenciaTexto = $diferencia > 0 ? '+' . $diferencia : $diferencia;

            // Determinar estatus
            $estatusClass = $item['estatus'] === 'aplicado' ? 'badge badge-success' : 'badge badge-warning';
            $estatusTexto = $item['estatus'] === 'aplicado' ? 'Aplicado' : 'Pendiente';

            $data[] = [
                'fecha' => date('d/m/Y', strtotime($item['fecha'])),
                'producto' => $item['clave'] . ' - ' . $item['producto'],
                'almacen' => $item['almacen'] ?? 'N/A',
                'existencia_sistema' => number_format($item['existencia_sistema']),
                'conteo_fisico' => number_format($item['conteo_fisico']),
                'diferencia' => '<span class="' . $diferenciaClass . '">' . $diferenciaTexto . '</span>',
                'porcentaje_diferencia' => number_format($porcentajeDiferencia, 1) . '%',
                'costo_unitario' => '$' . number_format($item['costo_unitario'] ?? 0, 2),
                'valor_diferencia' => '$' . number_format($item['valor_diferencia'] ?? 0, 2),
                'responsable' => $item['responsable'] ?? 'N/A',
                'estatus' => '<span class="' . $estatusClass . '">' . $estatusTexto . '</span>',
                'observaciones' => $item['observaciones'] ?? ''
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
    error_log("Error en tabla-inventario-fisico: " . $e->getMessage());
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error al generar el reporte'
    ]);
}
