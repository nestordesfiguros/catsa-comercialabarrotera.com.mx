<?php
// admin/excel/reportes-ventas-vendedor.php
// Genera Excel: Reporte Ventas por Vendedor (PhpSpreadsheet)

session_start();
require __DIR__ . '/../../lib/clsConsultas.php';
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$clsConsulta = new Consultas();

// Obtener filtros desde GET (sanitizados)
$fecha_inicio = trim($_GET['fecha_inicio'] ?? '');
$fecha_fin    = trim($_GET['fecha_fin'] ?? '');
$vendedor_id  = intval($_GET['vendedor'] ?? 0);

// Empresa actual (filtro multiempresa)
$idEmpresa = intval($_SESSION['id_empresa'] ?? 0);

// Construir WHERE seguro usando sanitizar
$where = "IFNULL(r.estatus, '') <> 'cancelada'";

if ($idEmpresa > 0) {
    $where .= " AND r.id_empresa = " . $clsConsulta->sanitizar($idEmpresa, true, false);
}
if ($vendedor_id > 0) {
    $where .= " AND r.id_vendedor = " . $clsConsulta->sanitizar($vendedor_id, true, false);
}
if ($fecha_inicio !== '') {
    $where .= " AND DATE(r.fecha) >= " . $clsConsulta->sanitizar($fecha_inicio, false, false);
}
if ($fecha_fin !== '') {
    $where .= " AND DATE(r.fecha) <= " . $clsConsulta->sanitizar($fecha_fin, false, false);
}

// Query principal
$sql = "
  SELECT
    r.id AS remision_id,
    r.fecha,
    r.total AS total_remision,
    r.id_vendedor,
    CONCAT_WS(' ', v.nombre, v.apellido1, v.apellido2) AS vendedor,
    c.razon_social AS cliente
  FROM cab_remisiones r
  LEFT JOIN cat_vendedores v ON v.id = r.id_vendedor
  LEFT JOIN cat_clientes c ON c.id = r.id_cliente
  WHERE {$where}
  ORDER BY r.fecha DESC
  LIMIT 10000
";

$rows = $clsConsulta->consultaGeneral($sql);
$numRows = $clsConsulta->numrows;

// Crear spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ventas por Vendedor');

// Cabecera del sheet
$sheet->setCellValue('A1', 'Remisión');
$sheet->setCellValue('B1', 'Fecha');
$sheet->setCellValue('C1', 'Vendedor');
$sheet->setCellValue('D1', 'Cliente');
$sheet->setCellValue('E1', 'Total');

$rowNum = 2;
$totalGlobal = 0.0;

if ($numRows > 0 && is_array($rows)) {
    foreach ($rows as $k => $r) {
        if (!is_array($r) || !isset($r['remision_id'])) continue;
        $remId = $r['remision_id'];
        $fecha = $r['fecha'];
        $vendedor = $r['vendedor'] ?? 'SIN VENDEDOR';
        $cliente = $r['cliente'] ?? '';
        $total = floatval($r['total_remision'] ?? 0);
        $totalGlobal += $total;

        $sheet->setCellValue('A' . $rowNum, $remId);
        $sheet->setCellValue('B' . $rowNum, $fecha);
        $sheet->setCellValue('C' . $rowNum, $vendedor);
        $sheet->setCellValue('D' . $rowNum, $cliente);
        $sheet->setCellValue('E' . $rowNum, $total);
        $rowNum++;
    }
}

// Totales al final
$sheet->setCellValue('D' . $rowNum, 'Total:');
$sheet->setCellValue('E' . $rowNum, $totalGlobal);

// Formato: ancho de columnas y número
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->getStyle('E2:E' . $rowNum)->getNumberFormat()
      ->setFormatCode('#,##0.00');

// Encabezados HTTP para descarga
$filename = 'reporte_ventas_vendedor_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Escribir archivo
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
