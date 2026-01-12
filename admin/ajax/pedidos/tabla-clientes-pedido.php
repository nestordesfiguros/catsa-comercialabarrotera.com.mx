<?php
// ajax/pedidos/tabla-clientes-pedido.php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 0;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 10;

$searchValue = '';
if (isset($_GET['search']) && is_array($_GET['search'])) {
    $searchValue = trim((string)($_GET['search']['value'] ?? ''));
}

if ($length < 1) $length = 10;
if ($length > 100) $length = 100;
if ($start < 0) $start = 0;

if ($idEmpresa <= 0) {
    echo json_encode(['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
    exit;
}

$whereBase = "estatus = 1 AND id_empresa = {$idEmpresa}";
$whereSearch = "";
if ($searchValue !== '') {
    $sv = addslashes($searchValue);
    $whereSearch = " AND (nombre_comercial LIKE '%{$sv}%' OR razon_social LIKE '%{$sv}%' OR rfc LIKE '%{$sv}%' OR tel1 LIKE '%{$sv}%' OR tel2 LIKE '%{$sv}%') ";
}

$rt = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS c FROM cat_clientes WHERE {$whereBase}");
$recordsTotal = (int)($rt[1]['c'] ?? 0);

$recordsFiltered = $recordsTotal;
if ($whereSearch !== '') {
    $rf = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS c FROM cat_clientes WHERE {$whereBase} {$whereSearch}");
    $recordsFiltered = (int)($rf[1]['c'] ?? 0);
}

$sql = "
    SELECT 
        id,
        nombre_comercial,
        razon_social,
        tel1,
        tel2,
        rfc
    FROM cat_clientes
    WHERE {$whereBase} {$whereSearch}
    ORDER BY nombre_comercial ASC, razon_social ASC
    LIMIT {$start}, {$length}
";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];

if (is_array($rs) && $clsConsulta->numrows > 0) {
    foreach ($rs as $val) {
        if (!is_array($val) || !isset($val['id'])) continue;

        $idCliente = (int)$val['id'];
        $nombre = $val['nombre_comercial'];
        if ($nombre === null || trim($nombre) === '') $nombre = $val['razon_social'] ?? '';

        $telefono = $val['tel1'] ?? ($val['tel2'] ?? '');
        $rfc = is_string($val['rfc'] ?? '') ? ($val['rfc'] ?? '') : '';

        $nombreHtml = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $telefonoHtml = htmlspecialchars((string)$telefono, ENT_QUOTES, 'UTF-8');
        $rfcHtml = htmlspecialchars((string)$rfc, ENT_QUOTES, 'UTF-8');

        $btn = '
            <div class="text-center">
                <button type="button"
                        class="btn btn-sm btn-success"
                        onclick="seleccionarCliente(' . $idCliente . ', \'' . addslashes((string)$nombre) . '\');">
                    Seleccionar
                </button>
            </div>';

        $data[] = [
            '<div>' . $nombreHtml . '</div>',
            '<div class="text-nowrap">' . $rfcHtml . '</div>',
            '<div class="text-nowrap">' . $telefonoHtml . '</div>',
            $btn
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);
