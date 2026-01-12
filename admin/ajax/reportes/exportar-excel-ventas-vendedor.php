<?php
// ajax/reportes/exportar-excel-ventas-vendedor.php

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
$idVendedor = $_GET['id_vendedor'] ?? 0;
$estatus = $_GET['estatus'] ?? '';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta
    $sql = "
        SELECT 
            v.id AS id_vendedor,
            CONCAT(v.nombre, ' ', v.apellido1, ' ', COALESCE(v.apellido2, '')) AS nombre_vendedor,
            v.comision AS porcentaje_comision,
            COUNT(DISTINCT r.id) AS cantidad_ventas,
            COALESCE(SUM(r.total), 0) AS total_ventas,
            COALESCE(MIN(r.total), 0) AS venta_minima,
            COALESCE(MAX(r.total), 0) AS venta_maxima
        FROM 
            cat_vendedores v
        LEFT JOIN 
            cab_remisiones r ON v.id = r.id_vendedor 
            AND r.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idVendedor > 0) {
        $sql .= " AND v.id = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND r.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    } else {
        $sql .= " AND r.estatus IN ('procesada', 'pendiente')";
    }

    $sql .= " 
        GROUP BY 
            v.id, v.nombre, v.apellido1, v.apellido2, v.comision
        HAVING 
            cantidad_ventas > 0 OR ? = 1
        ORDER BY 
            total_ventas DESC
    ";

    $params[] = ($idVendedor > 0) ? 0 : 1;
    $types .= "i";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial, rfc 
                   FROM cat_empresas 
                   WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ventas por Vendedor');

    // Logo de la empresa (ruta corregida)
    $logoPath = '../../img/logo-inicio.png';
    if (file_exists($logoPath)) {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo de la empresa');
        $drawing->setPath($logoPath);
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);
    } else {
        // Log para debugging
        error_log("Logo no encontrado en: " . realpath($logoPath));
    }

    // Información de la empresa
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

    // Título del reporte
    $sheet->setCellValue('A' . $empresaRow, 'Reporte de Ventas por Vendedor');
    $sheet->mergeCells('A' . $empresaRow . ':G' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos'));
    $sheet->setCellValue('D' . $filtersRow, 'Estatus:');
    $sheet->setCellValue('E' . $filtersRow, $estatus ? ucfirst($estatus) : 'Todos');
    $sheet->setCellValue('G' . $filtersRow, 'Generado: ' . date('d/m/Y H:i:s'));

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':G' . $filtersRow)->applyFromArray($filterStyle);

    // Encabezados de tabla
    $headersRow = $filtersRow + 2;
    $headers = ['Vendedor', 'Total Ventas', 'Cantidad de Ventas', 'Promedio por Venta', 'Venta Mínima', 'Venta Máxima', 'Comisión Total'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headersRow, $header);
        $col++;
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $headersRow . ':G' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    $totalVentas = 0;
    $totalCantidad = 0;
    $totalComision = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $promedioVenta = $item['cantidad_ventas'] > 0 ? $item['total_ventas'] / $item['cantidad_ventas'] : 0;
            $comisionTotal = $item['total_ventas'] * ($item['porcentaje_comision'] / 100);

            $totalVentas += $item['total_ventas'];
            $totalCantidad += $item['cantidad_ventas'];
            $totalComision += $comisionTotal;

            $sheet->setCellValue('A' . $dataRow, $item['nombre_vendedor']);
            $sheet->setCellValue('B' . $dataRow, $item['total_ventas']);
            $sheet->setCellValue('C' . $dataRow, $item['cantidad_ventas']);
            $sheet->setCellValue('D' . $dataRow, $promedioVenta);
            $sheet->setCellValue('E' . $dataRow, $item['venta_minima']);
            $sheet->setCellValue('F' . $dataRow, $item['venta_maxima']);
            $sheet->setCellValue('G' . $dataRow, $comisionTotal);

            // Alternar colores de fila para mejor legibilidad
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

    // Totales
    $totalRow = $dataRow;
    $promedioGeneral = $totalCantidad > 0 ? $totalVentas / $totalCantidad : 0;

    $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
    $sheet->setCellValue('B' . $totalRow, $totalVentas);
    $sheet->setCellValue('C' . $totalRow, $totalCantidad);
    $sheet->setCellValue('D' . $totalRow, $promedioGeneral);
    $sheet->setCellValue('G' . $totalRow, $totalComision);

    // Estilo para totales
    $totalStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '27AE60']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray($totalStyle);

    // Resumen ejecutivo
    $summaryRow = $totalRow + 2;
    if ($datos && count($datos) > 0) {
        $sheet->setCellValue('A' . $summaryRow, 'RESUMEN EJECUTIVO');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);

        $sheet->setCellValue('A' . ($summaryRow + 1), 'Total de Vendedores:');
        $sheet->setCellValue('B' . ($summaryRow + 1), count($datos));

        $sheet->setCellValue('A' . ($summaryRow + 2), 'Ventas Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 2), $totalVentas);

        $sheet->setCellValue('A' . ($summaryRow + 3), 'Transacciones Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 3), $totalCantidad);

        $sheet->setCellValue('A' . ($summaryRow + 4), 'Comisiones Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 4), $totalComision);

        $sheet->setCellValue('A' . ($summaryRow + 5), 'Ticket Promedio:');
        $sheet->setCellValue('B' . ($summaryRow + 5), $promedioGeneral);

        // Estilo para resumen
        $summaryStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F6F3']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $summaryRow . ':B' . ($summaryRow + 5))->applyFromArray($summaryStyle);
    }

    // Formato de números
    $numberColumns = ['B', 'D', 'E', 'F', 'G'];
    foreach ($numberColumns as $col) {
        $sheet->getStyle($col . ($headersRow + 1) . ':' . $col . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
    }

    // Formato para cantidad
    $sheet->getStyle('C' . ($headersRow + 1) . ':C' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    // Autoajustar columnas
    foreach (range('A', 'G') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Ajustar altura de filas
    $sheet->getRowDimension($headersRow)->setRowHeight(25);
    for ($i = $headersRow + 1; $i <= $dataRow; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(20);
    }

    // Bordes para toda la tabla
    $tableStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'BDC3C7']
            ]
        ]
    ];
    $sheet->getStyle('A' . $headersRow . ':G' . $totalRow)->applyFromArray($tableStyle);

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_ventas_vendedor_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
