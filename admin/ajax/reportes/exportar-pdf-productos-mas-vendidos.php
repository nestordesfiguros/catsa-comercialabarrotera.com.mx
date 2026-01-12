<?php
// ajax/reportes/exportar-pdf-productos-mas-vendidos.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$idAlmacen = $_GET['id_almacen'] ?? 0;
$idCategoria = $_GET['id_categoria'] ?? 0;
$tipoVenta = $_GET['tipo_venta'] ?? '';
$topN = $_GET['top_n'] ?? 10;
$ordenarPor = $_GET['ordenar_por'] ?? 'cantidad';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del DataTable
    $sql = "
        SELECT 
            cp.id_producto,
            cp.clave,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            SUM(mr.cantidad) AS cantidad_vendida,
            SUM(mr.cantidad * mr.precio) AS ventas_totales,
            SUM(mr.cantidad * cp.precio_compra) AS costo_total,
            SUM(mr.cantidad * mr.precio) - SUM(mr.cantidad * cp.precio_compra) AS utilidad,
            CASE 
                WHEN SUM(mr.cantidad * mr.precio) > 0 THEN 
                    ((SUM(mr.cantidad * mr.precio) - SUM(mr.cantidad * cp.precio_compra)) / SUM(mr.cantidad * mr.precio)) * 100 
                ELSE 0 
            END AS margen,
            (SUM(mr.cantidad * mr.precio) / (SELECT SUM(mr2.cantidad * mr2.precio) 
                                           FROM mov_remisiones mr2 
                                           INNER JOIN cab_remisiones cr2 ON mr2.id_remision = cr2.id 
                                           WHERE cr2.fecha BETWEEN ? AND ?)) * 100 AS porcentaje_total
        FROM 
            mov_remisiones mr
        INNER JOIN 
            cab_remisiones cr ON mr.id_remision = cr.id
        INNER JOIN 
            cat_productos cp ON mr.id_producto = cp.id_producto
        LEFT JOIN 
            cat_categorias cc ON cp.id_categoria = cc.id_categoria
        LEFT JOIN 
            cat_almacenes ca ON cr.id_almacen = ca.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin];
    $types = "ssss";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND cp.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    $sql .= " GROUP BY cp.id_producto, cp.clave, cp.nombre, cc.nombre_categoria";

    // Ordenamiento
    switch ($ordenarPor) {
        case 'ventas':
            $orderColumn = 'ventas_totales';
            break;
        case 'utilidad':
            $orderColumn = 'utilidad';
            break;
        case 'margen':
            $orderColumn = 'margen';
            break;
        default:
            $orderColumn = 'cantidad_vendida';
    }

    $sql .= " ORDER BY $orderColumn DESC";

    // Limitar resultados si se solicita un top N
    if ($topN > 0) {
        $sql .= " LIMIT ?";
        $params[] = $topN;
        $types .= "i";
    }

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Calcular totales
    $totalVentas = 0;
    $totalProductosVendidos = 0;
    $totalUtilidad = 0;
    $totalCosto = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $totalVentas += floatval($item['ventas_totales']);
            $totalProductosVendidos += intval($item['cantidad_vendida']);
            $totalUtilidad += floatval($item['utilidad']);
            $totalCosto += floatval($item['costo_total']);
        }
    }

    $margenPromedio = $totalVentas > 0 ? ($totalUtilidad / $totalVentas) * 100 : 0;

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial FROM cat_empresas WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Configurar MPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'orientation' => 'L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 30,
        'margin_bottom' => 20,
        'margin_header' => 5,
        'margin_footer' => 10
    ]);

    // Logo de la empresa
    $logoPath = '../../img/logo-inicio.png';
    $logoBase64 = '';

    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }

    // Header personalizado
    $mpdf->SetHTMLHeader('
    <div style="text-align: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">
        <table width="100%" style="border: none;">
            <tr>
                <td width="20%" style="text-align: left; vertical-align: middle;">
                    ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" style="height: 60px;" />' : '') . '
                </td>
                <td width="60%" style="text-align: center; vertical-align: middle;">
                    <div style="font-size: 16px; font-weight: bold;">' . ($empresaInfo ? htmlspecialchars($empresaInfo['nombre_comercial']) : 'Sistema ERP') . '</div>
                    <div style="font-size: 12px; color: #666;">' . ($empresaInfo ? htmlspecialchars($empresaInfo['razon_social']) : '') . '</div>
                </td>
                <td width="20%" style="text-align: right; vertical-align: middle; font-size: 10px;">
                    ' . date('d/m/Y H:i:s') . '
                </td>
            </tr>
        </table>
    </div>
    ');

    // Footer personalizado
    $mpdf->SetHTMLFooter('
    <div style="text-align: center; border-top: 1px solid #ccc; padding-top: 5px; font-size: 10px; color: #666;">
        Página {PAGENO} de {nbpg} | Sistema ERP - Productos Más Vendidos
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Productos Más Vendidos</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 7px; line-height: 1.1; }
            .report-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #2c3e50; }
            .filters { margin-bottom: 8px; padding: 6px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #dc3545; font-size: 6px; }
            .resumen { margin-bottom: 8px; padding: 6px; background-color: #e8f4fd; border-radius: 5px; border-left: 4px solid #17a2b8; }
            .resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
            .resumen-item { text-align: center; padding: 4px; background: white; border-radius: 5px; border: 1px solid #bdc3c7; }
            .resumen-valor { font-size: 9px; font-weight: bold; color: #2c3e50; }
            .resumen-label { font-size: 6px; color: #7f8c8d; }
            table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 6px; }
            th { background-color: #dc3545; color: white; border: 1px solid #c82333; padding: 3px; text-align: left; font-weight: bold; }
            td { border: 1px solid #bdc3c7; padding: 3px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { background-color: #d1ecf1; font-weight: bold; border-top: 2px solid #17a2b8; }
            .ranking-1 { background-color: #ffd700; font-weight: bold; }
            .ranking-2 { background-color: #c0c0c0; }
            .ranking-3 { background-color: #cd7f32; }
        </style>
    </head>
    <body>
        <div class="report-title">Productos Más Vendidos - Ranking</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Almacén: ' . ($idAlmacen ? 'Específico' : 'Todos') . '<br>
            • Categoría: ' . ($idCategoria ? 'Específica' : 'Todas') . '<br>
            • Tipo Venta: ' . ($tipoVenta ? ucfirst($tipoVenta) : 'Todos') . '<br>
            • Top: ' . ($topN > 0 ? $topN : 'Todos') . ' productos | Ordenado por: ' . ucfirst($ordenarPor) . '
        </div>

        <div class="resumen">
            <strong>Resumen del Periodo:</strong>
            <div class="resumen-grid">
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($totalVentas, 2) . '</div>
                    <div class="resumen-label">Total Ventas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($totalProductosVendidos) . '</div>
                    <div class="resumen-label">Productos Vendidos</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($totalUtilidad, 2) . '</div>
                    <div class="resumen-label">Utilidad Total</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($margenPromedio, 1) . '%</div>
                    <div class="resumen-label">Margen Promedio</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Producto</th>
                    <th width="15%">Categoría</th>
                    <th width="10%" class="text-center">Cantidad Vendida</th>
                    <th width="12%" class="text-right">Ventas Totales</th>
                    <th width="12%" class="text-right">Costo Total</th>
                    <th width="12%" class="text-right">Utilidad</th>
                    <th width="9%" class="text-center">Margen %</th>
                </tr>
            </thead>
            <tbody>';

    if ($datos && count($datos) > 0) {
        foreach ($datos as $index => $item) {
            $cantidadVendida = intval($item['cantidad_vendida']);
            $ventasTotales = floatval($item['ventas_totales']);
            $costoTotal = floatval($item['costo_total']);
            $utilidad = floatval($item['utilidad']);
            $margen = floatval($item['margen']);

            // Clase CSS para los primeros puestos
            $rankingClass = '';
            if ($index === 0) $rankingClass = 'ranking-1';
            elseif ($index === 1) $rankingClass = 'ranking-2';
            elseif ($index === 2) $rankingClass = 'ranking-3';

            $html .= '
                <tr class="' . $rankingClass . '">
                    <td class="text-center"><strong>' . ($index + 1) . '</strong></td>
                    <td>' . htmlspecialchars($item['clave'] . ' - ' . $item['producto']) . '</td>
                    <td>' . htmlspecialchars($item['categoria'] ?? 'Sin categoría') . '</td>
                    <td class="text-center">' . number_format($cantidadVendida) . '</td>
                    <td class="text-right">$' . number_format($ventasTotales, 2) . '</td>
                    <td class="text-right">$' . number_format($costoTotal, 2) . '</td>
                    <td class="text-right">$' . number_format($utilidad, 2) . '</td>
                    <td class="text-center">' . number_format($margen, 1) . '%</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="8" class="text-center">No se encontraron productos vendidos</td></tr>';
    }

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>TOTALES</strong></td>
                    <td class="text-center"><strong>' . number_format($totalProductosVendidos) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalVentas, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalCosto, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalUtilidad, 2) . '</strong></td>
                    <td class="text-center"><strong>' . number_format($margenPromedio, 1) . '%</strong></td>
                </tr>
            </tfoot>
        </table>';

    // Análisis por categoría
    if ($datos && count($datos) > 0) {
        $categorias = [];
        foreach ($datos as $item) {
            $categoria = $item['categoria'] ?? 'Sin categoría';
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [
                    'count' => 0,
                    'ventas' => 0,
                    'utilidad' => 0
                ];
            }
            $categorias[$categoria]['count']++;
            $categorias[$categoria]['ventas'] += floatval($item['ventas_totales']);
            $categorias[$categoria]['utilidad'] += floatval($item['utilidad']);
        }

        $html .= '
        <div style="margin-top: 10px; padding: 6px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #28a745;">
            <strong>Análisis por Categoría:</strong><br>';

        foreach ($categorias as $categoria => $data) {
            $porcentajeVentas = $totalVentas > 0 ? ($data['ventas'] / $totalVentas) * 100 : 0;
            $html .= '• ' . htmlspecialchars($categoria) . ': ' . $data['count'] . ' productos (' . number_format($porcentajeVentas, 1) . '% de ventas) - $' . number_format($data['utilidad'], 2) . ' utilidad<br>';
        }

        $html .= '</div>';
    }

    // Top 3 productos
    if ($datos && count($datos) >= 3) {
        $html .= '
        <div style="margin-top: 10px; padding: 6px; background-color: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">
            <strong>🏆 Top 3 Productos Destacados:</strong><br>
            <strong>🥇 1er Lugar:</strong> ' . htmlspecialchars($datos[0]['producto']) . ' - ' . number_format($datos[0]['cantidad_vendida']) . ' unidades - $' . number_format($datos[0]['ventas_totales'], 2) . '<br>
            <strong>🥈 2do Lugar:</strong> ' . htmlspecialchars($datos[1]['producto']) . ' - ' . number_format($datos[1]['cantidad_vendida']) . ' unidades - $' . number_format($datos[1]['ventas_totales'], 2) . '<br>
            <strong>🥉 3er Lugar:</strong> ' . htmlspecialchars($datos[2]['producto']) . ' - ' . number_format($datos[2]['cantidad_vendida']) . ' unidades - $' . number_format($datos[2]['ventas_totales'], 2) . '
        </div>';
    }

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('productos_mas_vendidos_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
