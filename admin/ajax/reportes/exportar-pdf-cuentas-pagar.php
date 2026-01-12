<?php
// ajax/reportes/exportar-excel-cuentas-pagar.php

require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$fechaCorte = $_GET['fecha_corte'] ?? '';
$idProveedor = $_GET['id_proveedor'] ?? 0;
$estatus = $_GET['estatus'] ?? '';
$diasVencimiento = $_GET['dias_vencimiento'] ?? '';
$montoMinimo = $_GET['monto_minimo'] ?? 0;
$montoMaximo = $_GET['monto_maximo'] ?? 0;
$orden = $_GET['orden'] ?? 'fecha_asc';

$clsConsulta = new Consultas();

try {
    $sql = "
        SELECT 
            cxp.id,
            cxp.id_proveedor,
            cxp.id_compra,
            cxp.fecha,
            cxp.monto_total,
            cxp.monto_pagado,
            cxp.estatus,
            cxp.observaciones,
            p.razon_social as nombre_proveedor,
            p.nombre_Comercial,
            cc.fecha as fecha_compra,
            DATEDIFF(?, cxp.fecha) as dias_transcurridos
        FROM 
            cuentas_por_pagar cxp
        INNER JOIN 
            cat_proveedores p ON cxp.id_proveedor = p.id
        LEFT JOIN 
            cab_compras cc ON cxp.id_compra = cc.id
        WHERE 
            cxp.monto_total > cxp.monto_pagado
    ";

    $params = [$fechaCorte];
    $types = "s";

    if ($idProveedor > 0) {
        $sql .= " AND cxp.id_proveedor = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    if (!empty($estatus)) {
        $sql .= " AND cxp.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    }

    if ($montoMinimo > 0) {
        $sql .= " AND (cxp.monto_total - cxp.monto_pagado) >= ?";
        $params[] = $montoMinimo;
        $types .= "d";
    }

    if ($montoMaximo > 0) {
        $sql .= " AND (cxp.monto_total - cxp.monto_pagado) <= ?";
        $params[] = $montoMaximo;
        $types .= "d";
    }

    if (!empty($diasVencimiento)) {
        $diasActual = "DATEDIFF(?, cxp.fecha)";
        $params[] = $fechaCorte;
        $types .= "s";

        switch ($diasVencimiento) {
            case '1-30':
                $sql .= " AND $diasActual BETWEEN 1 AND 30";
                break;
            case '31-60':
                $sql .= " AND $diasActual BETWEEN 31 AND 60";
                break;
            case '61-90':
                $sql .= " AND $diasActual BETWEEN 61 AND 90";
                break;
            case '91+':
                $sql .= " AND $diasActual > 90";
                break;
        }
    }

    $ordenSql = "";
    switch ($orden) {
        case 'fecha_asc':
            $ordenSql = "cxp.fecha ASC";
            break;
        case 'fecha_desc':
            $ordenSql = "cxp.fecha DESC";
            break;
        case 'monto_asc':
            $ordenSql = "(cxp.monto_total - cxp.monto_pagado) ASC";
            break;
        case 'monto_desc':
            $ordenSql = "(cxp.monto_total - cxp.monto_pagado) DESC";
            break;
        case 'proveedor':
            $ordenSql = "p.razon_social ASC";
            break;
        case 'dias_vencimiento':
            $ordenSql = "dias_transcurridos DESC";
            break;
        default:
            $ordenSql = "cxp.fecha ASC";
    }
    $sql .= " ORDER BY $ordenSql";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Consulta para resumen
    $sqlResumen = "
        SELECT 
            COUNT(DISTINCT cxp.id_proveedor) as total_proveedores,
            SUM(CASE WHEN cxp.estatus = 'pendiente' THEN (cxp.monto_total - cxp.monto_pagado) ELSE 0 END) as total_pendiente,
            SUM(CASE WHEN cxp.estatus = 'vencida' THEN (cxp.monto_total - cxp.monto_pagado) ELSE 0 END) as total_vencido,
            SUM(cxp.monto_pagado) as total_pagado
        FROM 
            cuentas_por_pagar cxp
        WHERE 
            cxp.monto_total > cxp.monto_pagado
    ";

    $paramsResumen = [];
    $typesResumen = "";

    if ($idProveedor > 0) {
        $sqlResumen .= " AND cxp.id_proveedor = ?";
        $paramsResumen[] = $idProveedor;
        $typesResumen .= "i";
    }

    if (!empty($estatus)) {
        $sqlResumen .= " AND cxp.estatus = ?";
        $paramsResumen[] = $estatus;
        $typesResumen .= "s";
    }

    $resumen = $clsConsulta->consultaPreparada($sqlResumen, $paramsResumen, $typesResumen);
    $resumenData = $resumen && count($resumen) > 0 ? $resumen[0] : null;

    $sqlEmpresa = "SELECT razon_social, nombre_comercial, rfc 
                   FROM cat_empresas 
                   WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Cuentas por Pagar');

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

    $sheet->setCellValue('A' . $empresaRow, 'Reporte de Cuentas por Pagar');
    $sheet->mergeCells('A' . $empresaRow . ':H' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Fecha de Corte:');
    $sheet->setCellValue('B' . $filtersRow, $fechaCorte ? date('d/m/Y', strtotime($fechaCorte)) : 'N/A');
    $sheet->setCellValue('D' . $filtersRow, 'Estatus:');
    $sheet->setCellValue('E' . $filtersRow, $estatus ? ucfirst($estatus) : 'Todos');
    $sheet->setCellValue('F' . $filtersRow, 'Días Vencimiento:');
    $sheet->setCellValue('G' . $filtersRow, $diasVencimiento ? str_replace(['1-30', '31-60', '61-90', '91+'], ['1-30 días', '31-60 días', '61-90 días', 'Más de 90 días'], $diasVencimiento) : 'Todos');
    $sheet->setCellValue('H' . $filtersRow, 'Generado: ' . date('d/m/Y H:i:s'));

    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':H' . $filtersRow)->applyFromArray($filterStyle);

    // Resumen
    if ($resumenData) {
        $summaryRow = $filtersRow + 2;
        $sheet->setCellValue('A' . $summaryRow, 'RESUMEN DE CUENTAS POR PAGAR');
        $sheet->mergeCells('A' . $summaryRow . ':H' . $summaryRow);
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Pendiente:');
        $sheet->setCellValue('B' . ($summaryRow + 1), $resumenData['total_pendiente'] ?? 0);
        $sheet->setCellValue('C' . ($summaryRow + 1), 'Total Vencido:');
        $sheet->setCellValue('D' . ($summaryRow + 1), $resumenData['total_vencido'] ?? 0);
        $sheet->setCellValue('E' . ($summaryRow + 1), 'Total Pagado:');
        $sheet->setCellValue('F' . ($summaryRow + 1), $resumenData['total_pagado'] ?? 0);
        $sheet->setCellValue('G' . ($summaryRow + 1), 'Proveedores:');
        $sheet->setCellValue('H' . ($summaryRow + 1), $resumenData['total_proveedores'] ?? 0);

        $summaryStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F6F3']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'font' => ['bold' => true]
        ];
        $sheet->getStyle('A' . ($summaryRow + 1) . ':H' . ($summaryRow + 1))->applyFromArray($summaryStyle);

        $headersRow = $summaryRow + 3;
    } else {
        $headersRow = $filtersRow + 2;
    }

    $headers = ['Proveedor', 'Fecha', 'Compra #', 'Monto Total', 'Monto Pagado', 'Saldo Pendiente', 'Días Vencimiento', 'Estatus'];
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
    $sheet->getStyle('A' . $headersRow . ':H' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    $dataRow = $headersRow + 1;
    $totalMonto = 0;
    $totalPagado = 0;
    $totalSaldo = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $saldoPendiente = $item['monto_total'] - $item['monto_pagado'];
            $fechaCompra = date('d/m/Y', strtotime($item['fecha']));
            $diasTranscurridos = $item['dias_transcurridos'];

            $estatusText = '';
            switch ($item['estatus']) {
                case 'pendiente':
                    $estatusText = 'Pendiente';
                    break;
                case 'parcial':
                    $estatusText = 'Parcial';
                    break;
                case 'pagada':
                    $estatusText = 'Pagada';
                    break;
                case 'vencida':
                    $estatusText = 'Vencida';
                    break;
                default:
                    $estatusText = $item['estatus'];
            }

            $totalMonto += $item['monto_total'];
            $totalPagado += $item['monto_pagado'];
            $totalSaldo += $saldoPendiente;

            $sheet->setCellValue('A' . $dataRow, $item['nombre_proveedor']);
            $sheet->setCellValue('B' . $dataRow, $fechaCompra);
            $sheet->setCellValue('C' . $dataRow, $item['id_compra']);
            $sheet->setCellValue('D' . $dataRow, $item['monto_total']);
            $sheet->setCellValue('E' . $dataRow, $item['monto_pagado']);
            $sheet->setCellValue('F' . $dataRow, $saldoPendiente);
            $sheet->setCellValue('G' . $dataRow, $diasTranscurridos);
            $sheet->setCellValue('H' . $dataRow, $estatusText);

            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':H' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron cuentas por pagar con los filtros seleccionados');
        $sheet->mergeCells('A' . $dataRow . ':H' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    $totalRow = $dataRow;
    $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
    $sheet->mergeCells('A' . $totalRow . ':C' . $totalRow);
    $sheet->setCellValue('D' . $totalRow, $totalMonto);
    $sheet->setCellValue('E' . $totalRow, $totalPagado);
    $sheet->setCellValue('F' . $totalRow, $totalSaldo);

    $totalStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '219653']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray($totalStyle);

    $numberColumns = ['D', 'E', 'F'];
    foreach ($numberColumns as $col) {
        $sheet->getStyle($col . ($headersRow + 1) . ':' . $col . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
    }

    $sheet->getStyle('G' . ($headersRow + 1) . ':G' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    foreach (range('A', 'H') as $column) {
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
    $sheet->getStyle('A' . $headersRow . ':H' . $totalRow)->applyFromArray($tableStyle);

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_cuentas_por_pagar_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
