<?php
// ajax/reportes/exportar-excel-ventas-producto.php

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
    // Reutilizar la misma consulta
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

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ventas por Producto');

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
        $sheet->mergeCells('C1:I1');
        $sheet->mergeCells('C2:I2');
        $sheet->getStyle('C1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $empresaRow = 3;
    }

    // Título del reporte
    $sheet->setCellValue('A' . $empresaRow, 'Reporte de Ventas por Producto');
    $sheet->mergeCells('A' . $empresaRow . ':I' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos'));
    $sheet->setCellValue('E' . $filtersRow, 'Orden:');
    $sheet->setCellValue('F' . $filtersRow, ucfirst(str_replace('_', ' ', $orden)) . ' (' . ($direccion === 'ASC' ? 'Asc' : 'Desc') . ')');
    $sheet->setCellValue('H' . $filtersRow, 'Límite:');
    $sheet->setCellValue('I' . $filtersRow, $limite > 0 ? $limite : 'Todos');

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F6F3']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':I' . $filtersRow)->applyFromArray($filterStyle);

    // Encabezados de tabla
    $headersRow = $filtersRow + 2;
    $headers = ['Producto', 'Categoría', 'Almacén', 'Cantidad Vendida', 'Precio Promedio', 'Total Ventas', 'Costo Total', 'Utilidad', 'Margen %'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headersRow, $header);
        $col++;
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '27AE60']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $headersRow . ':I' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
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

            $sheet->setCellValue('A' . $dataRow, $item['clave_producto'] . ' - ' . $item['nombre_producto']);
            $sheet->setCellValue('B' . $dataRow, $item['nombre_categoria'] ?? 'N/A');
            $sheet->setCellValue('C' . $dataRow, $item['nombre_almacen'] ?? 'N/A');
            $sheet->setCellValue('D' . $dataRow, $item['cantidad_vendida']);
            $sheet->setCellValue('E' . $dataRow, $item['precio_promedio']);
            $sheet->setCellValue('F' . $dataRow, $item['total_ventas']);
            $sheet->setCellValue('G' . $dataRow, $item['costo_total']);
            $sheet->setCellValue('H' . $dataRow, $utilidad);
            $sheet->setCellValue('I' . $dataRow, $margen);

            // Alternar colores de fila
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':I' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            // Color para utilidad positiva/negativa
            if ($utilidad >= 0) {
                $sheet->getStyle('H' . $dataRow . ':I' . $dataRow)
                    ->getFont()
                    ->getColor()
                    ->setARGB('2E7D32');
            } else {
                $sheet->getStyle('H' . $dataRow . ':I' . $dataRow)
                    ->getFont()
                    ->getColor()
                    ->setARGB('C62828');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron datos con los filtros seleccionados');
        $sheet->mergeCells('A' . $dataRow . ':I' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales
    $totalRow = $dataRow;
    $margenPromedio = $totalVentas > 0 ? ($totalUtilidad / $totalVentas) * 100 : 0;

    $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
    $sheet->setCellValue('D' . $totalRow, $totalCantidad);
    $sheet->setCellValue('F' . $totalRow, $totalVentas);
    $sheet->setCellValue('G' . $totalRow, $totalCosto);
    $sheet->setCellValue('H' . $totalRow, $totalUtilidad);
    $sheet->setCellValue('I' . $totalRow, $margenPromedio);

    // Estilo para totales
    $totalStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $totalRow . ':I' . $totalRow)->applyFromArray($totalStyle);

    // Formato de números
    $sheet->getStyle('E' . ($headersRow + 1) . ':E' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');
    $sheet->getStyle('F' . ($headersRow + 1) . ':H' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');
    $sheet->getStyle('I' . ($headersRow + 1) . ':I' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('0.00"%"');

    // Formato para cantidad
    $sheet->getStyle('D' . ($headersRow + 1) . ':D' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    // Autoajustar columnas
    foreach (range('A', 'I') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_ventas_producto_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
