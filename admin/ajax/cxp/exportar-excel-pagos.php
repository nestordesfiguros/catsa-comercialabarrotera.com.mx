<?php
// ajax/cxp/exportar-excel-pagos.php
session_start();
require '../../lib/clsConsultas.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id_cxp = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idEmpresa <= 0 || $id_cxp <= 0) {
    exit;
}

// Validar CxP pertenece a empresa
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cuentas_por_pagar WHERE id={$id_cxp} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows < 1) {
    exit;
}

$pagos = $clsConsulta->consultaGeneral("
    SELECT p.fecha, p.monto, m.descripcion AS metodo_pago, p.referencia, p.observaciones
    FROM pagos_cxp p
    LEFT JOIN metodo_pago m ON m.id = p.id_metodo_pago
    WHERE p.id_cxp = {$id_cxp}
    ORDER BY p.fecha ASC
");
if (!is_array($pagos) || $clsConsulta->numrows <= 0) $pagos = [];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Fecha');
$sheet->setCellValue('B1', 'Monto');
$sheet->setCellValue('C1', 'Método de pago');
$sheet->setCellValue('D1', 'Referencia');
$sheet->setCellValue('E1', 'Observaciones');

$fila = 2;
foreach ($pagos as $pago) {
    $sheet->setCellValue("A{$fila}", $pago['fecha']);
    $sheet->setCellValue("B{$fila}", $pago['monto']);
    $sheet->setCellValue("C{$fila}", $pago['metodo_pago']);
    $sheet->setCellValue("D{$fila}", $pago['referencia']);
    $sheet->setCellValue("E{$fila}", $pago['observaciones']);
    $fila++;
}

$filename = "pagos_cxp_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
