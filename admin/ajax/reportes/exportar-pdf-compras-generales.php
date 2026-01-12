<?php
// ajax/reportes/exportar-pdf-compras-generales.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$agrupacion = $_GET['agrupacion'] ?? 'mensual';
$estatus = $_GET['estatus'] ?? '';

$clsConsulta = new Consultas();

try {
    $formatoFecha = '';
    $formatoMostrar = '';

    switch ($agrupacion) {
        case 'diario':
            $formatoFecha = '%Y-%m-%d';
            $formatoMostrar = '%d/%m/%Y';
            break;
        case 'semanal':
            $formatoFecha = '%Y-%u';
            $formatoMostrar = 'Semana %u de %Y';
            break;
        case 'mensual':
            $formatoFecha = '%Y-%m';
            $formatoMostrar = '%M %Y';
            break;
        case 'anual':
            $formatoFecha = '%Y';
            $formatoMostrar = '%Y';
            break;
        default:
            $formatoFecha = '%Y-%m';
            $formatoMostrar = '%M %Y';
    }

    $sql = "
        SELECT 
            DATE_FORMAT(c.fecha, ?) as periodo_fecha,
            DATE_FORMAT(c.fecha, ?) as periodo,
            COUNT(DISTINCT c.id) as cantidad_compras,
            COALESCE(SUM(c.total), 0) as total_compras,
            COALESCE(MIN(c.total), 0) as compra_minima,
            COALESCE(MAX(c.total), 0) as compra_maxima,
            GROUP_CONCAT(DISTINCT p.id) as proveedores_unicos,
            COUNT(DISTINCT p.id) as proveedores_unicos_count
        FROM 
            cab_compras c
        LEFT JOIN 
            cat_proveedores p ON c.id_proveedor = p.id
        WHERE 
            c.fecha BETWEEN ? AND ?
    ";

    $params = [$formatoFecha, $formatoMostrar, $fechaInicio, $fechaFin];
    $types = "ssss";

    if (!empty($estatus)) {
        $sql .= " AND c.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    } else {
        $sql .= " AND c.estatus IN ('procesada', 'pendiente')";
    }

    $sql .= " 
        GROUP BY 
            periodo_fecha, periodo
        ORDER BY 
            periodo_fecha ASC
    ";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    $sqlEmpresa = "SELECT razon_social, nombre_comercial, rfc, calle, num_ext, num_int, colonia, cp 
                   FROM cat_empresas 
                   WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

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

    $logoPath = '../../img/logo-inicio.png';
    $logoBase64 = '';

    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }

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

    $mpdf->SetHTMLFooter('
    <div style="text-align: center; border-top: 1px solid #ccc; padding-top: 5px; font-size: 10px; color: #666;">
        Página {PAGENO} de {nbpg} | Sistema ERP - Reporte de Compras Generales
    </div>
    ');

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Compras Generales por Periodo</title>
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
                border-left: 4px solid #27ae60;
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
                background-color: #27ae60; 
                color: white; 
                border: 1px solid #219653; 
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
                background-color: #d5f4e6; 
                font-weight: bold; 
                border-top: 2px solid #27ae60;
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
            Reporte de Compras Generales por Periodo
        </div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Agrupación: ' . ucfirst($agrupacion) . '<br>
            • Estatus: ' . ($estatus ? ucfirst($estatus) : 'Todos') . '
        </div>

        <table>
            <thead>
                <tr>
                    <th width="20%">Periodo</th>
                    <th width="15%" class="text-right">Total Compras</th>
                    <th width="12%" class="text-center">Cantidad Compras</th>
                    <th width="13%" class="text-right">Promedio</th>
                    <th width="12%" class="text-right">Mínima</th>
                    <th width="12%" class="text-right">Máxima</th>
                    <th width="16%" class="text-center">Proveedores Únicos</th>
                </tr>
            </thead>
            <tbody>';

    $totalCompras = 0;
    $totalCantidad = 0;
    $compraMinima = PHP_FLOAT_MAX;
    $compraMaxima = 0;
    $totalProveedores = [];

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $promedio = $item['cantidad_compras'] > 0 ? $item['total_compras'] / $item['cantidad_compras'] : 0;

            $totalCompras += $item['total_compras'];
            $totalCantidad += $item['cantidad_compras'];

            if ($item['compra_minima'] < $compraMinima) $compraMinima = $item['compra_minima'];
            if ($item['compra_maxima'] > $compraMaxima) $compraMaxima = $item['compra_maxima'];

            if ($item['proveedores_unicos']) {
                $proveedores = explode(',', $item['proveedores_unicos']);
                foreach ($proveedores as $prov) {
                    $totalProveedores[$prov] = true;
                }
            }

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['periodo']) . '</td>
                    <td class="text-right">$' . number_format($item['total_compras'], 2) . '</td>
                    <td class="text-center">' . $item['cantidad_compras'] . '</td>
                    <td class="text-right">$' . number_format($promedio, 2) . '</td>
                    <td class="text-right">$' . number_format($item['compra_minima'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['compra_maxima'], 2) . '</td>
                    <td class="text-center">' . $item['proveedores_unicos_count'] . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" class="no-data">No se encontraron datos con los filtros seleccionados</td></tr>';
    }

    $promedioGeneral = $totalCantidad > 0 ? $totalCompras / $totalCantidad : 0;
    $totalProveedoresCount = count($totalProveedores);

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTALES GENERALES</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalCompras, 2) . '</strong></td>
                    <td class="text-center"><strong>' . $totalCantidad . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($promedioGeneral, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($compraMinima !== PHP_FLOAT_MAX ? $compraMinima : 0, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($compraMaxima, 2) . '</strong></td>
                    <td class="text-center"><strong>' . $totalProveedoresCount . '</strong></td>
                </tr>
            </tfoot>
        </table>';

    if ($datos && count($datos) > 0) {
        $html .= '
        <div class="summary">
            <strong>Resumen Ejecutivo:</strong><br>
            • Períodos Analizados: ' . count($datos) . '<br>
            • Compras Totales: $' . number_format($totalCompras, 2) . '<br>
            • Transacciones Totales: ' . $totalCantidad . '<br>
            • Compra Promedio: $' . number_format($promedioGeneral, 2) . '<br>
            • Proveedores Únicos: ' . $totalProveedoresCount . '<br>
            • Rango de Compras: $' . number_format($compraMinima, 2) . ' - $' . number_format($compraMaxima, 2) . '
        </div>';
    }

    $html .= '
    </body>
    </html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('reporte_compras_generales_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
