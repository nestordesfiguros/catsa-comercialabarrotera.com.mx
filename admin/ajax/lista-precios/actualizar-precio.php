<?php
// admin/ajax/lista-precios/actualizar-precio.php
session_start();
include '../../lib/clsConsultas.php';
header('Content-Type: application/json');

$clsConsulta = new Consultas();

$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$idProducto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$lista      = isset($_POST['lista']) ? (int)$_POST['lista'] : 1;
$idAlmacen  = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;
$precioRaw  = isset($_POST['precio']) ? $_POST['precio'] : '';

if ($idEmpresa <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Empresa inválida en sesión']);
    exit;
}
if ($idProducto <= 0 || $idAlmacen <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
    exit;
}
if ($lista < 1 || $lista > 5) $lista = 1;

// validar precio
$precio = (float)str_replace(',', '', trim((string)$precioRaw));
if (!is_numeric($precioRaw) && $precioRaw !== '0' && $precioRaw !== '0.00') {
    // fallback por si mandan string raro
    if (!is_finite($precio)) {
        echo json_encode(['ok' => false, 'msg' => 'Precio inválido']);
        exit;
    }
}
if ($precio < 0) {
    echo json_encode(['ok' => false, 'msg' => 'El precio no puede ser negativo']);
    exit;
}

// campo por lista
$precioField = 'precio01';
if ($lista === 2) $precioField = 'precio02';
if ($lista === 3) $precioField = 'precio03';
if ($lista === 4) $precioField = 'precio04';
if ($lista === 5) $precioField = 'precio05';

// validar que almacén pertenece a empresa
$sqlAlm = "SELECT id FROM cat_almacenes WHERE id = {$idAlmacen} AND estatus = 1 AND id_empresa = {$idEmpresa} LIMIT 1";
$rsAlm = $clsConsulta->consultaGeneral($sqlAlm);
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Almacén inválido o no pertenece a tu empresa']);
    exit;
}

// validar producto en ese almacén
$sqlP = "SELECT id_producto FROM cat_productos
         WHERE id_producto = {$idProducto} AND id_almacen = {$idAlmacen} AND estatus = 1
         LIMIT 1";
$rsP = $clsConsulta->consultaGeneral($sqlP);
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Producto no encontrado en el almacén seleccionado']);
    exit;
}

// update
$sqlUpd = "UPDATE cat_productos
           SET {$precioField} = " . number_format($precio, 2, '.', '') . "
           WHERE id_producto = {$idProducto} AND id_almacen = {$idAlmacen} AND estatus = 1
           LIMIT 1";

$ok = $clsConsulta->guardarGeneral($sqlUpd);

if ($ok) {
    echo json_encode(['ok' => true, 'precio' => number_format($precio, 2, '.', '')]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'No se pudo actualizar en BD']);
}
