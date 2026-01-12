<?php
// ajax/compras/guardar.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function normaliza_monto($valor)
{
    if (!isset($valor)) return "0.00";
    $v = preg_replace('/[^\d\.\-]/', '', (string)$valor);
    if ($v === '' || $v === '.' || $v === '-' || $v === '-.') return "0.00";
    $num = (float)$v;
    return number_format($num, 2, '.', '');
}

function normaliza_cantidad($valor)
{
    if (!isset($valor)) return "0";
    $v = preg_replace('/[^\d\.\-]/', '', (string)$valor);
    if ($v === '' || $v === '.' || $v === '-' || $v === '-.') return "0";
    return (string)(float)$v;
}

$idEmpresa    = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$fecha        = isset($_POST["fecha"]) ? $_POST["fecha"] : date('Y-m-d');
$id_usuario   = isset($_SESSION["id_user"]) ? (int)$_SESSION["id_user"] : 0;
$id_proveedor = isset($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : 0;

$producto_ids = isset($_POST['producto_id']) ? $_POST['producto_id'] : [];
$cantidades   = isset($_POST['cantidad']) ? $_POST['cantidad'] : [];
$precios      = isset($_POST['precio_venta']) ? $_POST['precio_venta'] : [];
$total        = normaliza_monto($_POST['total'] ?? '0');

if ($idEmpresa <= 0 || $id_usuario <= 0 || $id_proveedor <= 0) {
    echo 'error';
    exit;
}
if (!is_array($producto_ids) || count($producto_ids) === 0) {
    echo 'error';
    exit;
}

// Validar proveedor pertenece a empresa
$chkProv = $clsConsulta->consultaGeneral("SELECT id FROM cat_proveedores WHERE id={$id_proveedor} AND id_empresa={$idEmpresa} AND estatus=1 LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo 'error';
    exit;
}

// Insert cabecera compra con id_empresa y estatus
$con = "
    INSERT INTO cab_compras (id_empresa, id_proveedor, fecha, id_usuario, total, estatus)
    VALUES ({$idEmpresa}, '{$id_proveedor}', '{$fecha}', '{$id_usuario}', '{$total}', 'pendiente')
";
$clsConsulta->guardarGeneral($con);
$id_orden_compra = (int)$clsConsulta->ultimoid;

if ($id_orden_compra <= 0) {
    echo 'error';
    exit;
}

// Insert CxP con id_empresa
$sql_cxp = "
    INSERT INTO cuentas_por_pagar (
        id_empresa,
        id_proveedor,
        id_compra,
        fecha,
        monto_total,
        monto_pagado,
        estatus,
        observaciones
    ) VALUES (
        {$idEmpresa},
        '{$id_proveedor}',
        '{$id_orden_compra}',
        '{$fecha}',
        '{$total}',
        0.00,
        'pendiente',
        ''
    )
";
$clsConsulta->guardarGeneral($sql_cxp);

// Insert detalle compra
for ($i = 0; $i < count($producto_ids); $i++) {
    $id_producto = (int)$producto_ids[$i];
    $cantidad_in = $cantidades[$i] ?? '0';
    $precio_in   = $precios[$i]    ?? '0';

    $cantidad = normaliza_cantidad($cantidad_in);
    $precio   = normaliza_monto($precio_in);

    if ($id_producto <= 0) continue;

    $sqlDet = "
        INSERT INTO mov_compras (id_orden_compra, id_producto, cantidad, precio)
        VALUES ('{$id_orden_compra}', '{$id_producto}', '{$cantidad}', '{$precio}')
    ";
    $clsConsulta->aplicaQuery($sqlDet);

    // Actualiza precio de compra (si catálogo por empresa, se filtra; si no, no afecta)
    $sqlUpd = "UPDATE cat_productos SET precio_compra='{$precio}' WHERE id_producto={$id_producto}";
    $clsConsulta->aplicaQuery($sqlUpd);
}

echo 'success';
