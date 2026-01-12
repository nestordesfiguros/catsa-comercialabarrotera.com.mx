<?php
// admin/ajax/lista-precios/ajuste-global.php
session_start();
include '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$clsConsulta = new Consultas();

$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$lista     = isset($_POST['lista']) ? (int)$_POST['lista'] : 1;
$tipo      = $_POST['tipo'] ?? 'porcentaje'; // porcentaje | monto
$operacion = $_POST['operacion'] ?? 'aumentar'; // aumentar | disminuir
$scope     = $_POST['scope'] ?? 'todos'; // todos | filtrados
$search    = trim($_POST['search'] ?? '');
$idAlmacen = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

$valorRaw  = $_POST['valor'] ?? '';
$valor = (float)str_replace(',', '', trim((string)$valorRaw));

if ($idEmpresa <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Empresa inválida en sesión']);
    exit;
}
if ($idAlmacen <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Selecciona un almacén']);
    exit;
}
if ($lista < 1 || $lista > 5) $lista = 1;
if ($valor <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Valor inválido']);
    exit;
}

$precioField = 'precio01';
if ($lista === 2) $precioField = 'precio02';
if ($lista === 3) $precioField = 'precio03';
if ($lista === 4) $precioField = 'precio04';
if ($lista === 5) $precioField = 'precio05';

// Validar almacén pertenece a empresa
$sqlAlm = "SELECT id FROM cat_almacenes WHERE id = {$idAlmacen} AND estatus = 1 AND id_empresa = {$idEmpresa} LIMIT 1";
$rsAlm = $clsConsulta->consultaGeneral($sqlAlm);
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Almacén inválido o no pertenece a tu empresa']);
    exit;
}

// Construir SET (sin negativos)
$sign = ($operacion === 'disminuir') ? -1 : 1;

if ($tipo === 'monto') {
    $delta = $sign * $valor;
    // precio = GREATEST(precio + delta, 0)
    $setExpr = "{$precioField} = GREATEST(COALESCE({$precioField},0) + (" . number_format($delta, 2, '.', '') . "), 0)";
} else {
    // porcentaje
    $factor = 1 + ($sign * ($valor / 100.0));
    $setExpr = "{$precioField} = GREATEST(COALESCE({$precioField},0) * (" . number_format($factor, 6, '.', '') . "), 0)";
}

// WHERE (empresa por almacén, NO por cat_productos.id_empresa)
$where = "WHERE p.estatus = 1
          AND p.id_almacen = {$idAlmacen}
          AND a.id_empresa = {$idEmpresa}
          AND a.estatus = 1";

// filtro por búsqueda si scope=filtrados
if ($scope === 'filtrados' && $search !== '') {
    $sv = addslashes($search);
    $where .= " AND (p.clave LIKE '%{$sv}%' OR p.nombre LIKE '%{$sv}%')";
}

$sql = "UPDATE cat_productos p
        INNER JOIN cat_almacenes a ON a.id = p.id_almacen
        SET p.{$setExpr}
        {$where}";

$ok = $clsConsulta->guardarGeneral($sql);

if ($ok) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'No se pudo aplicar en BD']);
}
