<?php
// ajax/reportes/exportar-pdf-resumen-notas.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$tipoVenta = $_GET['tipo_venta'] ?? '';
$estatus = $_GET['estatus'] ?? '';
$idVendedor = $_GET['id_vendedor'] ?? 0;
$idCliente = $_GET['id_cliente'] ?? 0;
$idAlmacen = $_GET['id_almacen'] ?? 0;

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta corregida del endpoint AJAX
    $sql = "
        SELECT 
            cr.id,
            cr.fecha,
            cr.total,
            cr.estatus,
            cr.tipo_venta,
            cc.razon_social AS cliente,
            CONCAT(cv.nombre, ' ', cv.apellido1, ' ', COALESCE(cv.apellido2, '')) AS vendedor,
            ca.almacen,
            (SELECT COUNT(*) FROM mov_remisiones mr WHERE mr.id_remision = cr.id) AS total_productos,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr WHERE mr.id_remision = cr.id) AS cantidad_total
        FROM 
            cab_remisiones cr
        LEFT JOIN 
            cat_clientes cc ON cr.id_cliente = cc.id
        LEFT JOIN 
            cat_vendedores cv ON cr.id_vendedor = cv.id
        LEFT JOIN 
            cat_almacenes ca ON cr.id_almacen = ca.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if (!empty($estatus)) {
        $sql .= " AND cr.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idCliente > 0) {
        $sql .= " AND cr.id_cliente = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " ORDER BY cr.fecha DESC, cr.id DESC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen (también corregida)
    $sqlResumen = "
        SELECT 
            COUNT(*) AS cantidad_notas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COALESCE(AVG(cr.total), 0) AS ticket_promedio,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'contado' THEN cr.total ELSE 0 END), 0) AS ventas_contado,
            COALESCE(SUM(CASE WHEN cr.tipo_venta = 'credito' THEN cr.total ELSE 0 END), 0) AS ventas_credito,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE cr2.fecha BETWEEN ? AND ?) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            cr.fecha BETWEEN ? AND ?
    ";

    $paramsResumen = [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin];
    $typesResumen = "ssss";

    if (!empty($tipoVenta)) {
        $sqlResumen .= " AND cr.tipo_venta = ?";
        $paramsResumen[] = $tipoVenta;
        $typesResumen .= "s";
    }

    if (!empty($estatus)) {
        $sqlResumen .= " AND cr.estatus = ?";
        $paramsResumen[] = $estatus;
        $typesResumen .= "s";
    }

    if ($idVendedor > 0) {
        $sqlResumen .= " AND cr.id_vendedor = ?";
        $paramsResumen[] = $idVendedor;
        $typesResumen .= "i";
    }

    if ($idCliente > 0) {
        $sqlResumen .= " AND cr.id_cliente = ?";
        $paramsResumen[] = $idCliente;
        $typesResumen .= "i";
    }

    if ($idAlmacen > 0) {
        $sqlResumen .= " AND cr.id_almacen = ?";
        $paramsResumen[] = $idAlmacen;
        $typesResumen .= "i";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : [
        'cantidad_notas' => 0,
        'total_ventas' => 0,
        'ticket_promedio' => 0,
        'ventas_contado' => 0,
        'ventas_credito' => 0,
        'total_productos' => 0
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
        Página {PAGENO} de {nbpg} | Sistema ERP - Resumen de Notas de Venta
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Resumen de Notas de Venta</title>
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
        <div class="report-title">Resumen de Notas de Venta</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Tipo Venta: ' . ($tipoVenta ? ucfirst($tipoVenta) : 'Todos') . '<br>
            • Estatus: ' . ($estatus ? ucfirst($estatus) : 'Todos') . '
        </div>

        <div class="resumen">
            <strong>Resumen General:</strong>
            <div class="resumen-grid">
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($resumenData['total_ventas'], 2) . '</div>
                    <div class="resumen-label">Total Ventas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($resumenData['cantidad_notas']) . '</div>
                    <div class="resumen-label">Cantidad Notas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($resumenData['ticket_promedio'], 2) . '</div>
                    <div class="resumen-label">Ticket Promedio</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($resumenData['total_productos']) . '</div>
                    <div class="resumen-label">Productos Vendidos</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="6%">Folio</th>
                    <th width="8%">Fecha</th>
                    <th width="20%">Cliente</th>
                    <th width="15%">Vendedor</th>
                    <th width="15%">Almacén</th>
                    <th width="8%" class="text-center">Tipo Venta</th>
                    <th width="8%" class="text-center">Productos</th>
                    <th width="10%" class="text-right">Subtotal*</th>
                    <th width="10%" class="text-right">Total</th>
                    <th width="10%" class="text-center">Estatus</th>
                </tr>
            </thead>
            <tbody>';

    $totalGeneral = 0;
    $totalProductos = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $fechaFormateada = $item['fecha'] ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A';

            // Calcular subtotal estimado (sin IVA) - asumiendo 16% de IVA
            $subtotalEstimado = floatval($item['total']) / 1.16;

            $estatusClass = '';
            $estatusText = '';
            switch ($item['estatus']) {
                case 'procesada':
                    $estatusClass = 'badge-success';
                    $estatusText = 'Procesada';
                    break;
                case 'pendiente':
                    $estatusClass = 'badge-warning';
                    $estatusText = 'Pendiente';
                    break;
                case 'cancelada':
                    $estatusClass = 'badge-danger';
                    $estatusText = 'Cancelada';
                    break;
                default:
                    $estatusClass = 'badge-secondary';
                    $estatusText = $item['estatus'];
            }

            $tipoVentaClass = $item['tipo_venta'] === 'credito' ? 'badge-warning' : 'badge-success';
            $tipoVentaText = $item['tipo_venta'] === 'credito' ? 'Crédito' : 'Contado';

            $totalGeneral += floatval($item['total']);
            $totalProductos += intval($item['total_productos']);

            $html .= '
                <tr>
                    <td><strong>#' . $item['id'] . '</strong></td>
                    <td>' . $fechaFormateada . '</td>
                    <td>' . htmlspecialchars($item['cliente'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($item['vendedor'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($item['almacen'] ?? 'N/A') . '</td>
                    <td class="text-center"><span class="badge ' . $tipoVentaClass . '">' . $tipoVentaText . '</span></td>
                    <td class="text-center">' . number_format($item['total_productos']) . '</td>
                    <td class="text-right">$' . number_format($subtotalEstimado, 2) . '</td>
                    <td class="text-right">$' . number_format($item['total'], 2) . '</td>
                    <td class="text-center"><span class="badge ' . $estatusClass . '">' . $estatusText . '</span></td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="10" class="text-center">No se encontraron notas de venta</td></tr>';
    }

    // Calcular subtotal total estimado
    $subtotalTotalEstimado = $totalGeneral / 1.16;

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6"><strong>TOTALES</strong></td>
                    <td class="text-center"><strong>' . number_format($totalProductos) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($subtotalTotalEstimado, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalGeneral, 2) . '</strong></td>
                    <td>-</td>
                </tr>
            </tfoot>
        </table>';

    // Nota sobre el subtotal estimado
    $html .= '
        <div style="margin-top: 10px; font-size: 8px; color: #666;">
            * El subtotal es un cálculo estimado asumiendo un IVA del 16%
        </div>';

    // Distribución por tipo de venta
    if ($resumenData['total_ventas'] > 0) {
        $porcentajeContado = ($resumenData['ventas_contado'] / $resumenData['total_ventas']) * 100;
        $porcentajeCredito = ($resumenData['ventas_credito'] / $resumenData['total_ventas']) * 100;

        $html .= '
        <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #27ae60;">
            <strong>Distribución por Tipo de Venta:</strong><br>
            • Contado: $' . number_format($resumenData['ventas_contado'], 2) . ' (' . number_format($porcentajeContado, 1) . '%)<br>
            • Crédito: $' . number_format($resumenData['ventas_credito'], 2) . ' (' . number_format($porcentajeCredito, 1) . '%)
        </div>';
    }

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('resumen_notas_venta_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
