<?php
// ajax/pedidos/modificar.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idUsuario = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
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
$existe = $clsConsulta->consultaGeneral("SELECT id, id_cliente FROM cab_pedidos WHERE id={$idPedido} AND id_empresa={$idEmpresa} LIMIT 1");
if ($clsConsulta->numrows === 0) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$pedido = $existe[1];
$id_cliente = (int)$pedido['id_cliente'];

// Verificar disponibilidad considerando reservas existentes (excluyendo las del mismo pedido)
for ($i = 0; $i < count($prod); $i++) {
    $idp = (int)$prod[$i];
    $nuevaCantidad = (float)$can[$i];

    if ($idp > 0 && $nuevaCantidad > 0) {
        // Obtener cantidad actual en el pedido
        $cantidadActual = 0;
        $detalleActual = $clsConsulta->consultaGeneral("SELECT cantidad FROM mov_pedidos WHERE id_pedido = $idPedido AND id_producto = $idp LIMIT 1");
        if ($clsConsulta->numrows > 0) {
            $cantidadActual = (float)$detalleActual[1]['cantidad'];
        }

        // Solo verificar si la nueva cantidad es mayor
        if ($nuevaCantidad > $cantidadActual) {
            $diferencia = $nuevaCantidad - $cantidadActual;

            // Obtener stock disponible (inventario - reservas activas)
            $sqlStock = "SELECT 
                COALESCE(SUM(i.cantidad), 0) - 
                COALESCE(SUM(CASE WHEN ir.estatus = 'reservado' THEN ir.cantidad ELSE 0 END), 0) as disponible
                FROM inventarios i 
                LEFT JOIN inventarios_reservas ir ON i.id_producto = ir.id_producto AND i.id_almacen = ir.id_almacen
                WHERE i.id_producto = $idp AND i.id_almacen = 1";

            $stockData = $clsConsulta->consultaGeneral($sqlStock);
            $disponible = $stockData[1]['disponible'] ?? 0;

            if ($diferencia > $disponible) {
                $productoInfo = $clsConsulta->consultaGeneral("SELECT nombre FROM cat_productos WHERE id_producto = $idp LIMIT 1");
                $nombreProducto = $productoInfo[1]['nombre'] ?? 'Producto';
                echo json_encode(['success' => false, 'message' => "Stock insuficiente para $nombreProducto. Disponible: $disponible"]);
                exit;
            }
        }
    }
}

// Liberar reservas actuales del pedido
$productosActuales = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad FROM mov_pedidos WHERE id_pedido = $idPedido");
if ($clsConsulta->numrows > 0) {
    foreach ($productosActuales as $producto) {
        $id_producto = (int)$producto['id_producto'];
        $cantidad = (float)$producto['cantidad'];

        // Buscar y liberar reservas para este producto y pedido
        $sqlLiberar = "UPDATE inventarios_reservas 
                      SET estatus = 'liberado' 
                      WHERE id_producto = $id_producto 
                      AND id_cliente = $id_cliente 
                      AND cantidad = $cantidad 
                      AND estatus = 'reservado'
                      ORDER BY created_at DESC 
                      LIMIT 1";
        $clsConsulta->aplicaquery($sqlLiberar);
    }
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

        // Crear nuevas reservas
        $vence_at = date('Y-m-d H:i:s', strtotime('+3 days'));
        $sqlReserva = "INSERT INTO inventarios_reservas 
                      (id_producto, id_almacen, cantidad, id_usuario, id_cliente, estatus, vence_at, created_at)
                      VALUES 
                      ($idp, 1, $c, $idUsuario, $id_cliente, 'reservado', '$vence_at', NOW())";
        $clsConsulta->guardarGeneral($sqlReserva);
    }
}

/* Actualizar total */
$clsConsulta->guardarGeneral("UPDATE cab_pedidos SET total={$total} WHERE id={$idPedido} AND id_empresa={$idEmpresa}");

echo json_encode(['success' => true]);
