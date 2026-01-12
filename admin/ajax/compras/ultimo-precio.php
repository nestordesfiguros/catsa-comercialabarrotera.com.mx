<?php
// ajax/compras/ultimo-precio.php
session_start();
header('Content-Type: application/json');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    $idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

    $id_producto  = isset($_POST['id_producto'])  ? (int) $_POST['id_producto']  : 0;
    $id_proveedor = isset($_POST['id_proveedor']) ? (int) $_POST['id_proveedor'] : 0;

    if ($idEmpresa <= 0 || $id_producto <= 0) {
        echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
        exit;
    }

    $filtroProveedor = ($id_proveedor > 0) ? " AND c.id_proveedor = {$id_proveedor} " : "";

    $sql = "
        SELECT mc.precio
        FROM mov_compras mc
        INNER JOIN cab_compras c ON c.id = mc.id_orden_compra
        WHERE mc.id_producto = {$id_producto}
          AND c.id_empresa = {$idEmpresa}
          AND (c.estatus IS NULL OR c.estatus <> 'cancelada')
          {$filtroProveedor}
        ORDER BY COALESCE(c.fecha, c.fecha_registro) DESC, mc.id DESC
        LIMIT 1
    ";

    $rs = $clsConsulta->consultaGeneral($sql);

    if ($clsConsulta->numrows > 0) {
        $precio = isset($rs[1]['precio']) ? (float)$rs[1]['precio'] : 0.0;
        echo json_encode(['success' => true, 'precio' => $precio]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
