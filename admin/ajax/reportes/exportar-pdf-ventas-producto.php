<?php
// ajax/reportes/exportar-pdf-ventas-producto.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$idProducto = $_GET['id_producto'] ?? 0;
$idCategoria = $_GET['id_categoria'] ?? 0;
$idAlmacen = $_GET['id_almacen'] ?? 0;
$orden = $_GET['orden'] ?? 'total_ventas';
$direccion = $_GET['direccion'] ?? 'DESC';
$limite = $_GET['limite'] ?? 0;

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del endpoint AJAX
    $sql = "
        SELECT 
            p.id_producto,
            p.clave AS clave_producto,
            p.nombre AS nombre_producto,
            c.nombre_categoria,
            a.almacen AS nombre_almacen,
            COALESCE(SUM(mr.cantidad), 0) AS cantidad_vendida,
            COALESCE(AVG(mr.precio_unitario), 0) AS precio_promedio,
            COALESCE(SUM(mr.cantidad * mr.precio_unitario), 0) AS total_ventas,
            COALESCE(SUM(mr.cantidad * p.precio_compra), 0) AS costo_total
        FROM 
            cat_productos p
        LEFT JOIN 
            mov_remisiones mr ON p.id_producto = mr.id_producto
        LEFT JOIN 
            cab_remisiones cr ON mr.id_remision = cr.id
        LEFT JOIN 
            cat_categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN 
            cat_almacenes a ON cr.id_almacen = a.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idProducto > 0) {
        $sql .= " AND p.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND p.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " GROUP BY p.id_producto, p.clave, p.nombre, c.nombre_categoria, a.almacen";

    $ordenValido = in_array($orden, ['total_ventas', 'cantidad_vendida', 'nombre', 'utilidad']) ? $orden : 'total_ventas';
    $direccionValida = strtoupper($direccion) === 'ASC' ? 'ASC' : 'DESC';

    if ($ordenValido === 'utilidad') {
        $sql .= " ORDER BY (total_ventas - costo_total) $direccionValida";
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
        Página {PAGENO} de {nbpg} | Sistema ERP - Reporte de Ventas por Producto
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Ventas por Producto</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.3; }
            .report-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #2c3e50; }
            .filters { margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #27ae60; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9px; }
            th { background-color: #27ae60; color: white; border: 1px solid #219653; padding: 6px; text-align: left; font-weight: bold; }
            td { border: 1px solid #bdc3c7; padding: 6px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { background-color: #d5f4e6; font-weight: bold; border-top: 2px solid #27ae60; }
            .utilidad-positiva { color: #27ae60; font-weight: bold; }
            .utilidad-negativa { color: #e74c3c; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="report-title">Reporte de Ventas por Producto</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Orden: ' . ucfirst(str_replace('_', ' ', $orden)) . ' (' . ($direccion === 'ASC' ? 'Ascendente' : 'Descendente') . ')<br>
            • Límite: ' . ($limite > 0 ? $limite . ' registros' : 'Todos') . '
        </div>

        <table>
            <thead>
                <tr>
                    <th width="20%">Producto</th>
                    <th width="15%">Categoría</th>
                    <th width="15%">Almacén</th>
                    <th width="10%" class="text-center">Cantidad</th>
                    <th width="10%" class="text-right">Precio Prom.</th>
                    <th width="10%" class="text-right">Total Ventas</th>
                    <th width="10%" class="text-right">Costo Total</th>
                    <th width="10%" class="text-right">Utilidad</th>
                    <th width="10%" class="text-right">Margen %</th>
                </tr>
            </thead>
            <tbody>';

    $totalVentas = 0;
    $totalCantidad = 0;
    $totalCosto = 0;
    $totalUtilidad = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $utilidad = $item['total_ventas'] - $item['costo_total'];
            $margen = $item['total_ventas'] > 0 ? ($utilidad / $item['total_ventas']) * 100 : 0;

            $totalVentas += $item['total_ventas'];
            $totalCantidad += $item['cantidad_vendida'];
            $totalCosto += $item['costo_total'];
            $totalUtilidad += $utilidad;

            $claseUtilidad = $utilidad >= 0 ? 'utilidad-positiva' : 'utilidad-negativa';

            $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($item['clave_producto']) . '</strong><br><small>' . htmlspecialchars($item['nombre_producto']) . '</small></td>
                    <td>' . htmlspecialchars($item['nombre_categoria'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($item['nombre_almacen'] ?? 'N/A') . '</td>
                    <td class="text-center">' . number_format($item['cantidad_vendida']) . '</td>
                    <td class="text-right">$' . number_format($item['precio_promedio'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['total_ventas'], 2) . '</td>
                    <td class="text-right">$' . number_format($item['costo_total'], 2) . '</td>
                    <td class="text-right ' . $claseUtilidad . '">$' . number_format($utilidad, 2) . '</td>
                    <td class="text-right ' . $claseUtilidad . '">' . number_format($margen, 2) . '%</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="9" class="text-center">No se encontraron datos</td></tr>';
    }

    $margenPromedio = $totalVentas > 0 ? ($totalUtilidad / $totalVentas) * 100 : 0;
    $claseTotalUtilidad = $totalUtilidad >= 0 ? 'utilidad-positiva' : 'utilidad-negativa';

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>TOTALES GENERALES</strong></td>
                    <td class="text-center"><strong>' . number_format($totalCantidad) . '</strong></td>
                    <td class="text-right">-</td>
                    <td class="text-right"><strong>$' . number_format($totalVentas, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalCosto, 2) . '</strong></td>
                    <td class="text-right ' . $claseTotalUtilidad . '"><strong>$' . number_format($totalUtilidad, 2) . '</strong></td>
                    <td class="text-right ' . $claseTotalUtilidad . '"><strong>' . number_format($margenPromedio, 2) . '%</strong></td>
                </tr>
            </tfoot>
        </table>';

    // Resumen ejecutivo
    if ($datos && count($datos) > 0) {
        $html .= '
        <div style="margin-top: 15px; padding: 10px; background-color: #e8f6f3; border-radius: 5px; border-left: 4px solid #27ae60;">
            <strong>Resumen Ejecutivo:</strong><br>
            • Total de Productos: ' . count($datos) . '<br>
            • Unidades Vendidas: ' . number_format($totalCantidad) . '<br>
            • Ventas Totales: $' . number_format($totalVentas, 2) . '<br>
            • Utilidad Total: $' . number_format($totalUtilidad, 2) . '<br>
            • Margen Promedio: ' . number_format($margenPromedio, 2) . '%
        </div>';
    }

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('reporte_ventas_producto_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
