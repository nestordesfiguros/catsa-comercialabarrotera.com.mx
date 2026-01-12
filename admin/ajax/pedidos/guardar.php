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
$id_vendedor     = isset($_POST['id_vendedor']) && $_POST['id_vendedor'] !== '' ? (int)$_POST['id_vendedor'] : null;
$id_almacen      = (int)($_POST['id_almacen'] ?? 0);
$direccion_envio = $_POST['direccion_envio'] ?? '';
$productos       = $_POST['producto_id'] ?? [];
$cantidades      = $_POST['cantidad'] ?? [];
$precios         = $_POST['precio_venta'] ?? [];

if ($id_cliente <= 0 || $id_almacen <= 0 || !is_array($productos) || count($productos) === 0) {
    echo 'error';
    exit;
}

function colExists($clsConsulta, $tabla, $col)
{
    $r = $clsConsulta->consultaGeneral("SHOW COLUMNS FROM {$tabla} LIKE '{$col}'");
    return ($clsConsulta->numrows > 0);
}

// validar almacén pertenece a empresa
$chkAlm = $clsConsulta->consultaGeneral("SELECT id FROM cat_almacenes WHERE id = {$id_almacen} AND id_empresa = {$idEmpresa} AND estatus = 1 LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo 'error_almacen';
    exit;
}

// validar cliente pertenece a empresa
$rc = $clsConsulta->consultaGeneral("SELECT id, lista_precios FROM cat_clientes WHERE id = {$id_cliente} AND id_empresa = {$idEmpresa} AND estatus = 1 LIMIT 1");
if ($clsConsulta->numrows < 1) {
    echo 'error_cliente';
    exit;
}

// lista de precios del cliente (default 1)
$lista = 1;
$tmp = (int)($rc[1]['lista_precios'] ?? 0);
if ($tmp >= 1 && $tmp <= 5) $lista = $tmp;
$precioCol = 'precio0' . $lista;

// helper: disponible por producto en almacén (inventarios - reservas vigentes)
function disponibleProducto($clsConsulta, $idEmpresa, $idAlmacen, $idProducto)
{
    $sql = "
        SELECT
            IFNULL(inv.cantidad, 0) - IFNULL(res.reservado, 0) AS disponible
        FROM
            (SELECT SUM(cantidad) AS cantidad
             FROM inventarios
             WHERE id_empresa = {$idEmpresa} AND id_almacen = {$idAlmacen} AND id_producto = {$idProducto}) inv
        LEFT JOIN
            (SELECT SUM(cantidad) AS reservado
             FROM inventarios_reservas
             WHERE id_empresa = {$idEmpresa}
               AND estatus = 'reservado'
               AND id_almacen = {$idAlmacen}
               AND id_producto = {$idProducto}
               AND (vence_at IS NULL OR vence_at > NOW())
            ) res
        ON 1=1
    ";
    $r = $clsConsulta->consultaGeneral($sql);
    return (float)($r[1]['disponible'] ?? 0);
}

// validar stock + validar precio mínimo por lista (server-side)
for ($i = 0; $i < count($productos); $i++) {
    $idp = (int)$productos[$i];
    $can = (float)($cantidades[$i] ?? 0);
    $pre = (float)($precios[$i] ?? 0);

    if ($idp <= 0 || $can <= 0) continue;

    // CAMINO 1: producto debe pertenecer al almacén seleccionado + empresa
    $rp = $clsConsulta->consultaGeneral("
        SELECT p.nombre, IFNULL(p.{$precioCol}, 0) AS precio_min
        FROM cat_productos p
        INNER JOIN cat_almacenes a ON a.id = p.id_almacen
        WHERE p.id_producto = {$idp}
          AND p.estatus = 1
          AND p.id_almacen = {$id_almacen}
          AND a.id_empresa = {$idEmpresa}
          AND a.estatus = 1
        LIMIT 1
    ");
    if ($clsConsulta->numrows < 1) {
        echo 'error';
        exit;
    }

    $nombreProducto = $rp[1]['nombre'] ?? 'Producto';
    $precioMin = (float)($rp[1]['precio_min'] ?? 0);

    if ($pre < $precioMin) {
        echo "error:precio_invalido:{$nombreProducto}:" . number_format($precioMin, 2, '.', '');
        exit;
    }

    $disponible = disponibleProducto($clsConsulta, $idEmpresa, $id_almacen, $idp);
    if ($can > $disponible) {
        echo "error:stock_insuficiente:{$nombreProducto}:" . (int)$disponible;
        exit;
    }
}

// insertar cabecera
$tieneIdAlmacen = colExists($clsConsulta, 'cab_pedidos', 'id_almacen');

$campos = "id_empresa, fecha, id_cliente, id_vendedor, direccion_envio, estatus, total, id_usuario";
$vals   = "{$idEmpresa}, '" . addslashes($fecha) . "', {$id_cliente}, " . ($id_vendedor ? $id_vendedor : "NULL") . ",
           '" . addslashes($direccion_envio) . "', 'pendiente', 0, {$idUsuario}";

if ($tieneIdAlmacen) {
    $campos .= ", id_almacen";
    $vals   .= ", {$id_almacen}";
}

$conCab = "INSERT INTO cab_pedidos ({$campos}) VALUES ({$vals})";
$ok = $clsConsulta->guardarGeneral($conCab);
if (!$ok) {
    echo 'error';
    exit;
}

$idPedido = $clsConsulta->ultimoid;
$total = 0;

// detectar si reservas soporta id_pedido
$tieneIdPedidoReserva = colExists($clsConsulta, 'inventarios_reservas', 'id_pedido');

// insertar detalle + crear reservas
for ($i = 0; $i < count($productos); $i++) {
    $idp = (int)$productos[$i];
    $can = (float)($cantidades[$i] ?? 0);
    $pre = (float)($precios[$i] ?? 0);

    if ($idp <= 0 || $can <= 0) continue;

    $conDet = "INSERT INTO mov_pedidos (id_pedido, id_producto, cantidad, precio)
               VALUES ({$idPedido}, {$idp}, {$can}, {$pre})";
    $clsConsulta->guardarGeneral($conDet);

    $total += ($can * $pre);

    $vence_at = date('Y-m-d H:i:s', strtotime('+3 days'));

    // columnas base
    $cols = "id_empresa, id_producto, id_almacen, cantidad, id_usuario, id_cliente, estatus, vence_at, created_at";
    $vals = "{$idEmpresa}, {$idp}, {$id_almacen}, {$can}, {$idUsuario}, {$id_cliente}, 'reservado', '{$vence_at}', NOW()";

    // ✅ si existe, ligamos reserva al pedido
    if ($tieneIdPedidoReserva) {
        $cols = "id_empresa, id_producto, id_almacen, id_pedido, cantidad, id_usuario, id_cliente, estatus, vence_at, created_at";
        $vals = "{$idEmpresa}, {$idp}, {$id_almacen}, {$idPedido}, {$can}, {$idUsuario}, {$id_cliente}, 'reservado', '{$vence_at}', NOW()";
    }

    $sqlReserva = "INSERT INTO inventarios_reservas ({$cols}) VALUES ({$vals})";
    $clsConsulta->guardarGeneral($sqlReserva);
}

$clsConsulta->guardarGeneral("UPDATE cab_pedidos SET total = {$total} WHERE id = {$idPedido} AND id_empresa = {$idEmpresa}");

echo 'success';
