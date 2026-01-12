<?php
// admin/ajax/productos/modificar.php

session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function toSqlString($clsConsulta, $raw)
{
    $s = $clsConsulta->sanitizar($raw);
    $s = trim((string)$s);

    if (strlen($s) >= 2 && $s[0] === "'" && substr($s, -1) === "'") {
        $s = substr($s, 1, -1);
    }

    $s = trim($s);
    return "'" . $s . "'";
}

// Empresa actual
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

// Inputs
$id_producto       = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$clave_raw         = $_POST['clave'] ?? '';
$nombre_raw        = $_POST['nombre'] ?? '';
$descripcion_raw   = $_POST['descripcion'] ?? '';
$unidad_medida_raw = $_POST['unidad_medida'] ?? '';
$excento           = isset($_POST['excento']) ? (int)$_POST['excento'] : 0;

if ($id_producto <= 0 || trim($clave_raw) === '' || trim($nombre_raw) === '') {
    echo "error_datos";
    exit;
}

$clave_sql         = toSqlString($clsConsulta, $clave_raw);
$nombre_sql        = toSqlString($clsConsulta, $nombre_raw);
$descripcion_sql   = toSqlString($clsConsulta, $descripcion_raw);
$unidad_medida_sql = toSqlString($clsConsulta, $unidad_medida_raw);

// Obtener id_almacen del producto (para validar duplicado en el mismo almacén)
$id_almacen = 0;
$qAlm = "SELECT id_almacen FROM cat_productos WHERE id_producto = {$id_producto} LIMIT 1";
$rAlm = $clsConsulta->consultaGeneral($qAlm);
if ($clsConsulta->numrows > 0 && isset($rAlm[1]['id_almacen'])) {
    $id_almacen = (int)$rAlm[1]['id_almacen'];
}

// Validación duplicado: MISMA CLAVE, MISMO ALMACÉN, MISMA EMPRESA (si hay sesión), excluyendo el producto actual
$joinEmpresa = "";
$whereEmpresa = "";

if ($idEmpresa > 0) {
    $joinEmpresa = " INNER JOIN cat_almacenes a ON a.id = p.id_almacen ";
    $whereEmpresa = " AND a.id_empresa = {$idEmpresa}";
}

$whereAlmacen = "";
if ($id_almacen > 0) {
    $whereAlmacen = " AND p.id_almacen = {$id_almacen}";
}

$dupSql = "SELECT COUNT(*) AS total
           FROM cat_productos p
           {$joinEmpresa}
           WHERE p.clave = {$clave_sql}
             AND p.id_producto <> {$id_producto}
             {$whereAlmacen}
             {$whereEmpresa}";

$dupRes = $clsConsulta->consultaGeneral($dupSql);

$totalDup = 0;
if ($clsConsulta->numrows > 0 && isset($dupRes[1]['total'])) {
    $totalDup = (int)$dupRes[1]['total'];
}

if ($totalDup > 0) {
    echo "error_duplicado";
    exit;
}

// UPDATE (sin precios)
$sql = "
UPDATE cat_productos SET
    clave         = {$clave_sql},
    nombre        = {$nombre_sql},
    descripcion   = {$descripcion_sql},
    unidad_medida = {$unidad_medida_sql},
    excento       = {$excento}
WHERE id_producto = {$id_producto}
";

try {
    $ok = $clsConsulta->aplicaQuery($sql);
    echo $ok ? "success" : "error_sql";
} catch (Exception $e) {
    error_log('Error modificar.php: ' . $e->getMessage());
    echo "error_sql";
}
