<?php
// ajax/reportes/exportar-pdf-detalle-facturas.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$estatusFactura = $_GET['estatus_factura'] ?? '';
$timbrada = $_GET['timbrada'] ?? '';
$idCliente = $_GET['id_cliente'] ?? 0;
$serie = $_GET['serie'] ?? '';
$folio = $_GET['folio'] ?? '';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del endpoint AJAX
    $sql = "
        SELECT 
            cf.id,
            cf.serie,
            cf.folio,
            cf.fecha,
            cf.subtotal,
            cf.total,
            cf.estatus,
            cf.timbrada,
            cf.uuid,
            cc.razon_social,
            cc.rfc
        FROM 
            cab_facturas cf
        LEFT JOIN 
            cat_clientes cc ON cf.id_receptor = cc.id
        WHERE 
            cf.fecha BETWEEN ? AND ?
            AND cf.activo = 1
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if (!empty($estatusFactura)) {
        $sql .= " AND cf.estatus = ?";
        $params[] = $estatusFactura;
        $types .= "s";
    }

    if ($timbrada !== '') {
        $sql .= " AND cf.timbrada = ?";
        $params[] = $timbrada;
        $types .= "i";
    }

    if ($idCliente > 0) {
        $sql .= " AND cf.id_receptor = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if (!empty($serie)) {
        $sql .= " AND cf.serie LIKE ?";
        $params[] = "%$serie%";
        $types .= "s";
    }

    if (!empty($folio)) {
        $sql .= " AND cf.folio LIKE ?";
        $params[] = "%$folio%";
        $types .= "s";
    }

    $sql .= " ORDER BY cf.fecha DESC, cf.id DESC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen
    $sqlResumen = "
        SELECT 
            COUNT(*) AS cantidad_facturas,
            COALESCE(SUM(cf.total), 0) AS total_facturado,
            COALESCE(AVG(cf.total), 0) AS promedio_factura,
            SUM(CASE WHEN cf.timbrada = 1 THEN 1 ELSE 0 END) AS facturas_timbradas
        FROM 
            cab_facturas cf
        WHERE 
            cf.fecha BETWEEN ? AND ?
            AND cf.activo = 1
    ";

    $paramsResumen = [$fechaInicio, $fechaFin];
    $typesResumen = "ss";

    if (!empty($estatusFactura)) {
        $sqlResumen .= " AND cf.estatus = ?";
        $paramsResumen[] = $estatusFactura;
        $typesResumen .= "s";
    }

    if ($timbrada !== '') {
        $sqlResumen .= " AND cf.timbrada = ?";
        $paramsResumen[] = $timbrada;
        $typesResumen .= "i";
    }

    if ($idCliente > 0) {
        $sqlResumen .= " AND cf.id_receptor = ?";
        $paramsResumen[] = $idCliente;
        $typesResumen .= "i";
    }

    if (!empty($serie)) {
        $sqlResumen .= " AND cf.serie LIKE ?";
        $paramsResumen[] = "%$serie%";
        $typesResumen .= "s";
    }

    if (!empty($folio)) {
        $sqlResumen .= " AND cf.folio LIKE ?";
        $paramsResumen[] = "%$folio%";
        $typesResumen .= "s";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : [
        'cantidad_facturas' => 0,
        'total_facturado' => 0,
        'promedio_factura' => 0,
        'facturas_timbradas' => 0
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
        Página {PAGENO} de {nbpg} | Sistema ERP - Detallado de Facturas
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Detallado de Facturas</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.3; }
            .report-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #2c3e50; }
            .filters { margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #3498db; }
            .resumen { margin-bottom: 15px; padding: 10px; background-color: #e8f4fd; border-radius: 5px; border-left: 4px solid #2980b9; }
            .resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
            .resumen-item { text-align: center; padding: 8px; background: white; border-radius: 5px; border: 1px solid #bdc3c7; }
            .resumen-valor { font-size: 12px; font-weight: bold; color: #2c3e50; }
            .resumen-label { font-size: 8px; color: #7f8c8d; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 8px; }
            th { background-color: #3498db; color: white; border: 1px solid #2980b9; padding: 5px; text-align: left; font-weight: bold; }
            td { border: 1px solid #bdc3c7; padding: 5px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { background-color: #d6eaf8; font-weight: bold; border-top: 2px solid #3498db; }
            .badge { padding: 2px 6px; border-radius: 3px; font-size: 7px; }
            .badge-success { background-color: #27ae60; color: white; }
            .badge-warning { background-color: #f39c12; color: black; }
            .badge-danger { background-color: #e74c3c; color: white; }
            .badge-secondary { background-color: #95a5a6; color: white; }
        </style>
    </head>
    <body>
        <div class="report-title">Detallado de Facturas</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Estatus: ' . ($estatusFactura ? ucfirst(strtolower($estatusFactura)) : 'Todos') . '<br>
            • Timbrado: ' . ($timbrada !== '' ? ($timbrada ? 'Sí' : 'No') : 'Todos') . '
        </div>

        <div class="resumen">
            <strong>Resumen General:</strong>
            <div class="resumen-grid">
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($resumenData['total_facturado'], 2) . '</div>
                    <div class="resumen-label">Total Facturado</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($resumenData['cantidad_facturas']) . '</div>
                    <div class="resumen-label">Cantidad Facturas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($resumenData['promedio_factura'], 2) . '</div>
                    <div class="resumen-label">Promedio por Factura</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($resumenData['facturas_timbradas']) . '</div>
                    <div class="resumen-label">Facturas Timbradas</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="10%">Folio</th>
                    <th width="8%">Fecha</th>
                    <th width="25%">Cliente</th>
                    <th width="12%">RFC</th>
                    <th width="10%" class="text-right">Subtotal</th>
                    <th width="10%" class="text-right">IVA</th>
                    <th width="10%" class="text-right">Total</th>
                    <th width="8%" class="text-center">Estatus</th>
                    <th width="7%" class="text-center">Timbrado</th>
                </tr>
            </thead>
            <tbody>';

    $totalSubtotal = 0;
    $totalIva = 0;
    $totalGeneral = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $iva = floatval($item['total']) - floatval($item['subtotal']);
            $folioCompleto = $item['serie'] && $item['folio'] ? $item['serie'] . '-' . $item['folio'] : 'N/A';
            $fechaFormateada = $item['fecha'] ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A';

            $estatusClass = '';
            $estatusText = '';
            switch ($item['estatus']) {
                case 'PAGADA':
                    $estatusClass = 'badge-success';
                    $estatusText = 'Pagada';
                    break;
                case 'PENDIEENTE':
                    $estatusClass = 'badge-warning';
                    $estatusText = 'Pendiente';
                    break;
                case 'CANCELADA':
                    $estatusClass = 'badge-danger';
                    $estatusText = 'Cancelada';
                    break;
                default:
                    $estatusClass = 'badge-secondary';
                    $estatusText = $item['estatus'];
            }

            $timbradoClass = $item['timbrada'] ? 'badge-success' : 'badge-warning';
            $timbradoText = $item['timbrada'] ? 'Sí' : 'No';

            $totalSubtotal += floatval($item['subtotal']);
            $totalIva += $iva;
            $totalGeneral += floatval($item['total']);

            $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($folioCompleto) . '</strong></td>
                    <td>' . $fechaFormateada . '</td>
                    <td>' . htmlspecialchars($item['razon_social'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($item['rfc'] ?? 'N/A') . '</td>
                    <td class="text-right">$' . number_format($item['subtotal'], 2) . '</td>
                    <td class="text-right">$' . number_format($iva, 2) . '</td>
                    <td class="text-right">$' . number_format($item['total'], 2) . '</td>
                    <td class="text-center"><span class="badge ' . $estatusClass . '">' . $estatusText . '</span></td>
                    <td class="text-center"><span class="badge ' . $timbradoClass . '">' . $timbradoText . '</span></td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="9" class="text-center">No se encontraron facturas</td></tr>';
    }

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4"><strong>TOTALES</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalSubtotal, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalIva, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalGeneral, 2) . '</strong></td>
                    <td colspan="2">-</td>
                </tr>
            </tfoot>
        </table>';

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('detalle_facturas_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
