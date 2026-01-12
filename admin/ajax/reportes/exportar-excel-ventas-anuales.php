<?php
// ajax/reportes/exportar-excel-ventas-anuales.php

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
$tipoReporte = $_GET['tipo_reporte'] ?? 'mensual';
$anio = $_GET['anio'] ?? date('Y');
$anioComparativo = $_GET['anio_comparativo'] ?? (date('Y') - 1);
$tipoVenta = $_GET['tipo_venta'] ?? '';
$idVendedor = $_GET['id_vendedor'] ?? 0;
$idAlmacen = $_GET['id_almacen'] ?? 0;
$metrica = $_GET['metrica'] ?? 'ventas';

$clsConsulta = new Consultas();

try {
    // Reutilizar las funciones del endpoint AJAX
    $datos = [];

    switch ($tipoReporte) {
        case 'anual':
            $datos = generarReporteAnual($anio, $tipoVenta, $idVendedor, $idAlmacen);
            break;
        case 'mensual':
            $datos = generarReporteMensual($anio, $tipoVenta, $idVendedor, $idAlmacen);
            break;
        case 'comparativo':
            $datos = generarReporteComparativo($anio, $anioComparativo, $tipoVenta, $idVendedor, $idAlmacen);
            break;
        default:
            $datos = generarReporteMensual($anio, $tipoVenta, $idVendedor, $idAlmacen);
    }

    // Obtener información de la empresa
    $sqlEmpresa = "SELECT razon_social, nombre_comercial FROM cat_empresas WHERE id = 1 LIMIT 1";
    $empresa = $clsConsulta->consultaGeneral($sqlEmpresa);
    $empresaInfo = $clsConsulta->numrows > 0 ? $empresa[1] : null;

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $tituloHoja = getTituloReporte($tipoReporte, $anio, $anioComparativo);
    $sheet->setTitle(substr($tituloHoja, 0, 31)); // Máximo 31 caracteres para nombre de hoja

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
    $sheet->setCellValue('A' . $empresaRow, $tituloHoja);
    $sheet->mergeCells('A' . $empresaRow . ':I' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Tipo Reporte:');
    $sheet->setCellValue('B' . $filtersRow, ucfirst($tipoReporte));
    $sheet->setCellValue('D' . $filtersRow, 'Año:');
    $sheet->setCellValue('E' . $filtersRow, $anio);
    if ($tipoReporte === 'comparativo') {
        $sheet->setCellValue('F' . $filtersRow, 'Comparativo:');
        $sheet->setCellValue('G' . $filtersRow, $anioComparativo);
    }
    $sheet->setCellValue('H' . $filtersRow, 'Métrica:');
    $sheet->setCellValue('I' . $filtersRow, ucfirst(str_replace('_', ' ', $metrica)));

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':I' . $filtersRow)->applyFromArray($filterStyle);

    // Encabezados de tabla
    $headersRow = $filtersRow + 2;

    if ($tipoReporte === 'comparativo') {
        $headers = ['Mes', 'Año ' . $anio, 'Año ' . $anioComparativo, 'Crecimiento %', 'Diferencia $', 'Tendencia'];
    } else {
        $columnaPeriodo = $tipoReporte === 'anual' ? 'Año' : 'Mes';
        $headers = [$columnaPeriodo, 'Ventas Totales', 'Cantidad Ventas', 'Ticket Promedio', 'Productos Vendidos', 'Crecimiento %', '% del Total', 'Tendencia'];
    }

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
    $lastHeaderCol = $tipoReporte === 'comparativo' ? 'F' : 'H';
    $sheet->getStyle('A' . $headersRow . ':' . $lastHeaderCol . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    $totalVentas = 0;
    $totalCantidad = 0;
    $totalProductos = 0;

    if ($datos && count($datos) > 0) {
        if ($tipoReporte === 'comparativo') {
            // Procesar datos para reporte comparativo
            $datosAgrupados = [];
            foreach ($datos as $item) {
                $mes = $item['periodo'];
                $anioItem = $item['anio'];
                $ventas = floatval($item['total_ventas']);

                if (!isset($datosAgrupados[$mes])) {
                    $datosAgrupados[$mes] = [];
                }
                $datosAgrupados[$mes][$anioItem] = $ventas;
            }

            foreach ($datosAgrupados as $mes => $ventasPorAnio) {
                $ventasAnioActual = $ventasPorAnio[$anio] ?? 0;
                $ventasAnioComparativo = $ventasPorAnio[$anioComparativo] ?? 0;

                $crecimiento = 0;
                $diferencia = $ventasAnioActual - $ventasAnioComparativo;

                if ($ventasAnioComparativo > 0) {
                    $crecimiento = ($diferencia / $ventasAnioComparativo) * 100;
                } elseif ($ventasAnioActual > 0) {
                    $crecimiento = 100; // Crecimiento del 100% si no había ventas el año anterior
                }

                $tendencia = getTendenciaExcel($crecimiento);

                $sheet->setCellValue('A' . $dataRow, $mes);
                $sheet->setCellValue('B' . $dataRow, $ventasAnioActual);
                $sheet->setCellValue('C' . $dataRow, $ventasAnioComparativo);
                $sheet->setCellValue('D' . $dataRow, $crecimiento / 100); // Formato porcentaje
                $sheet->setCellValue('E' . $dataRow, $diferencia);
                $sheet->setCellValue('F' . $dataRow, $tendencia);

                // Alternar colores de fila
                if ($dataRow % 2 == 0) {
                    $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('F8F9FA');
                }

                $dataRow++;
            }
        } else {
            // Para reportes normales (mensual o anual)
            foreach ($datos as $index => $item) {
                $ventas = floatval($item['total_ventas']);
                $cantidad = intval($item['cantidad_ventas']);
                $productos = intval($item['total_productos']);
                $ticketPromedio = $cantidad > 0 ? $ventas / $cantidad : 0;

                $totalVentas += $ventas;
                $totalCantidad += $cantidad;
                $totalProductos += $productos;

                $crecimiento = $index > 0 ? calcularCrecimiento(floatval($datos[$index - 1]['total_ventas']), $ventas) : 0;
                $porcentajeTotal = $totalVentas > 0 ? ($ventas / $totalVentas) * 100 : 0;
                $tendencia = getTendenciaExcel($crecimiento);

                $sheet->setCellValue('A' . $dataRow, $item['periodo']);
                $sheet->setCellValue('B' . $dataRow, $ventas);
                $sheet->setCellValue('C' . $dataRow, $cantidad);
                $sheet->setCellValue('D' . $dataRow, $ticketPromedio);
                $sheet->setCellValue('E' . $dataRow, $productos);
                $sheet->setCellValue('F' . $dataRow, $crecimiento / 100); // Formato porcentaje
                $sheet->setCellValue('G' . $dataRow, $porcentajeTotal / 100); // Formato porcentaje
                $sheet->setCellValue('H' . $dataRow, $tendencia);

                // Alternar colores de fila
                if ($dataRow % 2 == 0) {
                    $sheet->getStyle('A' . $dataRow . ':H' . $dataRow)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('F8F9FA');
                }

                $dataRow++;
            }
        }
    } else {
        $lastCol = $tipoReporte === 'comparativo' ? 'F' : 'H';
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron datos con los filtros seleccionados');
        $sheet->mergeCells('A' . $dataRow . ':' . $lastCol . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales (solo para reportes normales, no comparativos)
    if ($tipoReporte !== 'comparativo' && $datos && count($datos) > 0) {
        $totalRow = $dataRow;
        $ticketPromedioGeneral = $totalCantidad > 0 ? $totalVentas / $totalCantidad : 0;

        $sheet->setCellValue('A' . $totalRow, 'TOTALES / PROMEDIOS');
        $sheet->setCellValue('B' . $totalRow, $totalVentas);
        $sheet->setCellValue('C' . $totalRow, $totalCantidad);
        $sheet->setCellValue('D' . $totalRow, $ticketPromedioGeneral);
        $sheet->setCellValue('E' . $totalRow, $totalProductos);
        $sheet->setCellValue('G' . $totalRow, 1); // 100%

        // Estilo para totales
        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray($totalStyle);
    }

    // Formato de números
    if ($tipoReporte === 'comparativo') {
        // Formato moneda para ventas y diferencia
        $sheet->getStyle('B' . ($headersRow + 1) . ':C' . ($dataRow - 1))
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $sheet->getStyle('E' . ($headersRow + 1) . ':E' . ($dataRow - 1))
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        // Formato porcentaje para crecimiento
        $sheet->getStyle('D' . ($headersRow + 1) . ':D' . ($dataRow - 1))
            ->getNumberFormat()
            ->setFormatCode('0.00%');
    } else {
        // Formato moneda para ventas y ticket promedio
        $sheet->getStyle('B' . ($headersRow + 1) . ':D' . ($dataRow - 1))
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        // Formato porcentaje para crecimiento y % del total
        $sheet->getStyle('F' . ($headersRow + 1) . ':G' . ($dataRow - 1))
            ->getNumberFormat()
            ->setFormatCode('0.00%');

        // Formato número para cantidad y productos
        $sheet->getStyle('C' . ($headersRow + 1) . ':C' . ($dataRow - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle('E' . ($headersRow + 1) . ':E' . ($dataRow - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0');
    }

    // Autoajustar columnas
    $lastCol = $tipoReporte === 'comparativo' ? 'F' : 'H';
    foreach (range('A', $lastCol) as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Centrar algunas columnas
    if ($tipoReporte === 'comparativo') {
        $sheet->getStyle('D:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    } else {
        $sheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . strtolower(str_replace(' ', '_', $tituloHoja)) . '_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}

// Funciones auxiliares
function getTituloReporte($tipoReporte, $anio, $anioComparativo)
{
    switch ($tipoReporte) {
        case 'anual':
            return 'Ventas Anuales ' . $anio;
        case 'mensual':
            return 'Ventas Mensuales ' . $anio;
        case 'comparativo':
            return 'Comparativo Ventas ' . $anio . ' vs ' . $anioComparativo;
        default:
            return 'Reporte de Ventas';
    }
}

function calcularCrecimiento($anterior, $actual)
{
    if ($anterior === 0) return $actual > 0 ? 100 : 0;
    return (($actual - $anterior) / $anterior) * 100;
}

function getTendenciaExcel($crecimiento)
{
    if ($crecimiento > 10) {
        return 'Fuerte Alza';
    } else if ($crecimiento > 0) {
        return 'Alza Moderada';
    } else if ($crecimiento < -10) {
        return 'Fuerte Baja';
    } else if ($crecimiento < 0) {
        return 'Baja Moderada';
    } else {
        return 'Estable';
    }
}

// Funciones de consulta a la base de datos
function generarReporteMensual($anio, $tipoVenta, $idVendedor, $idAlmacen)
{
    global $clsConsulta;

    $sql = "
        SELECT 
            MONTH(cr.fecha) AS mes_numero,
            DATE_FORMAT(cr.fecha, '%M') AS periodo,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE YEAR(cr2.fecha) = ? AND MONTH(cr2.fecha) = MONTH(cr.fecha)) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            YEAR(cr.fecha) = ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$anio, $anio];
    $types = "ii";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " GROUP BY MONTH(cr.fecha), periodo ORDER BY mes_numero";

    return $clsConsulta->consultaPreparada($sql, $params, $types);
}

function generarReporteAnual($anio, $tipoVenta, $idVendedor, $idAlmacen)
{
    global $clsConsulta;

    $sql = "
        SELECT 
            YEAR(cr.fecha) AS periodo,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE YEAR(cr2.fecha) = YEAR(cr.fecha)) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            YEAR(cr.fecha) BETWEEN ? - 4 AND ?
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$anio, $anio];
    $types = "ii";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " GROUP BY YEAR(cr.fecha) ORDER BY periodo DESC";

    return $clsConsulta->consultaPreparada($sql, $params, $types);
}

function generarReporteComparativo($anio, $anioComparativo, $tipoVenta, $idVendedor, $idAlmacen)
{
    global $clsConsulta;

    $sql = "
        SELECT 
            MONTH(cr.fecha) AS mes_numero,
            DATE_FORMAT(cr.fecha, '%M') AS periodo,
            YEAR(cr.fecha) AS anio,
            COUNT(DISTINCT cr.id) AS cantidad_ventas,
            COALESCE(SUM(cr.total), 0) AS total_ventas,
            (SELECT COALESCE(SUM(mr.cantidad), 0) FROM mov_remisiones mr 
             INNER JOIN cab_remisiones cr2 ON mr.id_remision = cr2.id 
             WHERE YEAR(cr2.fecha) = YEAR(cr.fecha) AND MONTH(cr2.fecha) = MONTH(cr.fecha)) AS total_productos
        FROM 
            cab_remisiones cr
        WHERE 
            YEAR(cr.fecha) IN (?, ?)
            AND cr.estatus IN ('procesada', 'pendiente')
    ";

    $params = [$anio, $anioComparativo];
    $types = "ii";

    // Agregar filtros
    if (!empty($tipoVenta)) {
        $sql .= " AND cr.tipo_venta = ?";
        $params[] = $tipoVenta;
        $types .= "s";
    }

    if ($idVendedor > 0) {
        $sql .= " AND cr.id_vendedor = ?";
        $params[] = $idVendedor;
        $types .= "i";
    }

    if ($idAlmacen > 0) {
        $sql .= " AND cr.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    $sql .= " GROUP BY YEAR(cr.fecha), MONTH(cr.fecha), periodo ORDER BY mes_numero, anio";

    return $clsConsulta->consultaPreparada($sql, $params, $types);
}
