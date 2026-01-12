<?php
// ajax/reportes/exportar-excel-productos-mas-vendidos.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Obtener parámetros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$idAlmacen = $_GET['id_almacen'] ?? 0;
$idCategoria = $_GET['id_categoria'] ?? 0;
$tipoVenta = $_GET['tipo_venta'] ?? '';
$topN = $_GET['top_n'] ?? 10;
$ordenarPor = $_GET['ordenar_por'] ?? 'cantidad';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del PDF
    $sql = "
        SELECT 
            cp.id_producto,
            cp.clave,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            SUM(mr.cantidad) AS cantidad_vendida,
            SUM(mr.cantidad * mr.precio) AS ventas_totales,
            SUM(mr.cantidad * cp.precio_compra) AS costo_total,
            SUM(mr.cantidad * mr.precio) - SUM(mr.cantidad * cp.precio_compra) AS utilidad,
            CASE 
                WHEN SUM(mr.cantidad * mr.precio) > 0 THEN 
                    ((SUM(mr.cantidad * mr.precio) - SUM(mr.cantidad * cp.precio_compra)) / SUM(mr.cantidad * mr.precio)) * 100 
                ELSE 0 
            END AS margen,
            (SUM(mr.cantidad * mr.precio) / (SELECT SUM(mr2.cantidad * mr2.precio) 
                                           FROM mov_remisiones mr2 
                                           INNER JOIN cab_remisiones cr2 ON mr2.id_remision = cr2.id 
                                           WHERE cr2.fecha BETWEEN ? AND ?)) * 100 AS porcentaje_total
        FROM 
            mov_remisiones mr
        INNER JOIN 
            cab_remisiones cr ON mr.id_remision = cr.id
        INNER JOIN 
            cat_productos cp ON mr.id_producto = cp.id_producto
        LEFT JOIN 
            cat_categorias cc ON cp.id_categoria = cc.id_categoria
        LEFT JOIN 
            cat_almacenes ca ON cr.id_almacen = ca.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin];
    $types = "ssss";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND cp.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    $sql .= " GROUP BY cp.id_producto, cp.clave, cp.nombre, cc.nombre_categoria";

    // Ordenamiento
    switch ($ordenarPor) {
        case 'ventas':
            $orderColumn = 'ventas_totales';
            break;
        case 'utilidad':
            $orderColumn = 'utilidad';
            break;
        case 'margen':
            $orderColumn = 'margen';
            break;
        default:
            $orderColumn = 'cantidad_vendida';
    }

    $sql .= " ORDER BY $orderColumn DESC";

    // Limitar resultados si se solicita un top N
    if ($topN > 0) {
        $sql .= " LIMIT ?";
        $params[] = $topN;
        $types .= "i";
    }

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Calcular totales
    $totalVentas = 0;
    $totalProductosVendidos = 0;
    $totalUtilidad = 0;
    $totalCosto = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $totalVentas += floatval($item['ventas_totales']);
            $totalProductosVendidos += intval($item['cantidad_vendida']);
            $totalUtilidad += floatval($item['utilidad']);
            $totalCosto += floatval($item['costo_total']);
        }
    }

    $margenPromedio = $totalVentas > 0 ? ($totalUtilidad / $totalVentas) * 100 : 0;

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial FROM cat_empresas WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Productos Más Vendidos');

    // Logo de la empresa
    $logoPath = '../../img/logo-inicio.png';
    if (file_exists($logoPath)) {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo de la empresa');
        $drawing->setPath($logoPath);
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);
    }

    // Información de la empresa
    $empresaRow = 1;
    if ($empresaInfo) {
        $sheet->setCellValue('C1', $empresaInfo['nombre_comercial']);
        $sheet->setCellValue('C2', $empresaInfo['razon_social']);
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C2')->getFont()->setSize(11);
        $sheet->mergeCells('C1:H1');
        $sheet->mergeCells('C2:H2');
        $sheet->getStyle('C1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $empresaRow = 3;
    }

    // Título del reporte
    $sheet->setCellValue('A' . $empresaRow, 'Productos Más Vendidos - Ranking');
    $sheet->mergeCells('A' . $empresaRow . ':H' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)));
    $sheet->setCellValue('C' . $filtersRow, 'Almacén:');
    $sheet->setCellValue('D' . $filtersRow, $idAlmacen ? 'Específico' : 'Todos');
    $sheet->setCellValue('E' . $filtersRow, 'Categoría:');
    $sheet->setCellValue('F' . $filtersRow, $idCategoria ? 'Específica' : 'Todas');
    $sheet->setCellValue('G' . $filtersRow, 'Top:');
    $sheet->setCellValue('H' . $filtersRow, $topN > 0 ? $topN . ' productos' : 'Todos');

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':H' . $filtersRow)->applyFromArray($filterStyle);

    // Resumen
    $resumenRow = $filtersRow + 2;
    $sheet->setCellValue('A' . $resumenRow, 'RESUMEN DEL PERIODO');
    $sheet->mergeCells('A' . $resumenRow . ':H' . $resumenRow);
    $sheet->getStyle('A' . $resumenRow)->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A' . $resumenRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D1ECF1');

    $resumenRow++;
    $sheet->setCellValue('A' . $resumenRow, 'Total Ventas:');
    $sheet->setCellValue('B' . $resumenRow, $totalVentas);
    $sheet->setCellValue('C' . $resumenRow, 'Productos Vendidos:');
    $sheet->setCellValue('D' . $resumenRow, $totalProductosVendidos);
    $sheet->setCellValue('E' . $resumenRow, 'Utilidad Total:');
    $sheet->setCellValue('F' . $resumenRow, $totalUtilidad);
    $sheet->setCellValue('G' . $resumenRow, 'Margen Promedio:');
    $sheet->setCellValue('H' . $resumenRow, $margenPromedio / 100); // Formato porcentaje

    // Encabezados de tabla
    $headersRow = $resumenRow + 2;
    $headers = ['#', 'Clave', 'Producto', 'Categoría', 'Cantidad Vendida', 'Ventas Totales', 'Costo Total', 'Utilidad', 'Margen %', '% del Total'];

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headersRow, $header);
        $col++;
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $headersRow . ':J' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    if ($datos && count($datos) > 0) {
        foreach ($datos as $index => $item) {
            $cantidadVendida = intval($item['cantidad_vendida']);
            $ventasTotales = floatval($item['ventas_totales']);
            $costoTotal = floatval($item['costo_total']);
            $utilidad = floatval($item['utilidad']);
            $margen = floatval($item['margen']);
            $porcentajeTotal = $totalVentas > 0 ? ($ventasTotales / $totalVentas) * 100 : 0;

            $sheet->setCellValue('A' . $dataRow, $index + 1);
            $sheet->setCellValue('B' . $dataRow, $item['clave']);
            $sheet->setCellValue('C' . $dataRow, $item['producto']);
            $sheet->setCellValue('D' . $dataRow, $item['categoria'] ?? 'Sin categoría');
            $sheet->setCellValue('E' . $dataRow, $cantidadVendida);
            $sheet->setCellValue('F' . $dataRow, $ventasTotales);
            $sheet->setCellValue('G' . $dataRow, $costoTotal);
            $sheet->setCellValue('H' . $dataRow, $utilidad);
            $sheet->setCellValue('I' . $dataRow, $margen / 100); // Formato porcentaje
            $sheet->setCellValue('J' . $dataRow, $porcentajeTotal / 100); // Formato porcentaje

            // Alternar colores de fila
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            // Color para los primeros puestos
            if ($index === 0) {
                $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFD700');
            } elseif ($index === 1) {
                $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('C0C0C0');
            } elseif ($index === 2) {
                $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('CD7F32');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron productos vendidos');
        $sheet->mergeCells('A' . $dataRow . ':J' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales
    if ($datos && count($datos) > 0) {
        $totalRow = $dataRow;
        $sheet->setCellValue('A' . $totalRow, 'TOTALES');
        $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
        $sheet->setCellValue('E' . $totalRow, $totalProductosVendidos);
        $sheet->setCellValue('F' . $totalRow, $totalVentas);
        $sheet->setCellValue('G' . $totalRow, $totalCosto);
        $sheet->setCellValue('H' . $totalRow, $totalUtilidad);
        $sheet->setCellValue('I' . $totalRow, $margenPromedio / 100); // Formato porcentaje
        $sheet->setCellValue('J' . $totalRow, 1); // 100%

        // Estilo para totales
        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->applyFromArray($totalStyle);
    }

    // Formato de números
    $sheet->getStyle('E' . ($headersRow + 1) . ':E' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    $sheet->getStyle('F' . ($headersRow + 1) . ':H' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');

    $sheet->getStyle('I' . ($headersRow + 1) . ':J' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('0.00%');

    // Formato para totales
    if ($datos && count($datos) > 0) {
        $sheet->getStyle('E' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle('F' . $totalRow . ':H' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $sheet->getStyle('I' . $totalRow . ':J' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('0.00%');
    }

    // Autoajustar columnas
    foreach (range('A', 'J') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Centrar algunas columnas
    $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('I:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // Hoja de análisis por categoría
    if ($datos && count($datos) > 0) {
        $categorias = [];
        foreach ($datos as $item) {
            $categoria = $item['categoria'] ?? 'Sin categoría';
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [
                    'count' => 0,
                    'ventas' => 0,
                    'utilidad' => 0,
                    'cantidad' => 0
                ];
            }
            $categorias[$categoria]['count']++;
            $categorias[$categoria]['ventas'] += floatval($item['ventas_totales']);
            $categorias[$categoria]['utilidad'] += floatval($item['utilidad']);
            $categorias[$categoria]['cantidad'] += intval($item['cantidad_vendida']);
        }

        // Crear hoja de análisis
        $analysisSheet = $spreadsheet->createSheet();
        $analysisSheet->setTitle('Análisis por Categoría');

        // Título
        $analysisSheet->setCellValue('A1', 'Análisis de Ventas por Categoría');
        $analysisSheet->mergeCells('A1:E1');
        $analysisSheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $analysisSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Encabezados
        $analysisHeaders = ['Categoría', 'Cantidad Productos', 'Ventas Totales', 'Utilidad', 'Margen %', '% del Total'];
        $col = 'A';
        foreach ($analysisHeaders as $header) {
            $analysisSheet->setCellValue($col . '3', $header);
            $col++;
        }

        // Estilo encabezados
        $analysisSheet->getStyle('A3:F3')->applyFromArray($headerStyle);

        // Datos de análisis
        $analysisRow = 4;
        foreach ($categorias as $categoria => $data) {
            $margenCategoria = $data['ventas'] > 0 ? ($data['utilidad'] / $data['ventas']) * 100 : 0;
            $porcentajeTotal = $totalVentas > 0 ? ($data['ventas'] / $totalVentas) * 100 : 0;

            $analysisSheet->setCellValue('A' . $analysisRow, $categoria);
            $analysisSheet->setCellValue('B' . $analysisRow, $data['count']);
            $analysisSheet->setCellValue('C' . $analysisRow, $data['ventas']);
            $analysisSheet->setCellValue('D' . $analysisRow, $data['utilidad']);
            $analysisSheet->setCellValue('E' . $analysisRow, $margenCategoria / 100); // Formato porcentaje
            $analysisSheet->setCellValue('F' . $analysisRow, $porcentajeTotal / 100); // Formato porcentaje

            // Alternar colores
            if ($analysisRow % 2 == 0) {
                $analysisSheet->getStyle('A' . $analysisRow . ':F' . $analysisRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            $analysisRow++;
        }

        // Formato de análisis
        $analysisSheet->getStyle('B4:B' . ($analysisRow - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $analysisSheet->getStyle('C4:D' . ($analysisRow - 1))
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $analysisSheet->getStyle('E4:F' . ($analysisRow - 1))
            ->getNumberFormat()
            ->setFormatCode('0.00%');

        // Autoajustar columnas análisis
        foreach (range('A', 'F') as $column) {
            $analysisSheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Regresar a la hoja principal
        $spreadsheet->setActiveSheetIndex(0);
    }

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="productos_mas_vendidos_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
