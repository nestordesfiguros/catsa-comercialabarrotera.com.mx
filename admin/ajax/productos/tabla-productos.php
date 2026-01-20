<?php
// admin/ajax/productos/tabla-productos.php
session_start();
include '../../lib/clsConsultas.php';
//header('Content-Type: application/json');

$clsConsulta = new Consultas();


function safe_html($value)
{
    return $value !== null ? htmlspecialchars($value, ENT_QUOTES) : '';
}

// Empresa actual
$idEmpresa = ((int)$_SESSION['id_empresa']) ?  (int)$_SESSION['id_empresa'] : 0;


$whereEmpresa = "";
if ($idEmpresa > 0) {
    $whereEmpresa = " AND a.id_empresa = {$idEmpresa}";
}

// DataTables params
$draw   = isset($_GET['draw']) ? (int)$_GET['draw'] : 0;
$start  = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$length = isset($_GET['length']) ? (int)$_GET['length'] : 20;
if ($length <= 0) $length = 20;

// Almacén (obligatorio)
$idAlmacen = isset($_GET['id_almacen']) ? (int)$_GET['id_almacen'] : 0;

if ($idAlmacen <= 0) {
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

// Search
$searchValue = '';
if (isset($_GET['search']) && isset($_GET['search']['value'])) {
    $searchValue = trim($_GET['search']['value']);
}
$searchSql = "";
if ($searchValue !== '') {
    // se conserva el escape que ya usabas
    $s = $clsConsulta->escape($searchValue);
    $searchSql = " AND (p.clave LIKE '%{$s}%' OR p.nombre LIKE '%{$s}%')";
}

// Orden
$orderColumnIndex = 0;
$orderDir = 'ASC';

if (isset($_GET['order'][0]['column'])) $orderColumnIndex = (int)$_GET['order'][0]['column'];
if (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'desc') $orderDir = 'DESC';

// Columnas (según la tabla):
// 0 Clave, 1 Nombre, 2 Existencia, 3 Unidad, 4 Foto, 5 Editar, 6 Borrar
$orderMap = [
    0 => "p.clave",
    1 => "p.nombre",
    2 => "existencia",        // alias: existencia = disponible
    3 => "u.nombre_unidad"
];

$orderBy = " ORDER BY p.nombre ASC";
if (isset($orderMap[$orderColumnIndex])) {
    $orderBy = " ORDER BY {$orderMap[$orderColumnIndex]} {$orderDir}";
}

// WHERE base: solo almacén seleccionado + empresa actual por JOIN almacenes
$whereBase = " WHERE p.estatus = 1
    AND p.id_almacen = {$idAlmacen}
    {$whereEmpresa}
";

// Total
$sqlTotal = "SELECT COUNT(*) AS total
FROM cat_productos p
INNER JOIN cat_almacenes a ON a.id = p.id_almacen
{$whereBase}";
$rsTotal = $clsConsulta->consultaGeneral($sqlTotal);
$recordsTotal = 0;
if ($clsConsulta->numrows > 0) {
    $recordsTotal = (int)$rsTotal[1]['total'];
}


// Filtrado
$sqlFiltered = "SELECT COUNT(*) AS total
FROM cat_productos p
INNER JOIN cat_almacenes a ON a.id = p.id_almacen
{$whereBase}
{$searchSql}";


$rsFiltered = $clsConsulta->consultaGeneral($sqlFiltered);
$recordsFiltered = 0;
if ($clsConsulta->numrows > 0) {
    $recordsFiltered = (int)$rsFiltered[1]['total'];
}

// --- Detecta si inventarios / reservas tienen id_empresa (por compat) ---
$invHasEmpresa = false;
$resHasEmpresa = false;

$chkInv = $clsConsulta->consultaGeneral("SHOW COLUMNS FROM inventarios LIKE 'id_empresa'");
if ($clsConsulta->numrows > 0) $invHasEmpresa = true;

$chkRes = $clsConsulta->consultaGeneral("SHOW COLUMNS FROM inventarios_reservas LIKE 'id_empresa'");
if ($clsConsulta->numrows > 0) $resHasEmpresa = true;

$invEmpresaWhere = ($invHasEmpresa && $idEmpresa > 0) ? " AND i.id_empresa = {$idEmpresa}" : "";
$resEmpresaWhere = ($resHasEmpresa && $idEmpresa > 0) ? " AND r.id_empresa = {$idEmpresa}" : "";

// Datos
// ✅ existencia = disponible = total - reservado (vigente)
$con = "SELECT
    p.id_producto,
    p.clave,
    p.nombre,
    p.imagen,
    u.nombre_unidad,

    COALESCE(inv.cantidad, 0) AS existencia_total,
    COALESCE(res.reservado, 0) AS reservado,
    (COALESCE(inv.cantidad, 0) - COALESCE(res.reservado, 0)) AS existencia

FROM cat_productos p
INNER JOIN cat_almacenes a ON a.id = p.id_almacen
LEFT JOIN cat_unidades_de_medida u ON p.unidad_medida = u.id_unidad

LEFT JOIN (
    SELECT id_producto, id_almacen, SUM(cantidad) AS cantidad
    FROM inventarios i
    WHERE i.id_almacen = {$idAlmacen}
    {$invEmpresaWhere}
    GROUP BY id_producto, id_almacen
) inv ON inv.id_producto = p.id_producto AND inv.id_almacen = p.id_almacen

LEFT JOIN (
    SELECT id_producto, id_almacen, SUM(cantidad) AS reservado
    FROM inventarios_reservas r
    WHERE r.id_almacen = {$idAlmacen}
    {$resEmpresaWhere}
    AND r.estatus = 'reservado'
    AND (r.vence_at IS NULL OR r.vence_at > NOW())
    GROUP BY id_producto, id_almacen
) res ON res.id_producto = p.id_producto AND res.id_almacen = p.id_almacen

{$whereBase}
{$searchSql}
{$orderBy}
LIMIT {$start}, {$length}";

$rs = $clsConsulta->consultaGeneral($con);

$data = [];
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $val) {

        $id = (int)$val['id_producto'];

        $clave  = safe_html($val['clave']);
        $nombre = safe_html($val['nombre']);
        $unidad = safe_html($val['nombre_unidad']);

        $existencia = is_numeric($val['existencia']) ? $val['existencia'] : 0;

        // Foto / Ver / Modificar
        $foto = '<div class="text-center"><i class="fas fa-image fa-lg text-info" onclick="fotos(' . $id . ');" style="cursor:pointer"></i></div>';

        if (!empty($val['imagen'])) {
            $img = safe_html($val['imagen']);
            $foto = '<div class="text-center">
                <img class="me-3" src="../img/productos/mini-' . $img . '" title="ver-foto" data-bs-toggle="modal" data-bs-target="#modalVerfoto"
                    style="cursor:pointer; height: 20px;" onclick="fnVerFoto(\'' . $img . '\',\'' . $nombre . '\');">
                <i class="fas fa-image fa-lg text-info" title="Modificar Foto" onclick="fotos(' . $id . ');" style="cursor:pointer"></i>
            </div>';
        }

        $editar = '<div class="text-center"><i class="fas fa-edit fa-lg text-info" onclick="editar(' . $id . ');" style="cursor:pointer"></i></div>';
        $borrar = '<div class="text-center"><i class="fas fa-trash-alt fa-lg text-danger" onclick="borrar(' . $id . ',\'' . $nombre . '\');" style="cursor:pointer"></i></div>';

        $data[] = [
            $clave,
            $nombre,
            '<div class="text-end">' . $existencia . '</div>', // ✅ ahora es DISPONIBLE
            $unidad,
            $foto,
            $editar,
            $borrar
        ];
    }
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);
