<?php
// ajax/reportes/exportar-excel-inventario-fisico.php

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
$fechaConteo = $_GET['fecha_conteo'] ?? date('Y-m-d');
$idAlmacen = $_GET['id_almacen'] ?? 0;
$idProducto = $_GET['id_producto'] ?? 0;
$estatusConteo = $_GET['estatus_conteo'] ?? '';
$responsable = $_GET['responsable'] ?? '';
$tipoReporte = $_GET['tipo_reporte'] ?? 'detallado';
$ordenarPor = $_GET['ordenar_por'] ?? 'producto';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del PDF
    $sql = "
        SELECT 
            ic.fecha_conteo AS fecha,
            cp.clave,
            cp.nombre AS producto,
            ca.almacen,
            COALESCE(i.cantidad, 0) AS existencia_sistema,
            ic.cantidad AS conteo_fisico,
            (ic.cantidad - COALESCE(i.cantidad, 0)) AS diferencia,
            CASE 
                WHEN COALESCE(i.cantidad, 0) > 0 THEN 
                    ABS((ic.cantidad - COALESCE(i.cantidad, 0)) / COALESCE(i.cantidad, 0)) * 100 
                ELSE 100 
            END AS porcentaje_diferencia,
            cp.precio_compra AS costo_unitario,
            (ABS(ic.cantidad - COALESCE(i.cantidad, 0)) * cp.precio_compra) AS valor_diferencia,
            ic.responsable,
            CASE 
                WHEN ic.aplicado = 1 THEN 'aplicado' 
                ELSE 'pendiente' 
            END AS estatus,
            ic.observaciones,
            ic.fecha_conteo_d
        FROM 
            inv_conteos ic
        INNER JOIN 
            cat_productos cp ON ic.id_producto = cp.id_producto
        LEFT JOIN 
            inventarios i ON ic.id_producto = i.id_producto AND ic.id_almacen = i.id_almacen
        LEFT JOIN 
            cat_almacenes ca ON ic.id_almacen = ca.id
        WHERE 
            ic.fecha_conteo_d = ?
    ";

    $params = [$fechaConteo];
    $types = "s";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND ic.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idProducto > 0) {
        $sql .= " AND ic.id_producto = ?";
        $params[] = $idProducto;
        $types .= "i";
    }

    if (!empty($estatusConteo)) {
        if ($estatusConteo === 'aplicado') {
            $sql .= " AND ic.aplicado = 1";
        } elseif ($estatusConteo === 'pendiente') {
            $sql .= " AND ic.aplicado = 0";
        } elseif ($estatusConteo === 'con_diferencias') {
            $sql .= " AND ic.cantidad != COALESCE(i.cantidad, 0)";
        }
    }

    if (!empty($responsable)) {
        $sql .= " AND ic.responsable LIKE ?";
        $params[] = "%$responsable%";
        $types .= "s";
    }

    // Ordenamiento
    $orderColumn = 'cp.nombre';
    switch ($ordenarPor) {
        case 'diferencia':
            $orderColumn = 'ABS(diferencia)';
            break;
        case 'fecha':
            $orderColumn = 'ic.fecha_conteo';
            break;
        case 'almacen':
            $orderColumn = 'ca.almacen';
            break;
    }

    $sql .= " ORDER BY $orderColumn DESC, cp.nombre ASC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Calcular totales
    $totalProductos = 0;
    $conDiferencias = 0;
    $totalDiferencias = 0;
    $totalValorDiferencias = 0;
    $totalSistema = 0;
    $totalFisico = 0;

    if ($datos && count($datos) > 0) {
        $totalProductos = count($datos);
        foreach ($datos as $item) {
            $diferencia = intval($item['diferencia']);
            $valorDiferencia = floatval($item['valor_diferencia']);
            $sistema = intval($item['existencia_sistema']);
            $fisico = intval($item['conteo_fisico']);

            $totalSistema += $sistema;
            $totalFisico += $fisico;
            $totalDiferencias += abs($diferencia);
            $totalValorDiferencias += $valorDiferencia;

            if ($diferencia !== 0) {
                $conDiferencias++;
            }
        }
    }

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial FROM cat_empresas WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Inventario Físico');

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
    $sheet->setCellValue('A' . $empresaRow, 'Inventario Físico - Conteos y Ajustes');
    $sheet->mergeCells('A' . $empresaRow . ':K' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Fecha Conteo:');
    $sheet->setCellValue('B' . $filtersRow, date('d/m/Y', strtotime($fechaConteo)));
    $sheet->setCellValue('C' . $filtersRow, 'Almacén:');
    $sheet->setCellValue('D' . $filtersRow, $idAlmacen ? 'Específico' : 'Todos');
    $sheet->setCellValue('E' . $filtersRow, 'Producto:');
    $sheet->setCellValue('F' . $filtersRow, $idProducto ? 'Específico' : 'Todos');
    $sheet->setCellValue('G' . $filtersRow, 'Estatus:');
    $sheet->setCellValue('H' . $filtersRow, $estatusConteo ? ucfirst(str_replace('_', ' ', $estatusConteo)) : 'Todos');
    $sheet->setCellValue('I' . $filtersRow, 'Tipo Reporte:');
    $sheet->setCellValue('J' . $filtersRow, ucfirst($tipoReporte));

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':K' . $filtersRow)->applyFromArray($filterStyle);

    // Resumen
    $resumenRow = $filtersRow + 2;
    $sheet->setCellValue('A' . $resumenRow, 'RESUMEN DEL CONTEO');
    $sheet->mergeCells('A' . $resumenRow . ':K' . $resumenRow);
    $sheet->getStyle('A' . $resumenRow)->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A' . $resumenRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D1ECF1');

    $resumenRow++;
    $sheet->setCellValue('A' . $resumenRow, 'Total Productos Contados:');
    $sheet->setCellValue('B' . $resumenRow, $totalProductos);
    $sheet->setCellValue('C' . $resumenRow, 'Con Diferencias:');
    $sheet->setCellValue('D' . $resumenRow, $conDiferencias);
    $sheet->setCellValue('E' . $resumenRow, 'Total Diferencias:');
    $sheet->setCellValue('F' . $resumenRow, $totalDiferencias);
    $sheet->setCellValue('G' . $resumenRow, 'Valor Diferencias:');
    $sheet->setCellValue('H' . $resumenRow, $totalValorDiferencias);
    $sheet->setCellValue('I' . $resumenRow, 'Total Sistema:');
    $sheet->setCellValue('J' . $resumenRow, $totalSistema);
    $sheet->setCellValue('K' . $resumenRow, 'Total Físico:');
    $sheet->setCellValue('L' . $resumenRow, $totalFisico);

    // Encabezados de tabla
    $headersRow = $resumenRow + 2;
    $headers = ['Fecha', 'Clave', 'Producto', 'Almacén', 'Sistema', 'Físico', 'Diferencia', '% Diferencia', 'Costo Unitario', 'Valor Diferencia', 'Responsable', 'Estatus', 'Observaciones'];

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headersRow, $header);
        $col++;
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC107']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $headersRow . ':M' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $diferencia = intval($item['diferencia']);
            $porcentajeDiferencia = floatval($item['porcentaje_diferencia']);

            $sheet->setCellValue('A' . $dataRow, date('d/m/Y', strtotime($item['fecha'])));
            $sheet->setCellValue('B' . $dataRow, $item['clave']);
            $sheet->setCellValue('C' . $dataRow, $item['producto']);
            $sheet->setCellValue('D' . $dataRow, $item['almacen'] ?? 'N/A');
            $sheet->setCellValue('E' . $dataRow, $item['existencia_sistema']);
            $sheet->setCellValue('F' . $dataRow, $item['conteo_fisico']);
            $sheet->setCellValue('G' . $dataRow, $diferencia);
            $sheet->setCellValue('H' . $dataRow, $porcentajeDiferencia / 100); // Formato porcentaje
            $sheet->setCellValue('I' . $dataRow, $item['costo_unitario'] ?? 0);
            $sheet->setCellValue('J' . $dataRow, $item['valor_diferencia'] ?? 0);
            $sheet->setCellValue('K' . $dataRow, $item['responsable'] ?? 'N/A');
            $sheet->setCellValue('L' . $dataRow, $item['estatus'] === 'aplicado' ? 'Aplicado' : 'Pendiente');
            $sheet->setCellValue('M' . $dataRow, $item['observaciones'] ?? '');

            // Alternar colores de fila
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':M' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            // Color para diferencia
            if ($diferencia > 0) {
                $sheet->getStyle('G' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('D4EDDA');
            } elseif ($diferencia < 0) {
                $sheet->getStyle('G' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8D7DA');
            }

            // Color para estatus
            if ($item['estatus'] === 'aplicado') {
                $sheet->getStyle('L' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('28A745');
                $sheet->getStyle('L' . $dataRow)->getFont()->getColor()->setARGB('FFFFFF');
            } else {
                $sheet->getStyle('L' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFC107');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron conteos físicos');
        $sheet->mergeCells('A' . $dataRow . ':M' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales
    if ($datos && count($datos) > 0) {
        $totalRow = $dataRow;
        $sheet->setCellValue('A' . $totalRow, 'TOTALES');
        $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
        $sheet->setCellValue('E' . $totalRow, $totalSistema);
        $sheet->setCellValue('F' . $totalRow, $totalFisico);
        $sheet->setCellValue('G' . $totalRow, $totalFisico - $totalSistema);
        $sheet->setCellValue('H' . $totalRow, '');
        $sheet->setCellValue('I' . $totalRow, '');
        $sheet->setCellValue('J' . $totalRow, $totalValorDiferencias);
        $sheet->setCellValue('K' . $totalRow, '');
        $sheet->setCellValue('L' . $totalRow, '');
        $sheet->setCellValue('M' . $totalRow, '');

        // Estilo para totales
        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $totalRow . ':M' . $totalRow)->applyFromArray($totalStyle);
    }

    // Formato de números
    $sheet->getStyle('E' . ($headersRow + 1) . ':G' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    $sheet->getStyle('H' . ($headersRow + 1) . ':H' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('0.00%');

    $sheet->getStyle('I' . ($headersRow + 1) . ':J' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');

    // Formato para totales
    if ($datos && count($datos) > 0) {
        $sheet->getStyle('E' . $totalRow . ':G' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle('J' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
    }

    // Autoajustar columnas
    foreach (range('A', 'M') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Centrar algunas columnas
    $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('L:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('I:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="inventario_fisico_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
