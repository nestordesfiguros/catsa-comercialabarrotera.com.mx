<?php
// ajax/almacen-entradas/obtener-productos-odc.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

$response = ['success' => false, 'productos' => []];

$odcId = isset($_POST['odc_id']) && is_numeric($_POST['odc_id']) ? (int)$_POST['odc_id'] : 0;
if ($idEmpresa <= 0 || $odcId <= 0) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Validar ODC pertenece a empresa y está procesada
$chk = $clsConsulta->consultaGeneral("SELECT id FROM cab_compras WHERE id={$odcId} AND id_empresa={$idEmpresa} AND estatus='procesada' LIMIT 1");
if ($clsConsulta->numrows < 1) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$con = "SELECT 
            mov.id_producto,
            prod.clave,
            prod.nombre,
            mov.cantidad,
            mov.precio,
            u.nombre_unidad AS unidad
        FROM mov_compras mov
        INNER JOIN cat_productos prod ON mov.id_producto = prod.id_producto
        LEFT JOIN cat_unidades_de_medida u ON prod.unidad_medida = u.id_unidad
        INNER JOIN cab_compras c ON c.id = mov.id_orden_compra
        WHERE mov.id_orden_compra = {$odcId}
          AND c.id_empresa = {$idEmpresa}
          AND (c.estatus IS NULL OR c.estatus <> 'cancelada')
          AND prod.estatus = 1";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0 && is_array($rs)) {
    $response['success'] = true;
    foreach ($rs as $producto) {
        $response['productos'][] = [
            'id_producto' => (int)$producto['id_producto'],
            'clave' => $producto['clave'],
            'nombre' => $producto['nombre'],
            'cantidad' => (float)$producto['cantidad'],
            'precio' => (float)$producto['precio'],
            'unidad' => $producto['unidad']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
