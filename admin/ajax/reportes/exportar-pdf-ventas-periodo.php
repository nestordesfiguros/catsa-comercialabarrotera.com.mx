<?php
// ajax/reportes/exportar-pdf-ventas-periodo.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$tipoPeriodo = $_GET['tipo_periodo'] ?? 'mes';
$tipoVenta = $_GET['tipo_venta'] ?? '';
$idVendedor = $_GET['id_vendedor'] ?? 0;
$idAlmacen = $_GET['id_almacen'] ?? 0;
$estatus = $_GET['estatus'] ?? '';
$orden = $_GET['orden'] ?? 'periodo';

$clsConsulta = new Consultas();

try {
    // Determinar la agrupación por periodo
    $groupBy = '';
    $periodFormat = '';

    switch ($tipoPeriodo) {
        case 'dia':
            $groupBy = 'DATE(cr.fecha)';
            $periodFormat = "DATE_FORMAT(cr.fecha, '%d/%m/%Y')";
            break;
        case 'semana':
            $groupBy = 'YEARWEEK(cr.fecha)';
            $periodFormat = "CONCAT('Sem ', YEARWEEK(cr.fecha))";
            break;
        case 'mes':
            $groupBy = 'YEAR(cr.fecha), MONTH(cr.fecha)';
            $periodFormat = "DATE_FORMAT(cr.fecha, '%M %Y')";
            break;
        case 'anio':
            $groupBy = 'YEAR(cr.fecha)';
            $periodFormat = "YEAR(cr.fecha)";
            break;
        default:
            $groupBy = 'YEAR(cr.fecha), MONTH(cr.fecha)';
            $periodFormat = "DATE_FORMAT(cr.fecha, '%M %Y')";
    }

    // Reutilizar la misma consulta del endpoint AJAX
    $sql = "
        SELECT 
            $periodFormat AS periodo,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COALESCE(AVG(cr.total), 0) AS ticket_promedio,
            COALESCE(MIN(cr.total), 0) AS venta_minima,
            COALESCE(MAX(cr.total), 0) AS venta_maxima,
            (SELECT COALESCE(SUM(total), 0) FROM cab_remisiones WHERE fecha BETWEEN ? AND ?) AS total_general
        FROM 
            cab_remisiones cr
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin];
    $types = "ssss";

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

    if (!empty($estatus)) {
        $sql .= " AND cr.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    }

    $sql .= " GROUP BY $groupBy";

    $ordenValido = in_array($orden, ['periodo', 'total_ventas', 'cantidad_ventas']) ? $orden : 'periodo';
    $sql .= " ORDER BY $ordenValido " . ($ordenValido === 'periodo' ? 'ASC' : 'DESC');

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen general
    $sqlResumen = "
        SELECT 
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(AVG(cr.total), 0) AS ticket_promedio,
            COUNT(DISTINCT DATE(cr.fecha)) AS dias_con_ventas,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'contado' THEN cr.total ELSE 0 END), 0) AS ventas_contado,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'credito' THEN cr.total ELSE 0 END), 0) AS ventas_credito
        FROM 
            cab_remisiones cr
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $paramsResumen = [$fechaInicio, $fechaFin];
    $typesResumen = "ss";

    if (!empty($tipoVenta)) {
        $sqlResumen .= " AND cr.tipo_venta = ?";
        $paramsResumen[] = $tipoVenta;
        $typesResumen .= "s";
    }

    if ($idVendedor > 0) {
        $sqlResumen .= " AND cr.id_vendedor = ?";
        $paramsResumen[] = $idVendedor;
        $typesResumen .= "i";
    }

    if ($idAlmacen > 0) {
        $sqlResumen .= " AND cr.id_almacen = ?";
        $paramsResumen[] = $idAlmacen;
        $typesResumen .= "i";
    }

    if (!empty($estatus)) {
        $sqlResumen .= " AND cr.estatus = ?";
        $paramsResumen[] = $estatus;
        $typesResumen .= "s";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : [
        'total_ventas' => 0,
        'cantidad_ventas' => 0,
        'ticket_promedio' => 0,
        'dias_con_ventas' => 0,
        'ventas_contado' => 0,
        'ventas_credito' => 0
    ];

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
        Página {PAGENO} de {nbpg} | Sistema ERP - Reporte de Ventas Generales por Periodo
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Ventas Generales por Periodo</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.3; }
            .report-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #2c3e50; }
            .filters { margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #3498db; }
            .resumen { margin-bottom: 15px; padding: 10px; background-color: #e8f4fd; border-radius: 5px; border-left: 4px solid #2980b9; }
            .resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
            .resumen-item { text-align: center; padding: 10px; background: white; border-radius: 5px; border: 1px solid #bdc3c7; }
            .resumen-valor { font-size: 14px; font-weight: bold; color: #2c3e50; }
            .resumen-label { font-size: 10px; color: #7f8c8d; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9px; }
            th { background-color: #3498db; color: white; border: 1px solid #2980b9; padding: 6px; text-align: left; font-weight: bold; }
            td { border: 1px solid #bdc3c7; padding: 6px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { background-color: #d6eaf8; font-weight: bold; border-top: 2px solid #3498db; }
        </style>
    </head>
    <body>
        <div class="report-title">Reporte de Ventas Generales por Periodo</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Agrupación: ' . ucfirst($tipoPeriodo) . '<br>
            • Orden: ' . ucfirst(str_replace('_', ' ', $orden)) . '
        </div>

        <div class="resumen">
            <strong>Resumen General del Periodo:</strong>
            <div class="resumen-grid">
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($resumenData['total_ventas'], 2) . '</div>
                    <div class="resumen-label">Total Ventas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($resumenData['cantidad_ventas']) . '</div>
                    <div class="resumen-label">Cantidad Ventas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($resumenData['ticket_promedio'], 2) . '</div>
                    <div class="resumen-label">Ticket Promedio</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($resumenData['dias_con_ventas']) . '</div>
                    <div class="resumen-label">Días con Ventas</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="20%">Periodo</th>
                    <th width="12%" class="text-center">Cantidad Ventas</th>
                    <th width="15%" class="text-right">Total Ventas</th>
                    <th width="12%" class="text-right">Ticket Promedio</th>
                    <th width="12%" class="text-right">Venta Mínima</th>
                    <th width="12%" class="text-right">Venta Máxima</th>
                    <th width="12%" class="text-right">% del Total</th>
                    <th width="15%" class="text-center">Tipo Venta Predominante</th>
                </tr>
            </thead>
            <tbody>';

    $totalVentas = 0;
    $totalCantidad = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $porcentajeTotal = $resumenData['total_ventas'] > 0 ? ($item['total_ventas'] / $resumenData['total_ventas']) * 100 : 0;

            $totalVentas += $item['total_ventas'];
            $totalCantidad += $item['cantidad_ventas'];

            $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($item['periodo']) . '</strong></td>
                    <td class="text-center">' . number_format($item['cantidad_ventas']) . '</td>
                    <td class="text-right">$' . number_format($item['total_ventas'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['ticket_promedio'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['venta_minima'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['venta_maxima'], 2) . '</td>
                    <td class="text-right">' . number_format($porcentajeTotal, 1) . '%</td>
                    <td class="text-center">' . ($item['total_ventas'] > 0 ? 'Mixto' : 'Sin datos') . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="8" class="text-center">No se encontraron datos</td></tr>';
    }

    $ticketPromedioGeneral = $totalCantidad > 0 ? $totalVentas / $totalCantidad : 0;

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTALES</strong></td>
                    <td class="text-center"><strong>' . number_format($totalCantidad) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalVentas, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($ticketPromedioGeneral, 2) . '</strong></td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right"><strong>100%</strong></td>
                    <td class="text-center">-</td>
                </tr>
            </tfoot>
        </table>';

    // Distribución por tipo de venta
    if ($resumenData['total_ventas'] > 0) {
        $porcentajeContado = ($resumenData['ventas_contado'] / $resumenData['total_ventas']) * 100;
        $porcentajeCredito = ($resumenData['ventas_credito'] / $resumenData['total_ventas']) * 100;

        $html .= '
        <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #f39c12;">
            <strong>Distribución por Tipo de Venta:</strong><br>
            • Contado: $' . number_format($resumenData['ventas_contado'], 2) . ' (' . number_format($porcentajeContado, 1) . '%)<br>
            • Crédito: $' . number_format($resumenData['ventas_credito'], 2) . ' (' . number_format($porcentajeCredito, 1) . '%)
        </div>';
    }

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('reporte_ventas_generales_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
