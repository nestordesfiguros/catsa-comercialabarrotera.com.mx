<?php
// ajax/reportes/exportar-pdf-existencias-costos.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$idAlmacen = $_GET['id_almacen'] ?? 0;
$idCategoria = $_GET['id_categoria'] ?? 0;
$idProveedor = $_GET['id_proveedor'] ?? 0;
$estatusStock = $_GET['estatus_stock'] ?? '';
$ordenarPor = $_GET['ordenar_por'] ?? 'nombre';
$direccionOrden = $_GET['direccion_orden'] ?? 'asc';
$mostrarSolo = $_GET['mostrar_solo'] ?? '';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del DataTable
    $sql = "
        SELECT 
            cp.id_producto,
            cp.clave,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            ca.almacen,
            cprov.razon_social AS proveedor,
            COALESCE(i.cantidad, 0) AS existencia,
            cp.stock_minimo,
            cp.stock_maximo,
            cp.precio_compra AS costo_unitario,
            cp.precio_venta,
            (COALESCE(i.cantidad, 0) * cp.precio_compra) AS valor_total,
            cp.estado,
            cp.fecha_registro AS ultima_actualizacion
        FROM 
            cat_productos cp
        LEFT JOIN 
            inventarios i ON cp.id_producto = i.id_producto
        LEFT JOIN 
            cat_categorias cc ON cp.id_categoria = cc.id_categoria
        LEFT JOIN 
            cat_almacenes ca ON i.id_almacen = ca.id
        LEFT JOIN 
            cat_proveedores cprov ON cp.id_proveedor = cprov.id
        WHERE 
            1 = 1
    ";

    $params = [];
    $types = "";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND i.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND cp.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if ($idProveedor > 0) {
        $sql .= " AND cp.id_proveedor = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    // Filtro por estado de stock
    if (!empty($estatusStock)) {
        switch ($estatusStock) {
            case 'stock_minimo':
                $sql .= " AND COALESCE(i.cantidad, 0) <= cp.stock_minimo AND COALESCE(i.cantidad, 0) > 0";
                break;
            case 'stock_maximo':
                $sql .= " AND COALESCE(i.cantidad, 0) >= cp.stock_maximo";
                break;
            case 'sin_existencia':
                $sql .= " AND COALESCE(i.cantidad, 0) = 0";
                break;
            case 'con_existencia':
                $sql .= " AND COALESCE(i.cantidad, 0) > 0";
                break;
        }
    }

    // Filtro por estado del producto
    if ($mostrarSolo === 'activos') {
        $sql .= " AND cp.estado = 'activo'";
    } elseif ($mostrarSolo === 'inactivos') {
        $sql .= " AND cp.estado = 'inactivo'";
    }

    // Ordenamiento
    $orderColumn = 'cp.nombre';
    switch ($ordenarPor) {
        case 'clave':
            $orderColumn = 'cp.clave';
            break;
        case 'existencia':
            $orderColumn = 'existencia';
            break;
        case 'costo':
            $orderColumn = 'cp.precio_compra';
            break;
        case 'valor':
            $orderColumn = 'valor_total';
            break;
    }

    $orderDirection = strtoupper($direccionOrden) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY $orderColumn $orderDirection, cp.clave ASC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Calcular totales
    $totalProductos = 0;
    $totalExistencia = 0;
    $totalValor = 0;
    $sinStock = 0;
    $stockMinimo = 0;

    if ($datos && count($datos) > 0) {
        $totalProductos = count($datos);
        foreach ($datos as $item) {
            $existencia = intval($item['existencia']);
            $totalExistencia += $existencia;
            $totalValor += floatval($item['valor_total']);

            if ($existencia === 0) {
                $sinStock++;
            }

            if ($existencia <= intval($item['stock_minimo']) && $existencia > 0) {
                $stockMinimo++;
            }
        }
    }

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
        Página {PAGENO} de {nbpg} | Sistema ERP - Existencias y Costos
    </div>
    ');

    // HTML del reporte
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Existencias y Costos</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 7px; line-height: 1.1; }
            .report-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #2c3e50; }
            .filters { margin-bottom: 8px; padding: 6px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #28a745; font-size: 6px; }
            .resumen { margin-bottom: 8px; padding: 6px; background-color: #e8f4fd; border-radius: 5px; border-left: 4px solid #17a2b8; }
            .resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
            .resumen-item { text-align: center; padding: 4px; background: white; border-radius: 5px; border: 1px solid #bdc3c7; }
            .resumen-valor { font-size: 9px; font-weight: bold; color: #2c3e50; }
            .resumen-label { font-size: 6px; color: #7f8c8d; }
            table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 6px; }
            th { background-color: #28a745; color: white; border: 1px solid #218838; padding: 3px; text-align: left; font-weight: bold; }
            td { border: 1px solid #bdc3c7; padding: 3px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { background-color: #d1ecf1; font-weight: bold; border-top: 2px solid #17a2b8; }
            .stock-critico { background-color: #f8d7da; }
            .stock-minimo { background-color: #fff3cd; }
            .stock-normal { background-color: #d1ecf1; }
        </style>
    </head>
    <body>
        <div class="report-title">Existencias y Costos de Inventario</div>

        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            • Almacén: ' . ($idAlmacen ? 'Específico' : 'Todos') . '<br>
            • Categoría: ' . ($idCategoria ? 'Específica' : 'Todas') . '<br>
            • Proveedor: ' . ($idProveedor ? 'Específico' : 'Todos') . '<br>
            • Estado Stock: ' . ($estatusStock ? ucfirst(str_replace('_', ' ', $estatusStock)) : 'Todos') . '
        </div>

        <div class="resumen">
            <strong>Resumen General:</strong>
            <div class="resumen-grid">
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($totalProductos) . '</div>
                    <div class="resumen-label">Total Productos</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">$' . number_format($totalValor, 2) . '</div>
                    <div class="resumen-label">Valor Total</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($sinStock) . '</div>
                    <div class="resumen-label">Sin Stock</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor">' . number_format($stockMinimo) . '</div>
                    <div class="resumen-label">Stock Mínimo</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="8%">Clave</th>
                    <th width="15%">Producto</th>
                    <th width="10%">Categoría</th>
                    <th width="10%">Almacén</th>
                    <th width="12%">Proveedor</th>
                    <th width="6%" class="text-center">Existencia</th>
                    <th width="6%" class="text-center">Stock Mín</th>
                    <th width="6%" class="text-center">Stock Máx</th>
                    <th width="8%" class="text-center">Estado</th>
                    <th width="8%" class="text-right">Costo Unit.</th>
                    <th width="8%" class="text-right">Precio Venta</th>
                    <th width="9%" class="text-right">Valor Total</th>
                </tr>
            </thead>
            <tbody>';

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $existencia = intval($item['existencia']);
            $stockMinimo = intval($item['stock_minimo']);

            // Determinar clase CSS para el estado
            $estadoClass = '';
            $estadoTexto = '';

            if ($existencia === 0) {
                $estadoClass = 'stock-critico';
                $estadoTexto = 'Sin Stock';
            } elseif ($existencia <= $stockMinimo) {
                $estadoClass = 'stock-minimo';
                $estadoTexto = 'Mínimo';
            } else {
                $estadoClass = 'stock-normal';
                $estadoTexto = 'Normal';
            }

            $html .= '
                <tr class="' . $estadoClass . '">
                    <td>' . htmlspecialchars($item['clave']) . '</td>
                    <td>' . htmlspecialchars($item['producto']) . '</td>
                    <td>' . htmlspecialchars($item['categoria'] ?? 'Sin categoría') . '</td>
                    <td>' . htmlspecialchars($item['almacen'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($item['proveedor'] ?? 'N/A') . '</td>
                    <td class="text-center">' . number_format($existencia) . '</td>
                    <td class="text-center">' . number_format($stockMinimo) . '</td>
                    <td class="text-center">' . ($item['stock_maximo'] > 0 ? number_format($item['stock_maximo']) : 'N/A') . '</td>
                    <td class="text-center">' . $estadoTexto . '</td>
                    <td class="text-right">$' . number_format($item['costo_unitario'] ?? 0, 2) . '</td>
                    <td class="text-right">$' . number_format($item['precio_venta'] ?? 0, 2) . '</td>
                    <td class="text-right">$' . number_format($item['valor_total'] ?? 0, 2) . '</td>
                </tr>';
        }
    } else {
        $html .= '<tr><td colspan="12" class="text-center">No se encontraron productos</td></tr>';
    }

    // Calcular promedios
    $promedioCosto = $totalProductos > 0 ? ($datos ? array_sum(array_column($datos, 'costo_unitario')) / $totalProductos : 0) : 0;
    $promedioVenta = $totalProductos > 0 ? ($datos ? array_sum(array_column($datos, 'precio_venta')) / $totalProductos : 0) : 0;

    $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5"><strong>TOTALES / PROMEDIOS</strong></td>
                    <td class="text-center"><strong>' . number_format($totalExistencia) . '</strong></td>
                    <td colspan="2">-</td>
                    <td>-</td>
                    <td class="text-right"><strong>$' . number_format($promedioCosto, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($promedioVenta, 2) . '</strong></td>
                    <td class="text-right"><strong>$' . number_format($totalValor, 2) . '</strong></td>
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
                    'valor' => 0
                ];
            }
            $categorias[$categoria]['count']++;
            $categorias[$categoria]['valor'] += floatval($item['valor_total']);
        }

        $html .= '
        <div style="margin-top: 10px; padding: 6px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #6f42c1;">
            <strong>Distribución por Categoría:</strong><br>';

        foreach ($categorias as $categoria => $data) {
            $porcentaje = ($totalProductos > 0) ? ($data['count'] / $totalProductos) * 100 : 0;
            $html .= '• ' . htmlspecialchars($categoria) . ': ' . $data['count'] . ' productos (' . number_format($porcentaje, 1) . '%) - $' . number_format($data['valor'], 2) . '<br>';
        }

        $html .= '</div>';
    }

    $html .= '</body></html>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('existencias_costos_' . date('Ymd_His') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}
