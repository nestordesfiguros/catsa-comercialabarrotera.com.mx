<?php
// ajax/pedidos/guardar.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idUsuario = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
if ($idEmpresa <= 0) {
    echo 'error';
    exit;
}

date_default_timezone_set('America/Mexico_City');

$fecha           = $_POST['fecha'] ?? date('Y-m-d');
$id_cliente      = (int)($_POST['cliente_id'] ?? 0);
$direccion_envio = $_POST['direccion_envio'] ?? '';
$productos       = $_POST['producto_id'] ?? [];
$cantidades      = $_POST['cantidad'] ?? [];
$precios         = $_POST['precio_venta'] ?? [];

if ($id_cliente <= 0 || !is_array($productos) || count($productos) === 0) {
    echo 'error';
    exit;
}

$conCab = "INSERT INTO cab_pedidos (id_empresa, fecha, id_cliente, id_vendedor, direccion_envio, estatus, total, id_usuario)
           VALUES ({$idEmpresa}, '" . addslashes($fecha) . "', {$id_cliente}, NULL, '" . addslashes($direccion_envio) . "', 'pendiente', 0, {$idUsuario})";
$ok = $clsConsulta->guardarGeneral($conCab);
if (!$ok) {
    echo 'error';
    exit;
}

/* IMPORTANTE: usar tu propiedad correcta */
$idPedido = $clsConsulta->ultimoid;

$total = 0;
for ($i = 0; $i < count($productos); $i++) {
    $idp = (int)$productos[$i];
    $can = (float)($cantidades[$i] ?? 0);
    $pre = (float)($precios[$i] ?? 0);
    if ($idp > 0 && $can > 0) {
        $conDet = "INSERT INTO mov_pedidos (id_pedido, id_producto, cantidad, precio) 
                   VALUES ({$idPedido}, {$idp}, {$can}, {$pre})";
        $clsConsulta->guardarGeneral($conDet);
        $total += $can * $pre;
    }
}

$clsConsulta->guardarGeneral("UPDATE cab_pedidos SET total = {$total} WHERE id = {$idPedido} AND id_empresa = {$idEmpresa}");

echo 'success';
