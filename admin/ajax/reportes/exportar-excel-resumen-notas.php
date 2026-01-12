<?php
// ajax/reportes/exportar-excel-resumen-notas.php

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
$tipoVenta = $_GET['tipo_venta'] ?? '';
$estatus = $_GET['estatus'] ?? '';
$idVendedor = $_GET['id_vendedor'] ?? 0;
$idCliente = $_GET['id_cliente'] ?? 0;
$idAlmacen = $_GET['id_almacen'] ?? 0;

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta corregida
    $sql = "
        SELECT 
            cr.id,
            cr.fecha,
            cr.total,
            cr.estatus,
            cr.tipo_venta,
            cc.razon_social AS cliente,
            CONCAT(cv.nombre, ' ', cv.apellido1, ' ', COALESCE(cv.apellido2, '')) AS vendedor,
            ca.almacen,
            (SELECT COUNT(*) FROM mov_remisiones mr WHERE mr.id_remision = cr.id) AS total_productos
        FROM 
            cab_remisiones cr
        LEFT JOIN 
            cat_clientes cc ON cr.id_cliente = cc.id
        LEFT JOIN 
            cat_vendedores cv ON cr.id_vendedor = cv.id
        LEFT JOIN 
            cat_almacenes ca ON cr.id_almacen = ca.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if (!empty($estatus)) {
        $sql .= " AND cr.estatus = ?";
        $params[] = $estatus;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idCliente > 0) {
        $sql .= " AND cr.id_cliente = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " ORDER BY cr.fecha DESC, cr.id DESC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial FROM cat_empresas WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Resumen Notas Venta');

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
    $sheet->setCellValue('A' . $empresaRow, 'Resumen de Notas de Venta');
    $sheet->mergeCells('A' . $empresaRow . ':J' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos'));
    $sheet->setCellValue('E' . $filtersRow, 'Tipo Venta:');
    $sheet->setCellValue('F' . $filtersRow, $tipoVenta ? ucfirst($tipoVenta) : 'Todos');
    $sheet->setCellValue('H' . $filtersRow, 'Estatus:');
    $sheet->setCellValue('I' . $filtersRow, $estatus ? ucfirst($estatus) : 'Todos');

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':J' . $filtersRow)->applyFromArray($filterStyle);

    // Encabezados de tabla
    $headersRow = $filtersRow + 2;
    $headers = ['Folio', 'Fecha', 'Cliente', 'Vendedor', 'Almacén', 'Tipo Venta', 'Productos', 'Subtotal*', 'Total', 'Estatus'];
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
    $totalGeneral = 0;
    $totalProductos = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $fechaFormateada = $item['fecha'] ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A';

            // Calcular subtotal estimado (sin IVA) - asumiendo 16% de IVA
            $subtotalEstimado = floatval($item['total']) / 1.16;

            $totalGeneral += floatval($item['total']);
            $totalProductos += intval($item['total_productos']);

            $sheet->setCellValue('A' . $dataRow, '#' . $item['id']);
            $sheet->setCellValue('B' . $dataRow, $fechaFormateada);
            $sheet->setCellValue('C' . $dataRow, $item['cliente'] ?? 'N/A');
            $sheet->setCellValue('D' . $dataRow, $item['vendedor'] ?? 'N/A');
            $sheet->setCellValue('E' . $dataRow, $item['almacen'] ?? 'N/A');
            $sheet->setCellValue('F' . $dataRow, $item['tipo_venta'] === 'credito' ? 'Crédito' : 'Contado');
            $sheet->setCellValue('G' . $dataRow, $item['total_productos']);
            $sheet->setCellValue('H' . $dataRow, $subtotalEstimado);
            $sheet->setCellValue('I' . $dataRow, $item['total']);
            $sheet->setCellValue('J' . $dataRow, $item['estatus']);

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
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron notas de venta con los filtros seleccionados');
        $sheet->mergeCells('A' . $dataRow . ':J' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales
    $totalRow = $dataRow;
    $subtotalTotalEstimado = $totalGeneral / 1.16;

    $sheet->setCellValue('A' . $totalRow, 'TOTALES');
    $sheet->setCellValue('G' . $totalRow, $totalProductos);
    $sheet->setCellValue('H' . $totalRow, $subtotalTotalEstimado);
    $sheet->setCellValue('I' . $totalRow, $totalGeneral);

    // Estilo para totales
    $totalStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->applyFromArray($totalStyle);

    // Nota sobre el subtotal estimado
    $noteRow = $totalRow + 1;
    $sheet->setCellValue('A' . $noteRow, '* El subtotal es un cálculo estimado asumiendo un IVA del 16%');
    $sheet->mergeCells('A' . $noteRow . ':J' . $noteRow);
    $sheet->getStyle('A' . $noteRow)->getFont()->setItalic(true)->setSize(9);
    $sheet->getStyle('A' . $noteRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    // Formato de números
    $sheet->getStyle('H' . ($headersRow + 1) . ':I' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');

    // Formato para cantidad
    $sheet->getStyle('G' . ($headersRow + 1) . ':G' . $totalRow)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    // Autoajustar columnas
    foreach (range('A', 'J') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="resumen_notas_venta_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
