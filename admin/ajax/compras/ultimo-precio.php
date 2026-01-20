<?php
// ajax/compras/ultimo-precio.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa   = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idProducto  = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$idProveedor = isset($_POST['id_proveedor']) ? (int)$_POST['id_proveedor'] : 0;

if ($idEmpresa <= 0 || $idProducto <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$whereProveedor = "";
if ($idProveedor > 0) {
    $whereProveedor = " AND c.id_proveedor={$idProveedor} ";
}

$sql = "
    SELECT m.precio
    FROM mov_compras m
    INNER JOIN cab_compras c ON c.id = m.id_orden_compra
    WHERE c.id_empresa={$idEmpresa}
      AND m.id_producto={$idProducto}
      {$whereProveedor}
    ORDER BY c.id DESC
    LIMIT 1
";

$rs = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    echo json_encode([
        'success' => true,
        'precio' => $rs[1]['precio']
    ]);
    exit;
}

echo json_encode(['success' => false]);
