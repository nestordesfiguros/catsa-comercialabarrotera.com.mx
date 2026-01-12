<?php
// ajax/reportes/exportar-excel-detalle-facturas.php

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
$estatusFactura = $_GET['estatus_factura'] ?? '';
$timbrada = $_GET['timbrada'] ?? '';
$idCliente = $_GET['id_cliente'] ?? 0;
$serie = $_GET['serie'] ?? '';
$folio = $_GET['folio'] ?? '';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del endpoint AJAX
    $sql = "
        SELECT 
            cf.id,
            cf.serie,
            cf.folio,
            cf.fecha,
            cf.subtotal,
            cf.total,
            cf.estatus,
            cf.timbrada,
            cf.uuid,
            cc.razon_social,
            cc.rfc
        FROM 
            cab_facturas cf
        LEFT JOIN 
            cat_clientes cc ON cf.id_receptor = cc.id
        WHERE 
            cf.fecha BETWEEN ? AND ?
            AND cf.activo = 1
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if (!empty($estatusFactura)) {
        $sql .= " AND cf.estatus = ?";
        $params[] = $estatusFactura;
        $types .= "s";
    }

    if ($timbrada !== '') {
        $sql .= " AND cf.timbrada = ?";
        $params[] = $timbrada;
        $types .= "i";
    }

    if ($idCliente > 0) {
        $sql .= " AND cf.id_receptor = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if (!empty($serie)) {
        $sql .= " AND cf.serie LIKE ?";
        $params[] = "%$serie%";
        $types .= "s";
    }

    if (!empty($folio)) {
        $sql .= " AND cf.folio LIKE ?";
        $params[] = "%$folio%";
        $types .= "s";
    }

    $sql .= " ORDER BY cf.fecha DESC, cf.id DESC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial FROM cat_empresas WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Detalle Facturas');

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
        $sheet->mergeCells('C1:J1');
        $sheet->mergeCells('C2:J2');
        $sheet->getStyle('C1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $empresaRow = 3;
    }

    // Título del reporte
    $sheet->setCellValue('A' . $empresaRow, 'Detallado de Facturas');
    $sheet->mergeCells('A' . $empresaRow . ':J' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos'));
    $sheet->setCellValue('E' . $filtersRow, 'Estatus:');
    $sheet->setCellValue('F' . $filtersRow, $estatusFactura ? ucfirst(strtolower($estatusFactura)) : 'Todos');
    $sheet->setCellValue('H' . $filtersRow, 'Timbrado:');
    $sheet->setCellValue('I' . $filtersRow, $timbrada !== '' ? ($timbrada ? 'Sí' : 'No') : 'Todos');

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':J' . $filtersRow)->applyFromArray($filterStyle);

    // Encabezados de tabla
    $headersRow = $filtersRow + 2;
    $headers = ['Folio', 'Fecha', 'Cliente', 'RFC', 'Subtotal', 'IVA', 'Total', 'Estatus', 'Timbrado', 'UUID'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headersRow, $header);
        $col++;
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3498DB']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $headersRow . ':J' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    $totalSubtotal = 0;
    $totalIva = 0;
    $totalGeneral = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $iva = floatval($item['total']) - floatval($item['subtotal']);
            $folioCompleto = $item['serie'] && $item['folio'] ? $item['serie'] . '-' . $item['folio'] : 'N/A';
            $fechaFormateada = $item['fecha'] ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A';

            $totalSubtotal += floatval($item['subtotal']);
            $totalIva += $iva;
            $totalGeneral += floatval($item['total']);

            $sheet->setCellValue('A' . $dataRow, $folioCompleto);
            $sheet->setCellValue('B' . $dataRow, $fechaFormateada);
            $sheet->setCellValue('C' . $dataRow, $item['razon_social'] ?? 'N/A');
            $sheet->setCellValue('D' . $dataRow, $item['rfc'] ?? 'N/A');
            $sheet->setCellValue('E' . $dataRow, $item['subtotal']);
            $sheet->setCellValue('F' . $dataRow, $iva);
            $sheet->setCellValue('G' . $dataRow, $item['total']);
            $sheet->setCellValue('H' . $dataRow, $item['estatus']);
            $sheet->setCellValue('I' . $dataRow, $item['timbrada'] ? 'Sí' : 'No');
            $sheet->setCellValue('J' . $dataRow, $item['uuid'] ?? 'N/A');

            // Alternar colores de fila
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron facturas con los filtros seleccionados');
        $sheet->mergeCells('A' . $dataRow . ':J' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales
    $totalRow = $dataRow;
    $sheet->setCellValue('A' . $totalRow, 'TOTALES');
    $sheet->setCellValue('E' . $totalRow, $totalSubtotal);
    $sheet->setCellValue('F' . $totalRow, $totalIva);
    $sheet->setCellValue('G' . $totalRow, $totalGeneral);

    // Estilo para totales
    $totalStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->applyFromArray($totalStyle);

    // Formato de números
    $sheet->getStyle('E' . ($headersRow + 1) . ':G' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');

    // Autoajustar columnas
    foreach (range('A', 'J') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="detalle_facturas_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
