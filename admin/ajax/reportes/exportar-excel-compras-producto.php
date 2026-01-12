<?php
// ajax/reportes/exportar-excel-compras-producto.php

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
$idProducto = $_GET['id_producto'] ?? 0;
$idProveedor = $_GET['id_proveedor'] ?? 0;
$categoria = $_GET['categoria'] ?? 0;
$orden = $_GET['orden'] ?? 'total_compras';
$direccion = $_GET['direccion'] ?? 'DESC';

$clsConsulta = new Consultas();

try {
    $sql = "
        SELECT 
            p.id_producto,
            p.clave as clave_producto,
            p.nombre as nombre_producto,
            c.nombre_categoria,
            pr.razon_social as nombre_proveedor,
            SUM(mc.cantidad) as cantidad_comprada,
            COALESCE(SUM(mc.cantidad * mc.precio), 0) as total_compras,
            MAX(cc.fecha) as ultima_compra
        FROM 
            cat_productos p
        LEFT JOIN 
            mov_compras mc ON p.id_producto = mc.id_producto
        LEFT JOIN 
            cab_compras cc ON mc.id_orden_compra = cc.id
            AND cc.fecha BETWEEN ? AND ?
        LEFT JOIN 
            cat_categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN 
            cat_proveedores pr ON cc.id_proveedor = pr.id
        WHERE 
            cc.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if ($idProducto > 0) {
        $sql .= " AND p.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if ($idProveedor > 0) {
        $sql .= " AND cc.id_proveedor = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    if ($categoria > 0) {
        $sql .= " AND p.id_categoria = ?";
        $params[] = $categoria;
        $types .= "i";
    }

    $sql .= " 
        GROUP BY 
            p.id_producto, p.clave, p.nombre, c.nombre_categoria, pr.razon_social
        HAVING 
            cantidad_comprada > 0
    ";

    $ordenCampos = [
        'total_compras' => 'total_compras',
        'cantidad_comprada' => 'cantidad_comprada',
        'nombre_producto' => 'p.nombre'
    ];

    $campoOrden = $ordenCampos[$orden] ?? 'total_compras';
    $sql .= " ORDER BY $campoOrden $direccion";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    $sqlEmpresa = "SELECT razon_social, nombre_comercial, rfc 
                   FROM cat_empresas 
                   WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Compras por Producto');

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

    $sheet->setCellValue('A' . $empresaRow, 'Reporte de Compras por Producto');
    $sheet->mergeCells('A' . $empresaRow . ':H' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Período:');
    $sheet->setCellValue('B' . $filtersRow, ($fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'Todos') . ' - ' . ($fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'Todos'));
    $sheet->setCellValue('E' . $filtersRow, 'Ordenado por:');
    $sheet->setCellValue('F' . $filtersRow, ucfirst(str_replace('_', ' ', $orden)) . ' (' . ($direccion == 'DESC' ? 'Descendente' : 'Ascendente') . ')');
    $sheet->setCellValue('H' . $filtersRow, 'Generado: ' . date('d/m/Y H:i:s'));

    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':H' . $filtersRow)->applyFromArray($filterStyle);

    $headersRow = $filtersRow + 2;
    $headers = ['Producto', 'Código', 'Categoría', 'Proveedor', 'Cantidad Comprada', 'Total Compras', 'Precio Promedio', 'Última Compra'];
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
    $totalCompras = 0;
    $totalCantidad = 0;

    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $precioPromedio = $item['cantidad_comprada'] > 0 ? $item['total_compras'] / $item['cantidad_comprada'] : 0;
            $ultimaCompra = $item['ultima_compra'] ? date('d/m/Y', strtotime($item['ultima_compra'])) : 'N/A';

            $totalCompras += $item['total_compras'];
            $totalCantidad += $item['cantidad_comprada'];

            $sheet->setCellValue('A' . $dataRow, $item['nombre_producto']);
            $sheet->setCellValue('B' . $dataRow, $item['clave_producto']);
            $sheet->setCellValue('C' . $dataRow, $item['nombre_categoria'] ?? 'N/A');
            $sheet->setCellValue('D' . $dataRow, $item['nombre_proveedor'] ?? 'N/A');
            $sheet->setCellValue('E' . $dataRow, $item['cantidad_comprada']);
            $sheet->setCellValue('F' . $dataRow, $item['total_compras']);
            $sheet->setCellValue('G' . $dataRow, $precioPromedio);
            $sheet->setCellValue('H' . $dataRow, $ultimaCompra);

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
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron datos con los filtros seleccionados');
        $sheet->mergeCells('A' . $dataRow . ':H' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    $totalRow = $dataRow;
    $precioPromedioGeneral = $totalCantidad > 0 ? $totalCompras / $totalCantidad : 0;

    $sheet->setCellValue('A' . $totalRow, 'TOTALES GENERALES');
    $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
    $sheet->setCellValue('E' . $totalRow, $totalCantidad);
    $sheet->setCellValue('F' . $totalRow, $totalCompras);
    $sheet->setCellValue('G' . $totalRow, $precioPromedioGeneral);

    $totalStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '219653']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray($totalStyle);

    $summaryRow = $totalRow + 2;
    if ($datos && count($datos) > 0) {
        $sheet->setCellValue('A' . $summaryRow, 'RESUMEN EJECUTIVO');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);

        $sheet->setCellValue('A' . ($summaryRow + 1), 'Total de Productos:');
        $sheet->setCellValue('B' . ($summaryRow + 1), count($datos));

        $sheet->setCellValue('A' . ($summaryRow + 2), 'Cantidad Total Comprada:');
        $sheet->setCellValue('B' . ($summaryRow + 2), $totalCantidad);

        $sheet->setCellValue('A' . ($summaryRow + 3), 'Compras Totales:');
        $sheet->setCellValue('B' . ($summaryRow + 3), $totalCompras);

        $sheet->setCellValue('A' . ($summaryRow + 4), 'Precio Promedio:');
        $sheet->setCellValue('B' . ($summaryRow + 4), $precioPromedioGeneral);

        $summaryStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F6F3']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $summaryRow . ':B' . ($summaryRow + 4))->applyFromArray($summaryStyle);
    }

    $numberColumns = ['F', 'G'];
    foreach ($numberColumns as $col) {
        $sheet->getStyle($col . ($headersRow + 1) . ':' . $col . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
    }

    $sheet->getStyle('E' . ($headersRow + 1) . ':E' . $totalRow)
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
    header('Content-Disposition: attachment;filename="reporte_compras_producto_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
