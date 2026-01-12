<?php
// ajax/pedidos/detalle.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
function safe($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($idEmpresa <= 0 || $id <= 0) {
    echo '<div class="text-danger">Solicitud inválida</div>';
    exit;
}

/* Verifica que el pedido sea de la empresa */
$existe = $clsConsulta->consultaGeneral("SELECT id, fecha, total FROM cab_pedidos WHERE id={$id} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows === 0) {
    echo '<div class="text-warning">Pedido no encontrado</div>';
    exit;
}

/* Renderiza tu detalle */
$con = "SELECT mp.cantidad, mp.precio, cp.clave, cp.nombre
        FROM mov_pedidos mp
        INNER JOIN cat_productos cp ON mp.id_producto = cp.id_producto
        WHERE mp.id_pedido = {$id}";
$rs = $clsConsulta->consultaGeneral($con);

echo '<table class="table table-sm">';
echo '<thead><tr><th>Cantidad</th><th>Clave</th><th>Producto</th><th class="text-end">Precio</th><th class="text-end">Importe</th></tr></thead><tbody>';
$total = 0;
if (is_array($rs)) {
    foreach ($rs as $r) {
        $cant = (float)($r['cantidad'] ?? 0);
        $pre  = (float)($r['precio'] ?? 0);
        $imp  = $cant * $pre;
        $total += $imp;
        echo '<tr>';
        echo '<td>' . number_format($cant, 2) . '</td>';
        echo '<td>' . safe($r['clave']) . '</td>';
        echo '<td>' . safe($r['nombre']) . '</td>';
        echo '<td class="text-end">' . number_format($pre, 2) . '</td>';
        echo '<td class="text-end">' . number_format($imp, 2) . '</td>';
        echo '</tr>';
    }
}
echo '</tbody><tfoot><tr><th colspan="4" class="text-end">Total</th><th class="text-end">' . number_format($total, 2) . '</th></tr></tfoot></table>';
