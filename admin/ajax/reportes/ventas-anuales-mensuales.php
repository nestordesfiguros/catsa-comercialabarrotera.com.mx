<?php
// ajax/reportes/ventas-anuales-mensuales.php

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$tipoReporte = $_POST['tipo_reporte'] ?? 'mensual';
$anio = $_POST['anio'] ?? date('Y');
$anioComparativo = $_POST['anio_comparativo'] ?? (date('Y') - 1);
$tipoVenta = $_POST['tipo_venta'] ?? '';
$idVendedor = $_POST['id_vendedor'] ?? 0;
$idAlmacen = $_POST['id_almacen'] ?? 0;
$metrica = $_POST['metrica'] ?? 'ventas';
$action = $_POST['action'] ?? '';

if ($action !== 'generar') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

try {
    $datos = [];
    $resumenData = [];

    switch ($tipoReporte) {
        case 'anual':
            $datos = generarReporteAnual($anio, $tipoVenta, $idVendedor, $idAlmacen);
            $resumenData = calcularResumenAnual($datos, $anio);
            break;
        case 'mensual':
            $datos = generarReporteMensual($anio, $tipoVenta, $idVendedor, $idAlmacen);
            $resumenData = calcularResumenMensual($datos, $anio);
            break;
        case 'comparativo':
            $datos = generarReporteComparativo($anio, $anioComparativo, $tipoVenta, $idVendedor, $idAlmacen);
            $resumenData = calcularResumenComparativo($datos, $anio, $anioComparativo);
            break;
        default:
            $datos = generarReporteMensual($anio, $tipoVenta, $idVendedor, $idAlmacen);
            $resumenData = calcularResumenMensual($datos, $anio);
    }

    if ($datos && count($datos) > 0) {
        echo json_encode([
            'success' => true,
            'data' => $datos,
            'resumen' => $resumenData,
            'total_registros' => count($datos)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [],
            'resumen' => $resumenData,
            'message' => 'No se encontraron datos con los criterios seleccionados'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en reporte ventas anuales/mensuales: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}

function generarReporteMensual($anio, $tipoVenta, $idVendedor, $idAlmacen)
{
    global $clsConsulta;

    $sql = "
        SELECT 
            MONTH(cr.fecha) AS mes_numero,
            DATE_FORMAT(cr.fecha, '%M') AS periodo,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE YEAR(cr2.fecha) = ? AND MONTH(cr2.fecha) = MONTH(cr.fecha)) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            YEAR(cr.fecha) = ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$anio, $anio];
    $types = "ii";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " GROUP BY MONTH(cr.fecha), periodo ORDER BY mes_numero";

    return $clsConsulta->consultaPreparada($sql, $params, $types);
}

function generarReporteAnual($anio, $tipoVenta, $idVendedor, $idAlmacen)
{
    global $clsConsulta;

    $sql = "
        SELECT 
            YEAR(cr.fecha) AS periodo,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE YEAR(cr2.fecha) = YEAR(cr.fecha)) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            YEAR(cr.fecha) BETWEEN ? - 4 AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$anio, $anio];
    $types = "ii";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " GROUP BY YEAR(cr.fecha) ORDER BY periodo DESC";

    return $clsConsulta->consultaPreparada($sql, $params, $types);
}

function generarReporteComparativo($anio, $anioComparativo, $tipoVenta, $idVendedor, $idAlmacen)
{
    global $clsConsulta;

    $sql = "
        SELECT 
            MONTH(cr.fecha) AS mes_numero,
            DATE_FORMAT(cr.fecha, '%M') AS periodo,
            YEAR(cr.fecha) AS anio,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE YEAR(cr2.fecha) = YEAR(cr.fecha) AND MONTH(cr2.fecha) = MONTH(cr.fecha)) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            YEAR(cr.fecha) IN (?, ?)
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$anio, $anioComparativo];
    $types = "ii";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " GROUP BY YEAR(cr.fecha), MONTH(cr.fecha), periodo ORDER BY mes_numero, anio";

    return $clsConsulta->consultaPreparada($sql, $params, $types);
}

function calcularResumenMensual($datos, $anio)
{
    global $clsConsulta;

    $ventasTotales = 0;
    $cantidadVentas = 0;
    $totalProductos = 0;
    $mejorMes = '';
    $mejorMesVentas = 0;

    foreach ($datos as $mes) {
        $ventas = floatval($mes['total_ventas']);
        $ventasTotales += $ventas;
        $cantidadVentas += intval($mes['cantidad_ventas']);
        $totalProductos += intval($mes['total_productos']);

        if ($ventas > $mejorMesVentas) {
            $mejorMesVentas = $ventas;
            $mejorMes = $mes['periodo'];
        }
    }

    // Calcular crecimiento vs año anterior
    $sqlAnterior = "
        SELECT COALESCE(SUM(total), 0) AS ventas_anterior 
        FROM cab_remisiones 
        WHERE YEAR(fecha) = ? - 1 
        AND estatus IN ('procesada', 'pendiente')
    ";
    $ventasAnterior = $clsConsulta->consultaPreparada($sqlAnterior, [$anio - 1], "i");
    $ventasAnteriorValor = $ventasAnterior && count($ventasAnterior) > 0 ? floatval($ventasAnterior[0]['ventas_anterior']) : 0;

    $crecimiento = $ventasAnteriorValor > 0 ? (($ventasTotales - $ventasAnteriorValor) / $ventasAnteriorValor) * 100 : 0;

    // Ventas por tipo
    $sqlTipoVenta = "
        SELECT 
            COALESCE(SUM(CASE WHEN tipo_venta = 'contado' THEN total ELSE 0 END), 0) AS ventas_contado,
            COALESCE(SUM(CASE WHEN tipo_venta = 'credito' THEN total ELSE 0 END), 0) AS ventas_credito
        FROM cab_remisiones 
        WHERE YEAR(fecha) = ? 
        AND estatus IN ('procesada', 'pendiente')
    ";
    $tipoVentaData = $clsConsulta->consultaPreparada($sqlTipoVenta, [$anio], "i");
    $tipoVenta = $tipoVentaData && count($tipoVentaData) > 0 ? $tipoVentaData[0] : ['ventas_contado' => 0, 'ventas_credito' => 0];

    return [
        'ventas_totales' => $ventasTotales,
        'cantidad_ventas' => $cantidadVentas,
        'total_productos' => $totalProductos,
        'crecimiento_anual' => $crecimiento,
        'mejor_mes' => $mejorMes,
        'promedio_mensual' => count($datos) > 0 ? $ventasTotales / count($datos) : 0,
        'ventas_contado' => $tipoVenta['ventas_contado'],
        'ventas_credito' => $tipoVenta['ventas_credito']
    ];
}

function calcularResumenAnual($datos, $anio)
{
    // Similar a calcularResumenMensual pero adaptado para años
    $ventasTotales = 0;
    $cantidadVentas = 0;
    $totalProductos = 0;
    $mejorAnio = '';
    $mejorAnioVentas = 0;

    foreach ($datos as $anioData) {
        $ventas = floatval($anioData['total_ventas']);
        $ventasTotales += $ventas;
        $cantidadVentas += intval($anioData['cantidad_ventas']);
        $totalProductos += intval($anioData['total_productos']);

        if ($ventas > $mejorAnioVentas) {
            $mejorAnioVentas = $ventas;
            $mejorAnio = $anioData['periodo'];
        }
    }

    return [
        'ventas_totales' => $ventasTotales,
        'cantidad_ventas' => $cantidadVentas,
        'total_productos' => $totalProductos,
        'crecimiento_anual' => 0, // No aplicable para reporte anual
        'mejor_mes' => $mejorAnio,
        'promedio_mensual' => $ventasTotales / 12, // Promedio mensual estimado
        'ventas_contado' => 0,
        'ventas_credito' => 0
    ];
}

function calcularResumenComparativo($datos, $anio, $anioComparativo)
{
    // Resumen para reporte comparativo
    $ventasAnioActual = 0;
    $ventasAnioComparativo = 0;

    foreach ($datos as $mes) {
        if ($mes['anio'] == $anio) {
            $ventasAnioActual += floatval($mes['total_ventas']);
        } else {
            $ventasAnioComparativo += floatval($mes['total_ventas']);
        }
    }

    $crecimiento = $ventasAnioComparativo > 0 ? (($ventasAnioActual - $ventasAnioComparativo) / $ventasAnioComparativo) * 100 : 0;

    return [
        'ventas_totales' => $ventasAnioActual,
        'cantidad_ventas' => 0,
        'total_productos' => 0,
        'crecimiento_anual' => $crecimiento,
        'mejor_mes' => '-',
        'promedio_mensual' => $ventasAnioActual / 12,
        'ventas_contado' => 0,
        'ventas_credito' => 0
    ];
}
