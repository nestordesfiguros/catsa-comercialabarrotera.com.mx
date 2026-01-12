<?php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$req    = $_REQUEST;
$draw   = isset($req['draw'])   ? intval($req['draw'])   : 1;
$start  = isset($req['start'])  ? intval($req['start'])  : 0;
$length = isset($req['length']) ? intval($req['length']) : 10;

$search = '';
if (isset($req['search'])) {
    if (is_array($req['search']) && isset($req['search']['value'])) $search = trim($req['search']['value']);
    else $search = trim($req['search']);
}

$where = " WHERE 1=1 ";
if ($search !== '') {
    $sv = $cls->escape($search);
    $where .= " AND (e.razon_social LIKE '%{$sv}%' OR e.rfc LIKE '%{$sv}%' OR e.correo LIKE '%{$sv}%') ";
}

$sqlCount = "SELECT COUNT(*) AS total FROM cat_empresas e {$where}";
$rsCount  = $cls->aplicaQuery($sqlCount);
$total = 0;
if ($rsCount) {
    $row = mysqli_fetch_assoc($rsCount);
    $total = intval($row['total'] ?? 0);
}

$sql = "SELECT e.id, e.razon_social, e.estatus
        FROM cat_empresas e
        {$where}
        ORDER BY e.razon_social ASC
        LIMIT {$start}, {$length}";
$rs  = $cls->aplicaQuery($sql);

$data = [];
while ($r = mysqli_fetch_assoc($rs)) {
    $id      = (int)$r['id'];
    $estatus = (int)$r['estatus'];
    $badge = $estatus ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';

    $acciones = '
    <div class="d-flex justify-content-center gap-1">
      <button class="btn btn-outline-primary btn-sm" title="Editar" onclick="editar(' . $id . ')">
        <i class="fa-solid fa-pen-to-square"></i>
      </button>
      <button class="btn btn-outline-info btn-sm" title="Timbrado" onclick="timbrado(' . $id . ')">
        <i class="fa-solid fa-certificate"></i>
      </button>
      <button class="btn btn-outline-secondary btn-sm" title="Estatus" onclick="valorEstatus(' . $id . ',' . $estatus . ')">
        <i class="fa-solid fa-arrows-rotate"></i>
      </button>
    </div>';

    $data[] = [$r['razon_social'], $acciones, $badge];
}

echo json_encode([
    "draw"            => $draw,
    "recordsTotal"    => $total,
    "recordsFiltered" => $total,
    "data"            => $data
]);
