<?php
// ajax/reportes/exportar-pdf-kardex.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$idProducto = $_GET['id_producto'] ?? 0;
$idAlmacen = $_GET['id_almacen'] ?? 0;
$tipoMovimiento = $_GET['tipo_movimiento'] ?? '';
$referencia = $_GET['referencia'] ?? '';
$agruparPor = $_GET['agrupar_por'] ?? 'producto';

$clsConsulta = new Consultas();

try {
    // Consulta para obtener los datos del kárdex
    $sql = "
        SELECT 
            ib.fecha,
            cp.clave,
            cp.nombre AS producto,
            ca.almacen,
            ib.tipo_movimiento,
            ib.referencia,
            CASE 
                WHEN ib.tipo_movimiento LIKE '%entrada%' OR ib.tipo_movimiento LIKE '%compra%' THEN ib.cantidad
                ELSE 0 
            END AS entrada,
            CASE 
                WHEN ib.tipo_movimiento LIKE '%salida%' OR ib.tipo_movimiento LIKE '%venta%' THEN ib.cantidad
                ELSE 0 
            END AS salida,
            ib.cantidad AS movimiento,
            (SELECT precio_compra FROM cat_productos WHERE id_producto = ib.id_producto) AS costo_unitario,
            (ib.cantidad * (SELECT precio_compra FROM cat_productos WHERE id_producto = ib.id_producto)) AS costo_total,
            u.nombre AS usuario
        FROM 
            inventario_bitacora ib
        INNER JOIN 
            cat_productos cp ON ib.id_producto = cp.id_producto
        LEFT JOIN 
            cat_almacenes ca ON ib.id_almacen = ca.id
        LEFT JOIN 
            usuarios u ON ib.id_usuario = u.id
        WHERE 
            ib.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    // Aplicar filtros
    if ($idProducto > 0) {
        $sql .= " AND ib.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND ib.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if (!empty($tipoMovimiento)) {
        $sql .= " AND ib.tipo_movimiento LIKE ?";
        $params[] = "%$tipoMovimiento%";
        $types .= "s";
    }

    if (!empty($referencia)) {
        $sql .= " AND ib.referencia LIKE ?";
        $params[] = "%$referencia%";
        $types .= "s";
    }

    $sql .= " ORDER BY ib.fecha DESC, ib.id DESC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Calcular totales
    $totalEntradas = 0;
    $totalSalidas = 0;
    $totalCosto = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $totalEntradas += floatval($item['entrada']);
            $totalSalidas += floatval($item['salida']);
            $totalCosto += floatval($item['costo_total']);
        }
    }

    $saldoFinal = $totalEntradas - $totalSalidas;

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
        Página {PAGENO} de {nbpg} | Sistema ERP - Kárdex de Inventario
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Kárdex de Inventario</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.2; }
            .report-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #2c3e50; }
            .filters { margin-bottom: 10px; padding: 8px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #3498db; font-size: 7px; }
            .resumen { margin-bottom: 10px; padding: 8px; background-color: #e8f4fd; border-radius: 5px; border-left: 4px solid #2980b9; }
            .resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
            .resumen-item { text-align: center; padding: 6px; background: white; border-radius: 5px; border: 1px solid #bdc3c7; }
            .resumen-valor { font-size: 10px; font-weight: bold; color: #2c3e50; }
            .resumen-label { font-size: 7px; color: #7f8c8d; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 7px; }
            th { background-color: #3498db; color: white; border: 1px solid #2980b9; padding: 4px; text-align: left; font-weight: bold; }
            td { border: 1px solid #bdc3c7; padding: 4px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { background-color: #d6eaf8; font-weight: bold; border-top: 2px solid #3498db; }
            .movimiento-entrada { background-color: #d5f4e6; }
            .movimiento-salida { background-color: #f4d5d5; }
        </style>
    </head>
    <body>
        <div class="report-title">Kárdex - Movimientos de Inventario</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Período: ' . ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos') . '<br>
            • Producto: ' . ($idProducto ? 'Específico' : 'Todos') . '<br>
            • Almacén: ' . ($idAlmacen ? 'Específico' : 'Todos') . '<br>
            • Tipo Movimiento: ' . ($tipoMovimiento ? ucfirst($tipoMovimiento) : 'Todos') . '
        </div>

        <div class="resumen">
            <strong>Resumen General:</strong>
            <div class="resumen-grid">
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($totalEntradas) . '</div>
                    <div class="resumen-label">Total Entradas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($totalSalidas) . '</div>
                    <div class="resumen-label">Total Salidas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($saldoFinal) . '</div>
                    <div class="resumen-label">Saldo Final</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format(count($datos)) . '</div>
                    <div class="resumen-label">Total Movimientos</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="8%">Fecha</th>
                    <th width="15%">Producto</th>
                    <th width="10%">Almacén</th>
                    <th width="12%">Tipo Movimiento</th>
                    <th width="10%">Referencia</th>
                    <th width="8%" class="text-center">Entrada</th>
                    <th width="8%" class="text-center">Salida</th>
                    <th width="8%" class="text-center">Saldo</th>
                    <th width="10%" class="text-right">Costo Unit.</th>
                    <th width="10%" class="text-right">Costo Total</th>
                    <th width="11%">Usuario</th>
                </tr>
            </thead>
            <tbody>';

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $fechaFormateada = $item['fecha'] ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A';
            $entrada = floatval($item['entrada']);
            $salida = floatval($item['salida']);
            $saldo = $entrada - $salida;

            $claseFila = $entrada > 0 ? 'movimiento-entrada' : ($salida > 0 ? 'movimiento-salida' : '');

            $html .= '
                <tr class="' . $claseFila . '">
                    <td>' . $fechaFormateada . '</td>
                    <td>' . htmlspecialchars($item['clave'] . ' - ' . $item['producto']) . '</td>
                    <td>' . htmlspecialchars($item['almacen'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars(ucfirst($item['tipo_movimiento'])) . '</td>
                    <td class="text-center">' . htmlspecialchars($item['referencia'] ?? 'N/A') . '</td>
                    <td class="text-center">' . ($entrada > 0 ? number_format($entrada) : '') . '</td>
                    <td class="text-center">' . ($salida > 0 ? number_format($salida) : '') . '</td>
                    <td class="text-center">' . number_format($saldo) . '</td>
                    <td class="text-right">$' . number_format($item['costo_unitario'] ?? 0, 2) . '</td>
                    <td class="text-right">$' . number_format($item['costo_total'] ?? 0, 2) . '</td>
                    <td>' . htmlspecialchars($item['usuario'] ?? 'Sistema') . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="11" class="text-center">No se encontraron movimientos de inventario</td></tr>';
    }

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5"><strong>TOTALES</strong></td>
                    <td class="text-center"><strong>' . number_format($totalEntradas) . '</strong></td>
                    <td class="text-center"><strong>' . number_format($totalSalidas) . '</strong></td>
                    <td class="text-center"><strong>' . number_format($saldoFinal) . '</strong></td>
                    <td>-</td>
                    <td class="text-right"><strong>$' . number_format($totalCosto, 2) . '</strong></td>
                    <td>-</td>
                </tr>
            </tfoot>
        </table>';

    // Análisis por tipo de movimiento
    if ($datos && count($datos) > 0) {
        $tiposMovimiento = [];
        foreach ($datos as $item) {
            $tipo = $item['tipo_movimiento'];
            if (!isset($tiposMovimiento[$tipo])) {
                $tiposMovimiento[$tipo] = 0;
            }
            $tiposMovimiento[$tipo]++;
        }

        $html .= '
        <div style="margin-top: 10px; padding: 8px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #27ae60;">
            <strong>Distribución por Tipo de Movimiento:</strong><br>';

        foreach ($tiposMovimiento as $tipo => $cantidad) {
            $porcentaje = (count($datos) > 0) ? ($cantidad / count($datos)) * 100 : 0;
            $html .= '• ' . ucfirst($tipo) . ': ' . $cantidad . ' movimientos (' . number_format($porcentaje, 1) . '%)<br>';
        }

        $html .= '</div>';
    }

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('kardex_inventario_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
