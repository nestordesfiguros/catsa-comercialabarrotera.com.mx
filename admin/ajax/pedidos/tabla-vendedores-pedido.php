<?php
// ajax/pedidos/tabla-vendedores-pedido.php
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
    $whereSearch = " AND (nombre LIKE '%{$sv}%' OR apellido1 LIKE '%{$sv}%' OR apellido2 LIKE '%{$sv}%' OR tel LIKE '%{$sv}%') ";
}

$rt = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS c FROM cat_vendedores WHERE {$whereBase}");
$recordsTotal = (int)($rt[1]['c'] ?? 0);

$recordsFiltered = $recordsTotal;
if ($whereSearch !== '') {
    $rf = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS c FROM cat_vendedores WHERE {$whereBase} {$whereSearch}");
    $recordsFiltered = (int)($rf[1]['c'] ?? 0);
}

$sql = "
    SELECT 
        id,
        nombre,
        apellido1,
        apellido2,
        tel
    FROM cat_vendedores
    WHERE {$whereBase} {$whereSearch}
    ORDER BY nombre ASC, apellido1 ASC
    LIMIT {$start}, {$length}
";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];

if (is_array($rs) && $clsConsulta->numrows > 0) {
    foreach ($rs as $val) {
        if (!is_array($val) || !isset($val['id'])) continue;

        $idVendedor = (int)$val['id'];
        $nombreCompleto = trim(($val['nombre'] ?? '') . ' ' . ($val['apellido1'] ?? '') . ' ' . ($val['apellido2'] ?? ''));
        $telefono = $val['tel'] ?? '';

        $btn = '
            <div class="text-center">
                <button type="button"
                        class="btn btn-sm btn-info"
                        onclick="seleccionarVendedor(' . $idVendedor . ', \'' . addslashes($nombreCompleto) . '\');">
                    Seleccionar
                </button>
            </div>';

        $data[] = [
            '<div>' . htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') . '</div>',
            '<div class="text-nowrap">' . htmlspecialchars((string)$telefono, ENT_QUOTES, 'UTF-8') . '</div>',
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
