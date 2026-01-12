<?php
// admin/ajax/reportes/exportar-excel-ventas-periodo.php
require_once '../../vendor/autoload.php';
require_once '../../lib/clsConsultas.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ventas_por_periodo_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

try {
    $clsConsulta = new Consultas();

    // Obtener parámetros
    $fechaInicio = $_POST['fechaInicio'] ?? date('Y-m-01');
    $fechaFin = $_POST['fechaFin'] ?? date('Y-m-t');
    $vendedor = $_POST['vendedor'] ?? '';
    $cliente = $_POST['cliente'] ?? '';
    $orden = $_POST['orden'] ?? 'DESC';

    // Construir consulta
    $sql = "SELECT 
                f.id_factura,
                f.numero_factura,
                f.fecha_emision,
                c.nombre as cliente,
                v.nombre as vendedor,
                f.subtotal,
                f.impuestos,
                f.descuentos,
                f.total,
                f.estado,
                f.tipo_pago
            FROM facturas f
            INNER JOIN clientes c ON f.id_cliente = c.id_cliente
            INNER JOIN vendedores v ON f.id_vendedor = v.id_vendedor
            WHERE f.fecha_emision BETWEEN ? AND ?";

    $params = [$fechaInicio, $fechaFin];
    $types = "ss";

    if (!empty($vendedor)) {
        $sql .= " AND f.id_vendedor = ?";
        $params[] = $vendedor;
        $types .= "i";
    }

    if (!empty($cliente)) {
        $sql .= " AND f.id_cliente = ?";
        $params[] = $cliente;
        $types .= "i";
    }

    $sql .= " ORDER BY f.fecha_emision $orden";

    $resultados = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Logo de la empresa
    $logoPath = '../../img/logo-inicio.png';
    if (file_exists($logoPath)) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($logoPath);
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);
    }

    // Título y información
    $sheet->setCellValue('C1', 'REPORTE DE VENTAS POR PERIODO');
    $sheet->mergeCells('C1:H1');
    $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('C2', 'Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)));
    $sheet->mergeCells('C2:H2');
    $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('C3', 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('C3:H3');
    $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Encabezados de columnas
    $headers = [
        'A5' => 'N° Factura',
        'B5' => 'Fecha Emisión',
        'C5' => 'Cliente',
        'D5' => 'Vendedor',
        'E5' => 'Subtotal',
        'F5' => 'Impuestos',
        'G5' => 'Descuentos',
        'H5' => 'Total',
        'I5' => 'Estado',
        'J5' => 'Tipo Pago'
    ];

    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3498DB']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];

    $sheet->getStyle('A5:J5')->applyFromArray($headerStyle);

    // Datos
    $row = 6;
    $totalSubtotal = 0;
    $totalImpuestos = 0;
    $totalDescuentos = 0;
    $totalGeneral = 0;

    if ($resultados && $clsConsulta->numrows > 0) {
        foreach ($resultados as $index => $factura) {
            $sheet->setCellValue('A' . $row, $factura[2]); // numero_factura
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($factura[3]))); // fecha_emision
            $sheet->setCellValue('C' . $row, $factura[4]); // cliente
            $sheet->setCellValue('D' . $row, $factura[5]); // vendedor
            $sheet->setCellValue('E' . $row, $factura[6]); // subtotal
            $sheet->setCellValue('F' . $row, $factura[7]); // impuestos
            $sheet->setCellValue('G' . $row, $factura[8]); // descuentos
            $sheet->setCellValue('H' . $row, $factura[9]); // total
            $sheet->setCellValue('I' . $row, $factura[10]); // estado
            $sheet->setCellValue('J' . $row, $factura[11]); // tipo_pago

            // Formato numérico
            $sheet->getStyle('E' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $totalSubtotal += floatval($factura[6]);
            $totalImpuestos += floatval($factura[7]);
            $totalDescuentos += floatval($factura[8]);
            $totalGeneral += floatval($factura[9]);

            $row++;
        }

        // Totales
        $sheet->setCellValue('D' . $row, 'TOTALES:');
        $sheet->setCellValue('E' . $row, $totalSubtotal);
        $sheet->setCellValue('F' . $row, $totalImpuestos);
        $sheet->setCellValue('G' . $row, $totalDescuentos);
        $sheet->setCellValue('H' . $row, $totalGeneral);

        // Estilo para totales
        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]]
        ];

        $sheet->getStyle('D' . $row . ':H' . $row)->applyFromArray($totalStyle);
        $sheet->getStyle('E' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Autoajustar columnas
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    } else {
        $sheet->setCellValue('A6', 'No se encontraron registros para el período seleccionado');
        $sheet->mergeCells('A6:J6');
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Bordes para toda la tabla
    $lastRow = $row;
    $tableStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];

    $sheet->getStyle('A5:J' . $lastRow)->applyFromArray($tableStyle);

    // Proteger hoja
    $sheet->getProtection()->setSheet(true);
    $sheet->getStyle('A1:J' . $lastRow)->getProtection()->setLocked(false);

    // Guardar archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    // En caso de error, crear archivo con mensaje de error
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Error al generar el reporte');
    $sheet->setCellValue('A2', $e->getMessage());

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

exit;
