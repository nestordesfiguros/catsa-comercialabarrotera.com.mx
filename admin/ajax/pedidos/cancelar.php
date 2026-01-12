<?php
// ajax/pedidos/cancelar.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idUsuario = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;

if ($idEmpresa <= 0) {
    echo 'error';
    exit;
}

$idPedido = isset($_POST['id_pedido']) ? (int)$_POST['id_pedido'] : 0;
if ($idPedido <= 0) {
    echo 'error';
    exit;
}

function colExists($clsConsulta, $tabla, $col)
{
    $clsConsulta->consultaGeneral("SHOW COLUMNS FROM {$tabla} LIKE '{$col}'");
    return ($clsConsulta->numrows > 0);
}

$tieneIdPedidoReserva = colExists($clsConsulta, 'inventarios_reservas', 'id_pedido');

// cargar pedido
$rp = $clsConsulta->consultaGeneral("
    SELECT id, estatus
    FROM cab_pedidos
    WHERE id = {$idPedido} AND id_empresa = {$idEmpresa}
    LIMIT 1
");
if ($clsConsulta->numrows < 1) {
    echo 'error_pedido';
    exit;
}

$estatus = strtolower((string)($rp[1]['estatus'] ?? ''));
if ($estatus !== 'pendiente') {
    echo 'error_estatus';
    exit;
}

// cancelar cabecera
$okCab = $clsConsulta->guardarGeneral("
    UPDATE cab_pedidos
    SET estatus = 'cancelado'
    WHERE id = {$idPedido} AND id_empresa = {$idEmpresa}
    LIMIT 1
");
if (!$okCab) {
    echo 'error';
    exit;
}

// liberar reservas del pedido (si hay id_pedido)
if ($tieneIdPedidoReserva) {
    $clsConsulta->guardarGeneral("
        UPDATE inventarios_reservas
        SET estatus = 'cancelado'
        WHERE id_empresa = {$idEmpresa}
          AND id_pedido = {$idPedido}
          AND estatus = 'reservado'
    ");
    echo 'success';
    exit;
}

// si NO existe id_pedido en reservas, no hay forma segura de liberar sin afectar otros pedidos
echo 'error_reservas_sin_pedido';
