<?php
// ajax/facturas/estado-timbrado.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Falta id']);
    exit;
}

$r = $clsConsulta->consultaGeneral("SELECT id, serie, folio, uuid, fecha_timbrado FROM cab_facturas WHERE id={$id} LIMIT 1");
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Factura no encontrada']);
    exit;
}
$f = $r[1];

$PATH_TIMBRADOS = __DIR__ . '/../timbrado/timbrados';
$xmlPath = $PATH_TIMBRADOS . '/' . $f['serie'] . '_' . $f['folio'] . '.xml';
$xmlExists = file_exists($xmlPath);

$timbrada = !empty($f['uuid']);
$out = [
    'success'        => true,
    'id'             => (int)$f['id'],
    'serie'          => $f['serie'],
    'folio'          => $f['folio'],
    'timbrada'       => $timbrada,
    'uuid'           => $f['uuid'] ?? null,
    'fecha_timbrado' => $f['fecha_timbrado'] ?? null,
    'xml_exists'     => $xmlExists,
    'xml_file'       => $xmlExists ? ($f['serie'] . '_' . $f['folio'] . '.xml') : null,
];

echo json_encode($out);
