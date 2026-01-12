<?php
// ajax/reportes/exportar-excel-existencias-costos.php

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
$idAlmacen = $_GET['id_almacen'] ?? 0;
$idCategoria = $_GET['id_categoria'] ?? 0;
$idProveedor = $_GET['id_proveedor'] ?? 0;
$estatusStock = $_GET['estatus_stock'] ?? '';
$ordenarPor = $_GET['ordenar_por'] ?? 'nombre';
$direccionOrden = $_GET['direccion_orden'] ?? 'asc';
$mostrarSolo = $_GET['mostrar_solo'] ?? '';

$clsConsulta = new Consultas();

try {
    // Reutilizar la misma consulta del PDF
    $sql = "
        SELECT 
            cp.id_producto,
            cp.clave,
            cp.nombre AS producto,
            cc.nombre_categoria AS categoria,
            ca.almacen,
            cprov.razon_social AS proveedor,
            COALESCE(i.cantidad, 0) AS existencia,
            cp.stock_minimo,
            cp.stock_maximo,
            cp.precio_compra AS costo_unitario,
            cp.precio_venta,
            (COALESCE(i.cantidad, 0) * cp.precio_compra) AS valor_total,
            cp.estado,
            cp.fecha_registro AS ultima_actualizacion
        FROM 
            cat_productos cp
        LEFT JOIN 
            inventarios i ON cp.id_producto = i.id_producto
        LEFT JOIN 
            cat_categorias cc ON cp.id_categoria = cc.id_categoria
        LEFT JOIN 
            cat_almacenes ca ON i.id_almacen = ca.id
        LEFT JOIN 
            cat_proveedores cprov ON cp.id_proveedor = cprov.id
        WHERE 
            1 = 1
    ";

    $params = [];
    $types = "";

    // Aplicar filtros
    if ($idAlmacen > 0) {
        $sql .= " AND i.id_almacen = ?";
        $params[] = $idAlmacen;
        $types .= "i";
    }

    if ($idCategoria > 0) {
        $sql .= " AND cp.id_categoria = ?";
        $params[] = $idCategoria;
        $types .= "i";
    }

    if ($idProveedor > 0) {
        $sql .= " AND cp.id_proveedor = ?";
        $params[] = $idProveedor;
        $types .= "i";
    }

    // Filtro por estado de stock
    if (!empty($estatusStock)) {
        switch ($estatusStock) {
            case 'stock_minimo':
                $sql .= " AND COALESCE(i.cantidad, 0) <= cp.stock_minimo AND COALESCE(i.cantidad, 0) > 0";
                break;
            case 'stock_maximo':
                $sql .= " AND COALESCE(i.cantidad, 0) >= cp.stock_maximo";
                break;
            case 'sin_existencia':
                $sql .= " AND COALESCE(i.cantidad, 0) = 0";
                break;
            case 'con_existencia':
                $sql .= " AND COALESCE(i.cantidad, 0) > 0";
                break;
        }
    }

    // Filtro por estado del producto
    if ($mostrarSolo === 'activos') {
        $sql .= " AND cp.estado = 'activo'";
    } elseif ($mostrarSolo === 'inactivos') {
        $sql .= " AND cp.estado = 'inactivo'";
    }

    // Ordenamiento
    $orderColumn = 'cp.nombre';
    switch ($ordenarPor) {
        case 'clave':
            $orderColumn = 'cp.clave';
            break;
        case 'existencia':
            $orderColumn = 'existencia';
            break;
        case 'costo':
            $orderColumn = 'cp.precio_compra';
            break;
        case 'valor':
            $orderColumn = 'valor_total';
            break;
    }

    $orderDirection = strtoupper($direccionOrden) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY $orderColumn $orderDirection, cp.clave ASC";

    $datos = $clsConsulta->consultaPreparada($sql, $params, $types);

    // Calcular totales
    $totalProductos = 0;
    $totalExistencia = 0;
    $totalValor = 0;
    $sinStock = 0;
    $stockMinimo = 0;

    if ($datos && count($datos) > 0) {
        $totalProductos = count($datos);
        foreach ($datos as $item) {
            $existencia = intval($item['existencia']);
            $totalExistencia += $existencia;
            $totalValor += floatval($item['valor_total']);

            if ($existencia === 0) {
                $sinStock++;
            }

            if ($existencia <= intval($item['stock_minimo']) && $existencia > 0) {
                $stockMinimo++;
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
    $sheet->setTitle('Existencias y Costos');

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
        $sheet->mergeCells('C1:L1');
        $sheet->mergeCells('C2:L2');
        $sheet->getStyle('C1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $empresaRow = 3;
    }

    // Título del reporte
    $sheet->setCellValue('A' . $empresaRow, 'Existencias y Costos de Inventario');
    $sheet->mergeCells('A' . $empresaRow . ':L' . $empresaRow);
    $sheet->getStyle('A' . $empresaRow)->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A' . $empresaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($empresaRow)->setRowHeight(25);

    // Información de filtros
    $filtersRow = $empresaRow + 1;
    $sheet->setCellValue('A' . $filtersRow, 'Almacén:');
    $sheet->setCellValue('B' . $filtersRow, $idAlmacen ? 'Específico' : 'Todos');
    $sheet->setCellValue('C' . $filtersRow, 'Categoría:');
    $sheet->setCellValue('D' . $filtersRow, $idCategoria ? 'Específica' : 'Todas');
    $sheet->setCellValue('E' . $filtersRow, 'Proveedor:');
    $sheet->setCellValue('F' . $filtersRow, $idProveedor ? 'Específico' : 'Todos');
    $sheet->setCellValue('G' . $filtersRow, 'Estado Stock:');
    $sheet->setCellValue('H' . $filtersRow, $estatusStock ? ucfirst(str_replace('_', ' ', $estatusStock)) : 'Todos');
    $sheet->setCellValue('I' . $filtersRow, 'Ordenado por:');
    $sheet->setCellValue('J' . $filtersRow, ucfirst($ordenarPor));

    // Estilo para información de filtros
    $filterStyle = [
        'font' => ['size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $filtersRow . ':L' . $filtersRow)->applyFromArray($filterStyle);

    // Resumen
    $resumenRow = $filtersRow + 2;
    $sheet->setCellValue('A' . $resumenRow, 'RESUMEN GENERAL');
    $sheet->mergeCells('A' . $resumenRow . ':L' . $resumenRow);
    $sheet->getStyle('A' . $resumenRow)->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A' . $resumenRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D1ECF1');

    $resumenRow++;
    $sheet->setCellValue('A' . $resumenRow, 'Total Productos:');
    $sheet->setCellValue('B' . $resumenRow, $totalProductos);
    $sheet->setCellValue('C' . $resumenRow, 'Valor Total Inventario:');
    $sheet->setCellValue('D' . $resumenRow, $totalValor);
    $sheet->setCellValue('E' . $resumenRow, 'Productos Sin Stock:');
    $sheet->setCellValue('F' . $resumenRow, $sinStock);
    $sheet->setCellValue('G' . $resumenRow, 'Stock Mínimo Alcanzado:');
    $sheet->setCellValue('H' . $resumenRow, $stockMinimo);
    $sheet->setCellValue('I' . $resumenRow, 'Total Existencia:');
    $sheet->setCellValue('J' . $resumenRow, $totalExistencia);

    // Encabezados de tabla
    $headersRow = $resumenRow + 2;
    $headers = ['Clave', 'Producto', 'Categoría', 'Almacén', 'Proveedor', 'Existencia', 'Stock Mín', 'Stock Máx', 'Estado', 'Costo Unitario', 'Precio Venta', 'Valor Total'];

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headersRow, $header);
        $col++;
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A' . $headersRow . ':L' . $headersRow)->applyFromArray($headerStyle);
    $sheet->getRowDimension($headersRow)->setRowHeight(25);

    // Datos
    $dataRow = $headersRow + 1;
    if ($datos && count($datos) > 0) {
        foreach ($datos as $item) {
            $existencia = intval($item['existencia']);
            $stockMinimo = intval($item['stock_minimo']);

            // Determinar estado del stock
            $estadoStock = '';
            if ($existencia === 0) {
                $estadoStock = 'Sin Stock';
            } elseif ($existencia <= $stockMinimo) {
                $estadoStock = 'Stock Mínimo';
            } else {
                $estadoStock = 'Normal';
            }

            $sheet->setCellValue('A' . $dataRow, $item['clave']);
            $sheet->setCellValue('B' . $dataRow, $item['producto']);
            $sheet->setCellValue('C' . $dataRow, $item['categoria'] ?? 'Sin categoría');
            $sheet->setCellValue('D' . $dataRow, $item['almacen'] ?? 'N/A');
            $sheet->setCellValue('E' . $dataRow, $item['proveedor'] ?? 'N/A');
            $sheet->setCellValue('F' . $dataRow, $existencia);
            $sheet->setCellValue('G' . $dataRow, $stockMinimo);
            $sheet->setCellValue('H' . $dataRow, $item['stock_maximo'] > 0 ? $item['stock_maximo'] : 'N/A');
            $sheet->setCellValue('I' . $dataRow, $estadoStock);
            $sheet->setCellValue('J' . $dataRow, $item['costo_unitario'] ?? 0);
            $sheet->setCellValue('K' . $dataRow, $item['precio_venta'] ?? 0);
            $sheet->setCellValue('L' . $dataRow, $item['valor_total'] ?? 0);

            // Alternar colores de fila
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':L' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            // Color para estado del stock
            if ($existencia === 0) {
                $sheet->getStyle('I' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8D7DA');
            } elseif ($existencia <= $stockMinimo) {
                $sheet->getStyle('I' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFF3CD');
            } else {
                $sheet->getStyle('I' . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('D1ECF1');
            }

            $dataRow++;
        }
    } else {
        $sheet->setCellValue('A' . $dataRow, 'No se encontraron productos');
        $sheet->mergeCells('A' . $dataRow . ':L' . $dataRow);
        $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        $dataRow++;
    }

    // Totales
    if ($datos && count($datos) > 0) {
        $totalRow = $dataRow;

        // Calcular promedios
        $sumaCostos = 0;
        $sumaVentas = 0;
        foreach ($datos as $item) {
            $sumaCostos += floatval($item['costo_unitario'] ?? 0);
            $sumaVentas += floatval($item['precio_venta'] ?? 0);
        }
        $promedioCosto = $totalProductos > 0 ? $sumaCostos / $totalProductos : 0;
        $promedioVenta = $totalProductos > 0 ? $sumaVentas / $totalProductos : 0;

        $sheet->setCellValue('A' . $totalRow, 'TOTALES / PROMEDIOS');
        $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow);
        $sheet->setCellValue('F' . $totalRow, $totalExistencia);
        $sheet->setCellValue('G' . $totalRow, '');
        $sheet->setCellValue('H' . $totalRow, '');
        $sheet->setCellValue('I' . $totalRow, '');
        $sheet->setCellValue('J' . $totalRow, $promedioCosto);
        $sheet->setCellValue('K' . $totalRow, $promedioVenta);
        $sheet->setCellValue('L' . $totalRow, $totalValor);

        // Estilo para totales
        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $totalRow . ':L' . $totalRow)->applyFromArray($totalStyle);
    }

    // Formato de números
    $sheet->getStyle('F' . ($headersRow + 1) . ':H' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    $sheet->getStyle('J' . ($headersRow + 1) . ':L' . ($dataRow - 1))
        ->getNumberFormat()
        ->setFormatCode('"$"#,##0.00');

    // Formato para totales
    if ($datos && count($datos) > 0) {
        $sheet->getStyle('J' . $totalRow . ':L' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');
        $sheet->getStyle('F' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');
    }

    // Autoajustar columnas
    foreach (range('A', 'L') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Centrar algunas columnas
    $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('I:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('J:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // Hoja de análisis por categoría
    if ($datos && count($datos) > 0) {
        $categorias = [];
        foreach ($datos as $item) {
            $categoria = $item['categoria'] ?? 'Sin categoría';
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [
                    'count' => 0,
                    'valor' => 0,
                    'existencia' => 0
                ];
            }
            $categorias[$categoria]['count']++;
            $categorias[$categoria]['valor'] += floatval($item['valor_total']);
            $categorias[$categoria]['existencia'] += intval($item['existencia']);
        }

        // Crear hoja de análisis
        $analysisSheet = $spreadsheet->createSheet();
        $analysisSheet->setTitle('Análisis por Categoría');

        // Título
        $analysisSheet->setCellValue('A1', 'Análisis de Inventario por Categoría');
        $analysisSheet->mergeCells('A1:E1');
        $analysisSheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $analysisSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Encabezados
        $analysisHeaders = ['Categoría', 'Cantidad Productos', '% del Total', 'Existencia Total', 'Valor Total'];
        $col = 'A';
        foreach ($analysisHeaders as $header) {
            $analysisSheet->setCellValue($col . '3', $header);
            $col++;
        }

        // Estilo encabezados
        $analysisSheet->getStyle('A3:E3')->applyFromArray($headerStyle);

        // Datos de análisis
        $analysisRow = 4;
        foreach ($categorias as $categoria => $data) {
            $porcentaje = ($totalProductos > 0) ? ($data['count'] / $totalProductos) * 100 : 0;

            $analysisSheet->setCellValue('A' . $analysisRow, $categoria);
            $analysisSheet->setCellValue('B' . $analysisRow, $data['count']);
            $analysisSheet->setCellValue('C' . $analysisRow, $porcentaje / 100); // Formato porcentaje
            $analysisSheet->setCellValue('D' . $analysisRow, $data['existencia']);
            $analysisSheet->setCellValue('E' . $analysisRow, $data['valor']);

            // Alternar colores
            if ($analysisRow % 2 == 0) {
                $analysisSheet->getStyle('A' . $analysisRow . ':E' . $analysisRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('F8F9FA');
            }

            $analysisRow++;
        }

        // Formato de análisis
        $analysisSheet->getStyle('B4:B' . ($analysisRow - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $analysisSheet->getStyle('C4:C' . ($analysisRow - 1))
            ->getNumberFormat()
            ->setFormatCode('0.00%');
        $analysisSheet->getStyle('D4:D' . ($analysisRow - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $analysisSheet->getStyle('E4:E' . ($analysisRow - 1))
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        // Autoajustar columnas análisis
        foreach (range('A', 'E') as $column) {
            $analysisSheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Regresar a la hoja principal
        $spreadsheet->setActiveSheetIndex(0);
    }

    // Descargar archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="existencias_costos_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
}
