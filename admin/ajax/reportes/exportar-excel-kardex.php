<?php
// ajax/reportes/exportar-excel-kardex.php

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
$idProducto = $_GET['id_producto'] ?? 0;
$idAlmacen = $_GET['id_almacen'] ?? 0;
$tipoMovimiento = $_GET['tipo_movimiento'] ?? '';
$referencia = $_GET['referencia'] ?? '';
$agruparPor = $_GET['agrupar_por'] ?? 'producto';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del PDF
    $sql = "
        SELECT 
            ib.fecha,
            cp.clave,
            cp.nombre AS producto,
            ca.almacen,
            ib.tipo_movimiento,
            ib.referencia,
            CASE 
                WHEN ib.tipo_movimiento LIKE '%entrada%' OR ib.tipo_movimiento LIKE '%compra%' THEN ib.cantidad
                ELSE 0 
            END AS entrada,
            CASE 
                WHEN ib.tipo_movimiento LIKE '%salida%' OR ib.tipo_movimiento LIKE '%venta%' THEN ib.cantidad
                ELSE 0 
            END AS salida,
            ib.cantidad AS movimiento,
            (SELECT precio_compra FROM cat_productos WHERE id_producto = ib.id_producto) AS costo_unitario,
            (ib.cantidad * (SELECT precio_compra FROM cat_productos WHERE id_producto = ib.id_producto)) AS costo_total,
            u.nombre AS usuario
        FROM 
            inventario_bitacora ib
        INNER JOIN 
            cat_productos cp ON ib.id_producto = cp.id_producto
        LEFT JOIN 
            cat_almacenes ca ON ib.id_almacen = ca.id
        LEFT JOIN 
            usuarios u ON ib.id_usuario = u.id
        WHERE 
            ib.fecha BETWEEN ? AND ?
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    // Aplicar filtros
    if ($idProducto > 0) {
        $sql .= " AND ib.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND ib.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if (!empty($tipoMovimiento)) {
        $sql .= " AND ib.tipo_movimiento LIKE ?";
        $params[] = "%$tipoMovimiento%";
        $types .= "s";
    }

    if (!empty($referencia)) {
        $sql .= " AND ib.referencia LIKE ?";
        $params[] = "%$referencia%";
        $types .= "s";
    }

    $sql .= " ORDER BY ib.fecha DESC, ib.id DESC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Calcular totales
    $totalEntradas = 0;
    $totalSalidas = 0;
    $totalCosto = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $totalEntradas += floatval($item['entrada']);
            $totalSalidas += floatval($item['salida']);
            $totalCosto += floatval($item['costo_total']);
        }
    }

    $saldoFinal = $totalEntradas - $totalSalidas;

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial FROM cat_empresas WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Kárdex Inventario');

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
        $sheet->mergeCells('C1:K1');
        $sheet->mergeCells('C2:K2');
        $sheet->getStyle('C1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $empresaRow = 3;
    }

    // Título del reporte
    $sheet->setCellValue('A' . $empresaRow, 'Kárdex - Movimientos de Inventario');
    $sheet->mergeCells('A' . $empresaRow . ':K' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)));
    $sheet->setCellValue('D' . $filtersRow, 'Producto:');
    $sheet->setCellValue('E' . $filtersRow, $idProducto ? 'Específico' : 'Todos');
    $sheet->setCellValue('F' . $filtersRow, 'Almacén:');
    $sheet->setCellValue('G' . $filtersRow, $idAlmacen ? 'Específico' : 'Todos');
    $sheet->setCellValue('H' . $filtersRow, 'Tipo Mov:');
    $sheet->setCellValue('I' . $filtersRow, $tipoMovimiento ? ucfirst($tipoMovimiento) : 'Todos');

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':K' . $filtersRow)->applyFromArray($filterStyle);

    // Resumen
    $resumenRow = $filtersRow + 2;
    $sheet->setCellValue('A' . $resumenRow, 'RESUMEN');
    $sheet->mergeCells('A' . $resumenRow . ':K' . $resumenRow);
    $sheet->getStyle('A' . $resumenRow)->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A' . $resumenRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6EAF8');

    $resumenRow++;
    $sheet->setCellValue('A' . $resumenRow, 'Total Entradas:');
    $sheet->setCellValue('B' . $resumenRow, $totalEntradas);
    $sheet->setCellValue('C' . $resumenRow, 'Total Salidas:');
    $sheet->setCellValue('D' . $resumenRow, $totalSalidas);
    $sheet->setCellValue('E' . $resumenRow, 'Saldo Final:');
    $sheet->setCellValue('F' . $resumenRow, $saldoFinal);
    $sheet->setCellValue('G' . $resumenRow, 'Total Movimientos:');
    $sheet->setCellValue('H' . $resumenRow, count($datos));
    $sheet->setCellValue('I' . $resumenRow, 'Costo Total:');
    $sheet->setCellValue('J' . $resumenRow, $totalCosto);

    // Encabezados de tabla
    $headersRow = $resumenRow + 2;
    $headers = ['Fecha', 'Clave', 'Producto', 'Almacén', 'Tipo Movimiento', 'Referencia', 'Entrada', 'Salida', 'Saldo', 'Costo Unitario', 'Costo Total', 'Usuario'];

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
    $sheet->getStyle('A' . $headersRow . ':L' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $fechaFormateada = $item['fecha'] ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A';
            $entrada = floatval($item['entrada']);
            $salida = floatval($item['salida']);
            $saldo = $entrada - $salida;

            $sheet->setCellValue('A' . $dataRow, $fechaFormateada);
            $sheet->setCellValue('B' . $dataRow, $item['clave']);
            $sheet->setCellValue('C' . $dataRow, $item['producto']);
            $sheet->setCellValue('D' . $dataRow, $item['almacen'] ?? 'N/A');
            $sheet->setCellValue('E' . $dataRow, ucfirst($item['tipo_movimiento']));
            $sheet->setCellValue('F' . $dataRow, $item['referencia'] ?? 'N/A');
            $sheet->setCellValue('G' . $dataRow, $entrada > 0 ? $entrada : '');
            $sheet->setCellValue('H' . $dataRow, $salida > 0 ? $salida : '');
            $sheet->setCellValue('I' . $dataRow, $saldo);
            $sheet->setCellValue('J' . $dataRow, $item['costo_unitario'] ?? 0);
            $sheet->setCellValue('K' . $dataRow, $item['costo_total'] ?? 0);
            $sheet->setCellValue('L' . $dataRow, $item['usuario'] ?? 'Sistema');

            // Alternar colores de fila
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':L' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            // Color para entradas (verde claro) y salidas (rojo claro)
            if ($entrada > 0) {
                $sheet->getStyle('G' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('D5F4E6');
            } elseif ($salida > 0) {
                $sheet->getStyle('H' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F4D5D5');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron movimientos de inventario');
        $sheet->mergeCells('A' . $dataRow . ':L' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales
    if ($datos && count($datos) > 0) {
        $totalRow = $dataRow;
        $sheet->setCellValue('A' . $totalRow, 'TOTALES');
        $sheet->mergeCells('A' . $totalRow . ':F' . $totalRow);
        $sheet->setCellValue('G' . $totalRow, $totalEntradas);
        $sheet->setCellValue('H' . $totalRow, $totalSalidas);
        $sheet->setCellValue('I' . $totalRow, $saldoFinal);
        $sheet->setCellValue('J' . $totalRow, '');
        $sheet->setCellValue('K' . $totalRow, $totalCosto);
        $sheet->setCellValue('L' . $totalRow, '');

        // Estilo para totales
        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $totalRow . ':L' . $totalRow)->applyFromArray($totalStyle);
    }

    // Formato de números
    $sheet->getStyle('G' . ($headersRow + 1) . ':I' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    $sheet->getStyle('J' . ($headersRow + 1) . ':K' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');

    // Autoajustar columnas
    foreach (range('A', 'L') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Centrar algunas columnas
    $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('G:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('J:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="kardex_inventario_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
