<?php
// ajax/remisiones/actualizar.php
session_start();
header('Content-Type: text/html; charset=utf-8');

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Empresa
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$idRemision = isset($_POST['id_remision']) ? (int)$_POST['id_remision'] : 0;
$id_cliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
$direccion_envio = isset($_POST['direccion_envio']) ? trim((string)$_POST['direccion_envio']) : '';
$fecha = isset($_POST['fecha']) ? trim((string)$_POST['fecha']) : '';

if ($idRemision <= 0) {
    echo 'Remisión inválida';
    exit;
}
if ($id_cliente <= 0) {
    echo 'Cliente inválido';
    exit;
}
if ($fecha === '') {
    echo 'Fecha inválida';
    exit;
}

$whereEmpresa = "";
if ($idEmpresa > 0) $whereEmpresa = " AND id_empresa = {$idEmpresa} ";

$direccion_sql = $clsConsulta->escape($direccion_envio);

// Validar remisión pendiente y de la empresa
$chk = $clsConsulta->consultaGeneral("SELECT id, LOWER(estatus) AS estatus FROM cab_remisiones WHERE id = {$idRemision} {$whereEmpresa} LIMIT 1");
if ($clsConsulta->numrows <= 0) {
    echo 'No autorizado o no existe';
    exit;
}
if (($chk[1]['estatus'] ?? '') !== 'pendiente') {
    echo 'Solo se pueden editar remisiones pendientes';
    exit;
}

// Validar cliente de la empresa
if ($idEmpresa > 0) {
    $cli = $clsConsulta->consultaGeneral("SELECT id FROM cat_clientes WHERE id = {$id_cliente} AND id_empresa = {$idEmpresa} AND estatus=1 LIMIT 1");
    if ($clsConsulta->numrows <= 0) {
        echo 'Cliente no autorizado';
        exit;
    }
}

if (!isset($_POST['producto_id']) || !is_array($_POST['producto_id']) || count($_POST['producto_id']) === 0) {
    echo 'No se ha capturado ningún producto';
    exit;
}

$producto_id  = $_POST['producto_id'];
$cantidad     = $_POST['cantidad'] ?? [];
$precio_venta = $_POST['precio_venta'] ?? [];

$clsConsulta->aplicaquery("START TRANSACTION");

// Borrar detalle anterior
$clsConsulta->aplicaquery("DELETE FROM mov_remisiones WHERE id_remision = {$idRemision}");

$totalCalc = 0.0;

// Productos permitidos por empresa
$whereEmpresaProd = "";
if ($idEmpresa > 0) $whereEmpresaProd = " AND (id_empresa = {$idEmpresa} OR id_empresa IS NULL OR id_empresa = 0) ";

for ($i = 0; $i < count($producto_id); $i++) {
    $idp = (int)($producto_id[$i] ?? 0);
    $qty = (float)($cantidad[$i] ?? 0);
    $pu  = (float)($precio_venta[$i] ?? 0);

    if ($idp <= 0 || $qty <= 0) continue;
    if ($pu < 0) $pu = 0.0;

    // Validar que el producto sea visible para la empresa
    if ($idEmpresa > 0) {
        $pchk = $clsConsulta->consultaGeneral("SELECT id_producto FROM cat_productos WHERE id_producto = {$idp} {$whereEmpresaProd} AND estatus=1 LIMIT 1");
        if ($clsConsulta->numrows <= 0) {
            $clsConsulta->aplicaquery("ROLLBACK");
            echo 'Producto no autorizado: ' . $idp;
            exit;
        }
    }

    $precio = $qty * $pu;
    $totalCalc += $precio;

    $sqlDetalle = "INSERT INTO mov_remisiones (id_remision, id_producto, cantidad, precio, precio_unitario)
                   VALUES ({$idRemision}, {$idp}, {$qty}, {$precio}, {$pu})";
    $clsConsulta->aplicaquery($sqlDetalle);
}

// Actualizar cabecera + total
$sqlCab = "
    UPDATE cab_remisiones
    SET id_cliente = {$id_cliente},
        fecha = '" . addslashes($fecha) . "',
        direccion_envio = {$direccion_sql},
        total = {$totalCalc}
    WHERE id = {$idRemision}
    {$whereEmpresa}
    LIMIT 1
";
$ok = $clsConsulta->aplicaquery($sqlCab);

if (!$ok) {
    $clsConsulta->aplicaquery("ROLLBACK");
    echo 'No se pudo actualizar la remisión';
    exit;
}

$clsConsulta->aplicaquery("COMMIT");
echo 'success';
