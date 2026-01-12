<?php
// ajax/reportes/exportar-excel-compras-proveedor.php

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

    $sqlEmpresa = "SELECT razon_social, nombre_comercial, rfc 
                   FROM cat_empresas 
                   WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Compras por Proveedor');

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
        $sheet->mergeCells('C1:G1');
        $sheet->mergeCells('C2:G2');
        $sheet->getStyle('C1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $empresaRow = 3;
    }

    $sheet->setCellValue('A' . $empresaRow, 'Reporte de Compras por Proveedor');
    $sheet->mergeCells('A' . $empresaRow . ':G' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos'));
    $sheet->setCellValue('D' . $filtersRow, 'Estatus:');
    $sheet->setCellValue('E' . $filtersRow, $estatus ? ucfirst($estatus) : 'Todos');
    $sheet->setCellValue('G' . $filtersRow, 'Generado: ' . date('d/m/Y H:i:s'));

    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':G' . $filtersRow)->applyFromArray($filterStyle);

    $headersRow = $filtersRow + 2;
    $headers = ['Proveedor', 'Total Compras', 'Cantidad de Compras', 'Promedio por Compra', 'Compra Mínima', 'Compra Máxima', 'Última Compra'];
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

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $promedioCompra = $item['cantidad_compras'] > 0 ? $item['total_compras'] / $item['cantidad_compras'] : 0;
            $ultimaCompra = $item['ultima_compra'] ? date('d/m/Y', strtotime($item['ultima_compra'])) : 'N/A';

            $totalCompras += $item['total_compras'];
            $totalCantidad += $item['cantidad_compras'];

            $sheet->setCellValue('A' . $dataRow, $item['nombre_proveedor']);
            $sheet->setCellValue('B' . $dataRow, $item['total_compras']);
            $sheet->setCellValue('C' . $dataRow, $item['cantidad_compras']);
            $sheet->setCellValue('D' . $dataRow, $promedioCompra);
            $sheet->setCellValue('E' . $dataRow, $item['compra_minima']);
            $sheet->setCellValue('F' . $dataRow, $item['compra_maxima']);
            $sheet->setCellValue('G' . $dataRow, $ultimaCompra);

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

    $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
    $sheet->setCellValue('B' . $totalRow, $totalCompras);
    $sheet->setCellValue('C' . $totalRow, $totalCantidad);
    $sheet->setCellValue('D' . $totalRow, $promedioGeneral);

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

        $sheet->setCellValue('A' . ($summaryRow + 1), 'Total de Proveedores:');
        $sheet->setCellValue('B' . ($summaryRow + 1), count($datos));

        $sheet->setCellValue('A' . ($summaryRow + 2), 'Compras Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 2), $totalCompras);

        $sheet->setCellValue('A' . ($summaryRow + 3), 'Transacciones Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 3), $totalCantidad);

        $sheet->setCellValue('A' . ($summaryRow + 4), 'Compra Promedio:');
        $sheet->setCellValue('B' . ($summaryRow + 4), $promedioGeneral);

        $summaryStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F6F3']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $summaryRow . ':B' . ($summaryRow + 4))->applyFromArray($summaryStyle);
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
    header('Content-Disposition: attachment;filename="reporte_compras_proveedor_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
