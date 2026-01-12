<?php
// ajax/reportes/exportar-excel-compras-generales.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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

    $sqlEmpresa = "SELECT razon_social, nombre_comercial, rfc 
                   FROM cat_empresas 
                   WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Compras Generales');

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

    $sheet->setCellValue('A' . $empresaRow, 'Reporte de Compras Generales por Periodo');
    $sheet->mergeCells('A' . $empresaRow . ':H' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos'));
    $sheet->setCellValue('D' . $filtersRow, 'Agrupación:');
    $sheet->setCellValue('E' . $filtersRow, ucfirst($agrupacion));
    $sheet->setCellValue('F' . $filtersRow, 'Estatus:');
    $sheet->setCellValue('G' . $filtersRow, $estatus ? ucfirst($estatus) : 'Todos');
    $sheet->setCellValue('H' . $filtersRow, 'Generado: ' . date('d/m/Y H:i:s'));

    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':H' . $filtersRow)->applyFromArray($filterStyle);

    $headersRow = $filtersRow + 2;
    $headers = ['Periodo', 'Total Compras', 'Cantidad de Compras', 'Promedio por Compra', 'Compra Mínima', 'Compra Máxima', 'Proveedores Únicos'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headersRow, $header);
        $col++;
    }

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '27AE60']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $headersRow . ':G' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    $dataRow = $headersRow + 1;
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

            $sheet->setCellValue('A' . $dataRow, $item['periodo']);
            $sheet->setCellValue('B' . $dataRow, $item['total_compras']);
            $sheet->setCellValue('C' . $dataRow, $item['cantidad_compras']);
            $sheet->setCellValue('D' . $dataRow, $promedio);
            $sheet->setCellValue('E' . $dataRow, $item['compra_minima']);
            $sheet->setCellValue('F' . $dataRow, $item['compra_maxima']);
            $sheet->setCellValue('G' . $dataRow, $item['proveedores_unicos_count']);

            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':G' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron datos con los filtros seleccionados');
        $sheet->mergeCells('A' . $dataRow . ':G' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    $totalRow = $dataRow;
    $promedioGeneral = $totalCantidad > 0 ? $totalCompras / $totalCantidad : 0;
    $totalProveedoresCount = count($totalProveedores);

    $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
    $sheet->setCellValue('B' . $totalRow, $totalCompras);
    $sheet->setCellValue('C' . $totalRow, $totalCantidad);
    $sheet->setCellValue('D' . $totalRow, $promedioGeneral);
    $sheet->setCellValue('E' . $totalRow, $compraMinima !== PHP_FLOAT_MAX ? $compraMinima : 0);
    $sheet->setCellValue('F' . $totalRow, $compraMaxima);
    $sheet->setCellValue('G' . $totalRow, $totalProveedoresCount);

    $totalStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '219653']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray($totalStyle);

    $summaryRow = $totalRow + 2;
    if ($datos && count($datos) > 0) {
        $sheet->setCellValue('A' . $summaryRow, 'RESUMEN EJECUTIVO');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);

        $sheet->setCellValue('A' . ($summaryRow + 1), 'Períodos Analizados:');
        $sheet->setCellValue('B' . ($summaryRow + 1), count($datos));

        $sheet->setCellValue('A' . ($summaryRow + 2), 'Compras Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 2), $totalCompras);

        $sheet->setCellValue('A' . ($summaryRow + 3), 'Transacciones Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 3), $totalCantidad);

        $sheet->setCellValue('A' . ($summaryRow + 4), 'Compra Promedio:');
        $sheet->setCellValue('B' . ($summaryRow + 4), $promedioGeneral);

        $sheet->setCellValue('A' . ($summaryRow + 5), 'Proveedores Únicos:');
        $sheet->setCellValue('B' . ($summaryRow + 5), $totalProveedoresCount);

        $summaryStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F6F3']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $summaryRow . ':B' . ($summaryRow + 5))->applyFromArray($summaryStyle);
    }

    $numberColumns = ['B', 'D', 'E', 'F'];
    foreach ($numberColumns as $col) {
        $sheet->getStyle($col . ($headersRow + 1) . ':' . $col . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
    }

    $sheet->getStyle('C' . ($headersRow + 1) . ':C' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    $sheet->getStyle('G' . ($headersRow + 1) . ':G' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    foreach (range('A', 'G') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $sheet->getRowDimension($headersRow)->setRowHeight(25);
    for ($i = $headersRow + 1; $i <= $dataRow; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(20);
    }

    $tableStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'BDC3C7']
            ]
        ]
    ];
    $sheet->getStyle('A' . $headersRow . ':G' . $totalRow)->applyFromArray($tableStyle);

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_compras_generales_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
