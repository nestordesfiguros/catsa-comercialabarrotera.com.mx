<?php
// ajax/reportes/exportar-pdf-ventas-cliente.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$idCliente = $_GET['id_cliente'] ?? 0;
$idVendedor = $_GET['id_vendedor'] ?? 0;
$tipoVenta = $_GET['tipo_venta'] ?? '';
$orden = $_GET['orden'] ?? 'total_ventas';
$direccion = $_GET['direccion'] ?? 'DESC';
$limite = $_GET['limite'] ?? 0;

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del endpoint AJAX
    $sql = "
        SELECT 
            c.id,
            c.no_cliente,
            COALESCE(c.nombre_comercial, c.razon_social) AS nombre_cliente,
            CONCAT(v.nombre, ' ', v.apellido1, ' ', COALESCE(v.apellido2, '')) AS nombre_vendedor,
            cr.tipo_venta,
            COUNT(DISTINCT cr.id) AS cantidad_compras,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COALESCE(MIN(cr.total), 0) AS compra_minima,
            COALESCE(MAX(cr.total), 0) AS compra_maxima,
            MAX(cr.fecha) AS ultima_compra
        FROM 
            cat_clientes c
        LEFT JOIN 
            cab_remisiones cr ON c.id = cr.id_cliente
        LEFT JOIN 
            cat_vendedores v ON cr.id_vendedor = v.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idCliente > 0) {
        $sql .= " AND c.id = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    $sql .= " GROUP BY c.id, c.no_cliente, c.razon_social, c.nombre_comercial, v.nombre, v.apellido1, v.apellido2, cr.tipo_venta";

    $ordenValido = in_array($orden, ['total_ventas', 'cantidad_compras', 'nombre_cliente', 'ticket_promedio']) ? $orden : 'total_ventas';
    $direccionValida = strtoupper($direccion) === 'ASC' ? 'ASC' : 'DESC';

    if ($ordenValido === 'ticket_promedio') {
        $sql .= " ORDER BY (total_ventas / NULLIF(cantidad_compras, 0)) $direccionValida";
    } else {
        $sql .= " ORDER BY $ordenValido $direccionValida";
    }

    if ($limite > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limite;
        $types .= "i";
    }

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

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
        Página {PAGENO} de {nbpg} | Sistema ERP - Reporte de Ventas por Cliente
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Ventas por Cliente</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.3; }
            .report-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #2c3e50; }
            .filters { margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #3498db; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9px; }
            th { background-color: #3498db; color: white; border: 1px solid #2980b9; padding: 6px; text-align: left; font-weight: bold; }
            td { border: 1px solid #bdc3c7; padding: 6px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { background-color: #d6eaf8; font-weight: bold; border-top: 2px solid #3498db; }
            .badge-contado { background-color: #27ae60; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px; }
            .badge-credito { background-color: #f39c12; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        </style>
    </head>
    <body>
        <div class="report-title">Reporte de Ventas por Cliente</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Orden: ' . ucfirst(str_replace('_', ' ', $orden)) . ' (' . ($direccion === 'ASC' ? 'Ascendente' : 'Descendente') . ')<br>
            • Límite: ' . ($limite > 0 ? $limite . ' registros' : 'Todos') . '
        </div>

        <table>
            <thead>
                <tr>
                    <th width="20%">Cliente</th>
                    <th width="15%">Vendedor</th>
                    <th width="10%">Tipo Venta</th>
                    <th width="10%" class="text-center">Cantidad Compras</th>
                    <th width="10%" class="text-right">Ticket Promedio</th>
                    <th width="10%" class="text-right">Compra Mínima</th>
                    <th width="10%" class="text-right">Compra Máxima</th>
                    <th width="10%" class="text-right">Total Ventas</th>
                    <th width="15%" class="text-center">Última Compra</th>
                </tr>
            </thead>
            <tbody>';

    $totalVentas = 0;
    $totalCompras = 0;
    $sumaTickets = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $ticketPromedio = $item['cantidad_compras'] > 0 ? $item['total_ventas'] / $item['cantidad_compras'] : 0;

            $totalVentas += $item['total_ventas'];
            $totalCompras += $item['cantidad_compras'];
            $sumaTickets += $ticketPromedio;

            $badgeClass = $item['tipo_venta'] === 'credito' ? 'badge-credito' : 'badge-contado';
            $tipoVentaTexto = $item['tipo_venta'] === 'credito' ? 'Crédito' : 'Contado';

            $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($item['no_cliente']) . '</strong><br><small>' . htmlspecialchars($item['nombre_cliente']) . '</small></td>
                    <td>' . htmlspecialchars($item['nombre_vendedor'] ?? 'N/A') . '</td>
                    <td><span class="' . $badgeClass . '">' . $tipoVentaTexto . '</span></td>
                    <td class="text-center">' . number_format($item['cantidad_compras']) . '</td>
                    <td class="text-right">$' . number_format($ticketPromedio, 2) . '</td>
                    <td class="text-right">$' . number_format($item['compra_minima'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['compra_maxima'], 2) . '</td>
                    <td class="text-right"><strong>$' . number_format($item['total_ventas'], 2) . '</strong></td>
                    <td class="text-center">' . ($item['ultima_compra'] ? date('d/m/Y', strtotime($item['ultima_compra'])) : 'N/A') . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="9" class="text-center">No se encontraron datos</td></tr>';
    }

    $ticketPromedioGeneral = $totalCompras > 0 ? $totalVentas / $totalCompras : 0;

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>TOTALES GENERALES</strong></td>
                    <td class="text-center"><strong>' . number_format($totalCompras) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($ticketPromedioGeneral, 2) . '</strong></td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right"><strong>$' . number_format($totalVentas, 2) . '</strong></td>
                    <td class="text-center">-</td>
                </tr>
            </tfoot>
        </table>';

    // Resumen ejecutivo
    if ($datos && count($datos) > 0) {
        $html .= '
        <div style="margin-top: 15px; padding: 10px; background-color: #e8f4fd; border-radius: 5px; border-left: 4px solid #3498db;">
            <strong>Resumen Ejecutivo:</strong><br>
            • Total de Clientes: ' . count($datos) . '<br>
            • Compras Totales: ' . number_format($totalCompras) . '<br>
            • Ventas Totales: $' . number_format($totalVentas, 2) . '<br>
            • Ticket Promedio: $' . number_format($ticketPromedioGeneral, 2) . '<br>
            • Cliente Promedio: $' . number_format($totalVentas / count($datos), 2) . '
        </div>';
    }

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('reporte_ventas_cliente_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
