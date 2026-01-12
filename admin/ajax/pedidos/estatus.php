<?php
// ajax/pedidos/estatus.php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($idEmpresa <= 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Empresa inválida']);
    exit;
}

$id_pedido = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estatus   = isset($_POST['estatus']) ? trim((string)$_POST['estatus']) : '';

$permitidos = ['pendiente', 'procesado', 'cancelado'];
if ($id_pedido <= 0 || $estatus === '' || !in_array($estatus, $permitidos, true)) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

// Usuario (soporte id_usuario / id_user)
$idUsuario = 0;
if (isset($_SESSION['id_usuario'])) $idUsuario = (int)$_SESSION['id_usuario'];
elseif (isset($_SESSION['id_user'])) $idUsuario = (int)$_SESSION['id_user'];
if ($idUsuario <= 0) $idUsuario = 1;

// Verificar pedido y pertenencia
$sqlCheck = "SELECT id, id_cliente, id_vendedor, fecha, total, direccion_envio, estatus
             FROM cab_pedidos
             WHERE id = {$id_pedido} AND id_empresa = {$idEmpresa}
             LIMIT 1";
$pedidoCheck = $clsConsulta->consultaGeneral($sqlCheck);
if ($clsConsulta->numrows < 1) {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado o pedido no encontrado']);
    exit;
}
$pedido = $pedidoCheck[1];

$id_cliente      = (int)$pedido['id_cliente'];
$id_vendedor     = (int)($pedido['id_vendedor'] ?? 0);
$fecha           = $pedido['fecha'] ?? date('Y-m-d H:i:s');
$total           = isset($pedido['total']) ? (float)$pedido['total'] : 0.0;
$direccion_envio = isset($pedido['direccion_envio']) ? (string)$pedido['direccion_envio'] : '';

$estatusActual = strtolower((string)($pedido['estatus'] ?? ''));

// Si ya está procesado y piden procesado, no dupliques: regresa remisión existente si está ligada
if ($estatus === 'procesado' && $estatusActual === 'procesado') {
    // Intentar recuperar remisión ligada (si usas id_pedido en cab_remisiones)
    $rsR = $clsConsulta->consultaGeneral("SELECT id FROM cab_remisiones WHERE id_pedido = {$id_pedido} LIMIT 1");
    $idRem = ($clsConsulta->numrows > 0) ? (int)($rsR[1]['id'] ?? 0) : 0;

    echo json_encode([
        'success' => true,
        'mensaje' => 'El pedido ya está procesado.',
        'remision_id' => $idRem
    ]);
    exit;
}

// 1) Actualizar estatus del pedido
$sqlEstatus = "UPDATE cab_pedidos
               SET estatus = '" . addslashes($estatus) . "'
               WHERE id = {$id_pedido} AND id_empresa = {$idEmpresa}
               LIMIT 1";
$ok = $clsConsulta->aplicaquery($sqlEstatus);

if (!$ok) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar estatus']);
    exit;
}

// 2) Manejo según estatus
if ($estatus === 'procesado') {

    // Crear remisión PENDIENTE (para continuar en remisiones-editar/{id})
    $fecha_emision = date('Y-m-d', strtotime((string)$fecha));
    $direccion_sql = $clsConsulta->escape($direccion_envio);

    // Plazo crédito del cliente (si aplica; queda guardado para tu flujo)
    $plazo_credito = 30;
    $clienteDatos = $clsConsulta->consultaGeneral("
        SELECT plazo_credito
        FROM cat_clientes
        WHERE id = {$id_cliente} AND id_empresa = {$idEmpresa}
        LIMIT 1
    ");
    if ($clsConsulta->numrows > 0 && isset($clienteDatos[1]['plazo_credito']) && (int)$clienteDatos[1]['plazo_credito'] > 0) {
        $plazo_credito = (int)$clienteDatos[1]['plazo_credito'];
    }

    // Cabecera remisión (estatus en minúsculas para que filtros y cambiar-estatus funcionen)
    $sqlRemision = "
        INSERT INTO cab_remisiones
        (id_empresa, id_cliente, id_vendedor, fecha, estatus, total, direccion_envio, id_usuario, tipo_venta, plazo_credito, id_pedido)
        VALUES
        ({$idEmpresa}, {$id_cliente}, {$id_vendedor}, '{$fecha_emision}', 'pendiente', {$total}, {$direccion_sql}, {$idUsuario}, 'credito', {$plazo_credito}, {$id_pedido})
    ";
    $clsConsulta->guardarGeneral($sqlRemision);
    $id_remision = (int)$clsConsulta->ultimoid;

    if ($id_remision <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'No se pudo crear la remisión']);
        exit;
    }

    // Detalle remisión desde mov_pedidos
    $productos = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad, precio, precio_original FROM mov_pedidos WHERE id_pedido = {$id_pedido}");
    if ($clsConsulta->numrows > 0) {
        foreach ($productos as $k => $prod) {
            if ($k === 0) continue;

            $id_producto     = (int)$prod['id_producto'];
            $cantidad        = (float)$prod['cantidad'];

            // Respaldos de precio
            $precio          = isset($prod['precio']) ? (float)$prod['precio'] : 0.0;
            $precio_unitario = isset($prod['precio_original']) ? (float)$prod['precio_original'] : 0.0;

            if ($precio_unitario <= 0) $precio_unitario = $precio;
            if ($precio <= 0) $precio = $precio_unitario * $cantidad;

            if ($precio < 0) $precio = 0.0;
            if ($precio_unitario < 0) $precio_unitario = 0.0;

            $sqlDetalle = "
                INSERT INTO mov_remisiones
                (id_remision, id_producto, cantidad, precio, precio_unitario)
                VALUES
                ({$id_remision}, {$id_producto}, {$cantidad}, {$precio}, {$precio_unitario})
            ";
            $clsConsulta->aplicaquery($sqlDetalle);
        }
    }

    // Recalcular total de remisión por detalle (por si mov_pedidos trae totales raros)
    $clsConsulta->aplicaquery("
        UPDATE cab_remisiones cr
        JOIN (
            SELECT id_remision, COALESCE(SUM(cantidad * precio_unitario),0) AS total_calc
            FROM mov_remisiones
            WHERE id_remision = {$id_remision}
        ) t ON t.id_remision = cr.id
        SET cr.total = t.total_calc
        WHERE cr.id = {$id_remision}
        LIMIT 1
    ");

    echo json_encode([
        'success' => true,
        'mensaje' => 'Pedido procesado. Remisión generada.',
        'remision_id' => $id_remision
    ]);
    exit;
} elseif ($estatus === 'cancelado') {

    // Liberar reservas (tu lógica original; sin inventar columnas)
    $productosPedido = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad FROM mov_pedidos WHERE id_pedido = {$id_pedido}");
    if ($clsConsulta->numrows > 0) {
        foreach ($productosPedido as $k => $producto) {
            if ($k === 0) continue;

            $id_producto = (int)$producto['id_producto'];

            $sqlLiberar = "UPDATE inventarios_reservas
                           SET estatus = 'liberado'
                           WHERE id_producto = {$id_producto}
                             AND id_cliente = {$id_cliente}
                             AND estatus = 'reservado'
                           ORDER BY created_at DESC
                           LIMIT 1";
            $clsConsulta->aplicaquery($sqlLiberar);
        }
    }

    echo json_encode(['success' => true, 'mensaje' => 'Pedido cancelado. Reservas liberadas.']);
    exit;
}

// pendiente u otros
echo json_encode(['success' => true, 'mensaje' => 'Estatus actualizado correctamente']);
