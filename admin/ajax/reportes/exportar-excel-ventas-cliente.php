<?php
// ajax/reportes/exportar-excel-ventas-cliente.php

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
$idCliente = $_GET['id_cliente'] ?? 0;
$idVendedor = $_GET['id_vendedor'] ?? 0;
$tipoVenta = $_GET['tipo_venta'] ?? '';
$orden = $_GET['orden'] ?? 'total_ventas';
$direccion = $_GET['direccion'] ?? 'DESC';
$limite = $_GET['limite'] ?? 0;

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta
    $sql = "
        SELECT 
            c.id,
            c.no_cliente,
            COALESCE(c.nombre_comercial, c.razon_social) AS nombre_cliente,
            CONCAT(v.nombre, ' ', v.apellido1, ' ', COALESCE(v.apellido2, '')) AS nombre_vendedor,
            cr.tipo_venta,
            COUNT(DISTINCT cr.id) AS cantidad_compras,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            COALESCE(MIN(cr.total), 0) AS compra_minima,
            COALESCE(MAX(cr.total), 0) AS compra_maxima,
            MAX(cr.fecha) AS ultima_compra
        FROM 
            cat_clientes c
        LEFT JOIN 
            cab_remisiones cr ON c.id = cr.id_cliente
        LEFT JOIN 
            cat_vendedores v ON cr.id_vendedor = v.id
        WHERE 
            cr.fecha BETWEEN ? AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idCliente > 0) {
        $sql .= " AND c.id = ?";
        $params[] = $idCliente;
        $types .= "i";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    $sql .= " GROUP BY c.id, c.no_cliente, c.razon_social, c.nombre_comercial, v.nombre, v.apellido1, v.apellido2, cr.tipo_venta";

    $ordenValido = in_array($orden, ['total_ventas', 'cantidad_compras', 'nombre_cliente', 'ticket_promedio']) ? $orden : 'total_ventas';
    $direccionValida = strtoupper($direccion) === 'ASC' ? 'ASC' : 'DESC';

    if ($ordenValido === 'ticket_promedio') {
        $sql .= " ORDER BY (total_ventas / NULLIF(cantidad_compras, 0)) $direccionValida";
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
    $sheet->setTitle('Ventas por Cliente');

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
    $sheet->setCellValue('A' . $empresaRow, 'Reporte de Ventas por Cliente');
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
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':I' . $filtersRow)->applyFromArray($filterStyle);

    // Encabezados de tabla
    $headersRow = $filtersRow + 2;
    $headers = ['Cliente', 'Vendedor', 'Tipo Venta', 'Cantidad Compras', 'Ticket Promedio', 'Compra Mínima', 'Compra Máxima', 'Total Ventas', 'Última Compra'];
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
    $sheet->getStyle('A' . $headersRow . ':I' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    $totalVentas = 0;
    $totalCompras = 0;
    $sumaTickets = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $ticketPromedio = $item['cantidad_compras'] > 0 ? $item['total_ventas'] / $item['cantidad_compras'] : 0;

            $totalVentas += $item['total_ventas'];
            $totalCompras += $item['cantidad_compras'];
            $sumaTickets += $ticketPromedio;

            $sheet->setCellValue('A' . $dataRow, $item['no_cliente'] . ' - ' . $item['nombre_cliente']);
            $sheet->setCellValue('B' . $dataRow, $item['nombre_vendedor'] ?? 'N/A');
            $sheet->setCellValue('C' . $dataRow, $item['tipo_venta'] === 'credito' ? 'Crédito' : 'Contado');
            $sheet->setCellValue('D' . $dataRow, $item['cantidad_compras']);
            $sheet->setCellValue('E' . $dataRow, $ticketPromedio);
            $sheet->setCellValue('F' . $dataRow, $item['compra_minima']);
            $sheet->setCellValue('G' . $dataRow, $item['compra_maxima']);
            $sheet->setCellValue('H' . $dataRow, $item['total_ventas']);
            $sheet->setCellValue('I' . $dataRow, $item['ultima_compra'] ? date('d/m/Y', strtotime($item['ultima_compra'])) : 'N/A');

            // Alternar colores de fila
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':I' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
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
    $ticketPromedioGeneral = $totalCompras > 0 ? $totalVentas / $totalCompras : 0;

    $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
    $sheet->setCellValue('D' . $totalRow, $totalCompras);
    $sheet->setCellValue('E' . $totalRow, $ticketPromedioGeneral);
    $sheet->setCellValue('H' . $totalRow, $totalVentas);

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
    header('Content-Disposition: attachment;filename="reporte_ventas_cliente_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
