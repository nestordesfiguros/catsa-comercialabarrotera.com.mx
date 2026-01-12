<?php
// ajax/reportes/exportar-pdf-compras-proveedor.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$idProveedor = $_GET['id_proveedor'] ?? 0;
$estatus = $_GET['estatus'] ?? '';

$clsConsulta = new Consultas();

try {
    $sql = "
        SELECT 
            p.id AS id_proveedor,
            CONCAT(p.razon_social, ' - ', p.nombre_Comercial) AS nombre_proveedor,
            COUNT(DISTINCT c.id) AS cantidad_compras,
            COALESCE(SUM(c.total), 0) AS total_compras,
            COALESCE(MIN(c.total), 0) AS compra_minima,
            COALESCE(MAX(c.total), 0) AS compra_maxima,
            MAX(c.fecha) AS ultima_compra
        FROM 
            cat_proveedores p
        LEFT JOIN 
            cab_compras c ON p.id = c.id_proveedor 
            AND c.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idProveedor > 0) {
        $sql .= " AND p.id = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND c.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    } else {
        $sql .= " AND c.estatus IN ('procesada', 'pendiente')";
    }

    $sql .= " 
        GROUP BY 
            p.id, p.razon_social, p.nombre_Comercial
        HAVING 
            cantidad_compras > 0 OR ? = 1
        ORDER BY 
            total_compras DESC
    ";

    $params[] = ($idProveedor > 0) ? 0 : 1;
    $types .= "i";

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
        Página {PAGENO} de {nbpg} | Sistema ERP - Reporte de Compras por Proveedor
    </div>
    ');

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Compras por Proveedor</title>
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
            Reporte de Compras por Proveedor
        </div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Estatus: ' . ($estatus ? ucfirst($estatus) : 'Todos') . '
        </div>

        <table>
            <thead>
                <tr>
                    <th width="30%">Proveedor</th>
                    <th width="15%" class="text-right">Total Compras</th>
                    <th width="12%" class="text-center">Cantidad Compras</th>
                    <th width="13%" class="text-right">Promedio</th>
                    <th width="10%" class="text-right">Mínima</th>
                    <th width="10%" class="text-right">Máxima</th>
                    <th width="10%" class="text-center">Última Compra</th>
                </tr>
            </thead>
            <tbody>';

    $totalCompras = 0;
    $totalCantidad = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $promedioCompra = $item['cantidad_compras'] > 0 ? $item['total_compras'] / $item['cantidad_compras'] : 0;
            $ultimaCompra = $item['ultima_compra'] ? date('d/m/Y', strtotime($item['ultima_compra'])) : 'N/A';

            $totalCompras += $item['total_compras'];
            $totalCantidad += $item['cantidad_compras'];

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['nombre_proveedor']) . '</td>
                    <td class="text-right">$' . number_format($item['total_compras'], 2) . '</td>
                    <td class="text-center">' . $item['cantidad_compras'] . '</td>
                    <td class="text-right">$' . number_format($promedioCompra, 2) . '</td>
                    <td class="text-right">$' . number_format($item['compra_minima'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['compra_maxima'], 2) . '</td>
                    <td class="text-center">' . $ultimaCompra . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" class="no-data">No se encontraron datos con los filtros seleccionados</td></tr>';
    }

    $promedioGeneral = $totalCantidad > 0 ? $totalCompras / $totalCantidad : 0;

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTALES GENERALES</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalCompras, 2) . '</strong></td>
                    <td class="text-center"><strong>' . $totalCantidad . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($promedioGeneral, 2) . '</strong></td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-center">-</td>
                </tr>
            </tfoot>
        </table>';

    if ($datos && count($datos) > 0) {
        $html .= '
        <div class="summary">
            <strong>Resumen Ejecutivo:</strong><br>
            • Total de Proveedores: ' . count($datos) . '<br>
            • Compras Totales: $' . number_format($totalCompras, 2) . '<br>
            • Transacciones Totales: ' . $totalCantidad . '<br>
            • Compra Promedio: $' . number_format($promedioGeneral, 2) . '<br>
            • Período Analizado: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'N/A') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'N/A') . '
        </div>';
    }

    $html .= '
    </body>
    </html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('reporte_compras_proveedor_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
