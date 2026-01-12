<?php
// ajax/pedidos/modificar.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode(['success' => false, 'message' => 'Empresa inválida']);
    exit;
}

$idPedido = (int)($_POST['id_pedido'] ?? 0);
$direccion = $_POST['direccion_envio'] ?? '';
$prod = $_POST['producto_id'] ?? [];
$can  = $_POST['cantidad'] ?? [];
$pre  = $_POST['precio_venta'] ?? [];

if ($idPedido <= 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido inválido']);
    exit;
}

/* Verifica pertenencia */
$existe = $clsConsulta->consultaGeneral("SELECT id FROM cab_pedidos WHERE id={$idPedido} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows === 0) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

/* Actualiza domicilio */
$clsConsulta->guardarGeneral("UPDATE cab_pedidos SET direccion_envio='" . addslashes($direccion) . "' WHERE id={$idPedido} AND id_empresa={$idEmpresa}");

/* Reemplaza detalle (simple y efectivo) */
$clsConsulta->guardarGeneral("DELETE FROM mov_pedidos WHERE id_pedido={$idPedido}");

$total = 0;
for ($i = 0; $i < count($prod); $i++) {
    $idp = (int)$prod[$i];
    $c = (float)$can[$i];
    $p = (float)$pre[$i];
    if ($idp > 0 && $c > 0) {
        $clsConsulta->guardarGeneral("INSERT INTO mov_pedidos (id_pedido,id_producto,cantidad,precio) VALUES ({$idPedido},{$idp},{$c},{$p})");
        $total += $c * $p;
    }
}
/* Total */
$clsConsulta->guardarGeneral("UPDATE cab_pedidos SET total={$total} WHERE id={$idPedido} AND id_empresa={$idEmpresa}");

echo json_encode(['success' => true]);
