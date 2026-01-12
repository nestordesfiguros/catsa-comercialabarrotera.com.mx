<?php
// ajax/reportes/exportar-pdf-ventas-vendedor.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$idVendedor = $_GET['id_vendedor'] ?? 0;
$estatus = $_GET['estatus'] ?? '';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del endpoint AJAX
    $sql = "
        SELECT 
            v.id AS id_vendedor,
            CONCAT(v.nombre, ' ', v.apellido1, ' ', COALESCE(v.apellido2, '')) AS nombre_vendedor,
            v.comision AS porcentaje_comision,
            COUNT(DISTINCT r.id) AS cantidad_ventas,
            COALESCE(SUM(r.total), 0) AS total_ventas,
            COALESCE(MIN(r.total), 0) AS venta_minima,
            COALESCE(MAX(r.total), 0) AS venta_maxima
        FROM 
            cat_vendedores v
        LEFT JOIN 
            cab_remisiones r ON v.id = r.id_vendedor 
            AND r.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idVendedor > 0) {
        $sql .= " AND v.id = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND r.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    } else {
        $sql .= " AND r.estatus IN ('procesada', 'pendiente')";
    }

    $sql .= " 
        GROUP BY 
            v.id, v.nombre, v.apellido1, v.apellido2, v.comision
        HAVING 
            cantidad_ventas > 0 OR ? = 1
        ORDER BY 
            total_ventas DESC
    ";

    $params[] = ($idVendedor > 0) ? 0 : 1;
    $types .= "i";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial, rfc, calle, num_ext, num_int, colonia, cp 
                   FROM cat_empresas 
                   WHERE id = 1 LIMIT 1";
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

    // Logo de la empresa (ruta corregida)
    $logoPath = '../../img/logo-inicio.png';
    $logoBase64 = '';

    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    } else {
        // Log para debugging
        error_log("Logo no encontrado en: " . realpath($logoPath));
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
        Página {PAGENO} de {nbpg} | Sistema ERP - Reporte de Ventas por Vendedor
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Ventas por Vendedor</title>
        <style>
            body { 
                font-family: DejaVu Sans, sans-serif; 
                font-size: 11px; 
                line-height: 1.3;
            }
            .report-title { 
                text-align: center; 
                font-size: 16px; 
                font-weight: bold; 
                margin-bottom: 15px;
                color: #2c3e50;
            }
            .filters { 
                margin-bottom: 15px; 
                padding: 10px; 
                background-color: #f8f9fa; 
                border-radius: 5px;
                border-left: 4px solid #3498db;
            }
            .filters strong { 
                color: #2c3e50; 
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 10px;
                font-size: 10px;
            }
            th { 
                background-color: #34495e; 
                color: white; 
                border: 1px solid #2c3e50; 
                padding: 6px; 
                text-align: left; 
                font-weight: bold;
                font-size: 9px;
            }
            td { 
                border: 1px solid #bdc3c7; 
                padding: 6px; 
                font-size: 9px;
            }
            .text-right { 
                text-align: right; 
            }
            .text-center { 
                text-align: center; 
            }
            .total-row { 
                background-color: #ecf0f1; 
                font-weight: bold; 
                border-top: 2px solid #7f8c8d;
            }
            .no-data { 
                text-align: center; 
                color: #7f8c8d; 
                font-style: italic;
                padding: 20px;
            }
            .summary { 
                margin-top: 15px;
                padding: 10px;
                background-color: #f8f9fa;
                border-radius: 5px;
                border-left: 4px solid #27ae60;
            }
        </style>
    </head>
    <body>
        <div class="report-title">
            Reporte de Ventas por Vendedor
        </div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Estatus: ' . ($estatus ? ucfirst($estatus) : 'Todos') . '
        </div>

        <table>
            <thead>
                <tr>
                    <th width="25%">Vendedor</th>
                    <th width="15%" class="text-right">Total Ventas</th>
                    <th width="12%" class="text-center">Cantidad Ventas</th>
                    <th width="12%" class="text-right">Promedio</th>
                    <th width="12%" class="text-right">Venta Mínima</th>
                    <th width="12%" class="text-right">Venta Máxima</th>
                    <th width="12%" class="text-right">Comisión</th>
                </tr>
            </thead>
            <tbody>';

    $totalVentas = 0;
    $totalCantidad = 0;
    $totalComision = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $promedioVenta = $item['cantidad_ventas'] > 0 ? $item['total_ventas'] / $item['cantidad_ventas'] : 0;
            $comisionTotal = $item['total_ventas'] * ($item['porcentaje_comision'] / 100);

            $totalVentas += $item['total_ventas'];
            $totalCantidad += $item['cantidad_ventas'];
            $totalComision += $comisionTotal;

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['nombre_vendedor']) . '</td>
                    <td class="text-right">$' . number_format($item['total_ventas'], 2) . '</td>
                    <td class="text-center">' . $item['cantidad_ventas'] . '</td>
                    <td class="text-right">$' . number_format($promedioVenta, 2) . '</td>
                    <td class="text-right">$' . number_format($item['venta_minima'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['venta_maxima'], 2) . '</td>
                    <td class="text-right">$' . number_format($comisionTotal, 2) . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" class="no-data">No se encontraron datos con los filtros seleccionados</td></tr>';
    }

    $promedioGeneral = $totalCantidad > 0 ? $totalVentas / $totalCantidad : 0;

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTALES GENERALES</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalVentas, 2) . '</strong></td>
                    <td class="text-center"><strong>' . $totalCantidad . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($promedioGeneral, 2) . '</strong></td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right"><strong>$' . number_format($totalComision, 2) . '</strong></td>
                </tr>
            </tfoot>
        </table>';

    // Resumen ejecutivo
    if ($datos && count($datos) > 0) {
        $html .= '
        <div class="summary">
            <strong>Resumen Ejecutivo:</strong><br>
            • Total de Vendedores: ' . count($datos) . '<br>
            • Ventas Totales: $' . number_format($totalVentas, 2) . '<br>
            • Transacciones Totales: ' . $totalCantidad . '<br>
            • Comisiones Totales: $' . number_format($totalComision, 2) . '<br>
            • Ticket Promedio: $' . number_format($promedioGeneral, 2) . '
        </div>';
    }

    $html .= '
    </body>
    </html>';

    $mpdf->WriteHTML($html);

    // Descargar PDF
    $mpdf->Output('reporte_ventas_vendedor_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
