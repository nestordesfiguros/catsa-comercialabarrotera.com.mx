<?php
// ajax/pedidos/estatus.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? intval($_SESSION['id_empresa']) : 0;
if ($idEmpresa <= 0) {
    echo json_encode(['success' => false, 'mensaje' => 'Empresa inválida']);
    exit;
}

$id_pedido = isset($_POST['id']) ? intval($_POST['id']) : 0;
$estatus   = isset($_POST['estatus']) ? $_POST['estatus'] : '';

if ($id_pedido <= 0 || $estatus == '') {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

/* Verificar que el pedido pertenezca a la empresa actual */
$sqlCheck = "SELECT id FROM cab_pedidos WHERE id = $id_pedido AND id_empresa = $idEmpresa LIMIT 1";
$clsConsulta->consultaGeneral($sqlCheck);
if ($clsConsulta->numrows < 1) {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

// 1. Actualizar estatus del pedido (solo dentro de la empresa actual)
$sqlEstatus = "UPDATE cab_pedidos SET estatus = '$estatus' WHERE id = $id_pedido AND id_empresa = $idEmpresa";
$ok = $clsConsulta->aplicaquery($sqlEstatus);

if (!$ok) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar estatus']);
    exit;
}

// 2. Si es procesado, generar remisión y cuenta por cobrar
if ($estatus === 'procesado') {
    $con = "SELECT * FROM cab_pedidos WHERE id = $id_pedido AND id_empresa = $idEmpresa LIMIT 1";
    $pedidoDatos = $clsConsulta->consultaGeneral($con);

    if ($clsConsulta->numrows < 1) {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró el pedido']);
        exit;
    }

    // Respetando tu índice 1-based y nombres originales
    $pedido          = $pedidoDatos[1];
    $id_cliente      = intval($pedido['id_cliente']);
    $id_vendedor     = intval($pedido['id_vendedor']);
    $fecha           = $pedido['fecha'];
    $total           = floatval($pedido['total']);
    $direccion_envio = $pedido['direccion_envio'];
    $id_usuario      = intval($pedido['id_usuario']);

    // 2.1 Obtener plazo de crédito del cliente (mismo nombre de campo que usas)
    $clienteDatos = $clsConsulta->consultaGeneral("
        SELECT plazo_credito 
        FROM cat_clientes 
        WHERE id = $id_cliente AND id_empresa = $idEmpresa
        LIMIT 1
    ");
    $plazo_credito = ($clsConsulta->numrows > 0 && intval($clienteDatos[1]['plazo_credito']) > 0)
        ? intval($clienteDatos[1]['plazo_credito']) : 30;

    // 2.2 Calcular fechas
    $fecha_emision     = date('Y-m-d', strtotime($fecha));
    $fecha_vencimiento = date('Y-m-d', strtotime("+$plazo_credito days", strtotime($fecha_emision)));

    // 2.3 Insertar cabecera remisión (propagando id_empresa)
    $sqlRemision = "
        INSERT INTO cab_remisiones 
        (id_empresa, id_cliente, id_vendedor, fecha, estatus, total, direccion_envio, id_usuario, tipo_venta, plazo_credito, id_pedido)
        VALUES
        ($idEmpresa, $id_cliente, $id_vendedor, '$fecha_emision', 'pendiente', $total, '$direccion_envio', $id_usuario, 'credito', $plazo_credito, $id_pedido)
    ";
    $clsConsulta->guardarGeneral($sqlRemision);
    $id_remision = $clsConsulta->ultimoid;

    // 2.4 Insertar detalle remisión
    $productos = $clsConsulta->consultaGeneral("SELECT * FROM mov_pedidos WHERE id_pedido = $id_pedido");
    if ($clsConsulta->numrows > 0) {
        foreach ($productos as $prod) {
            // Mantener nombres originales
            $id_producto     = intval($prod['id_producto']);
            $cantidad        = floatval($prod['cantidad']);
            $precio          = isset($prod['precio']) ? floatval($prod['precio']) : 0.00;
            $precio_unitario = isset($prod['precio_original']) ? floatval($prod['precio_original']) : 0.00;

            // Si precio o precio_unitario vienen 0/NULL, buscar respaldo en cat_productos
            if ($precio <= 0 || $precio_unitario <= 0) {
                $precioCat = $clsConsulta->consultaGeneral("
                    SELECT 
                        COALESCE(NULLIF(precio_venta,0), 0)          AS pv,
                        COALESCE(NULLIF(precio_minimo_venta,0), 0)   AS pmin,
                        COALESCE(NULLIF(precio_sugerido,0), 0)       AS psug
                    FROM cat_productos
                    WHERE id_producto = $id_producto
                    LIMIT 1
                ");

                if ($clsConsulta->numrows > 0) {
                    $pv   = floatval($precioCat[1]['pv']);
                    $pmin = floatval($precioCat[1]['pmin']);
                    $psug = floatval($precioCat[1]['psug']);

                    // Orden de respaldo: precio_venta -> precio_minimo_venta -> precio_sugerido
                    $fallback = ($pv > 0) ? $pv : (($pmin > 0) ? $pmin : $psug);

                    if ($precio <= 0)          $precio = $fallback;
                    if ($precio_unitario <= 0) $precio_unitario = $fallback;
                }
            }

            // Asegurar no negativos
            if ($precio < 0)          $precio = 0.00;
            if ($precio_unitario < 0) $precio_unitario = 0.00;

            $sqlDetalle = "
                INSERT INTO mov_remisiones
                (id_remision, id_producto, cantidad, precio, precio_unitario)
                VALUES
                ($id_remision, $id_producto, $cantidad, $precio, $precio_unitario)
            ";
            $clsConsulta->aplicaquery($sqlDetalle);
        }
    }

    // 2.5 Recalcular total de remisión a partir de partidas (como en tu original)
    $recalculaTotal = "
        UPDATE cab_remisiones cr
        JOIN (
            SELECT id_remision, SUM(cantidad * precio_unitario) AS total_calc
            FROM mov_remisiones
            WHERE id_remision = $id_remision
        ) t ON t.id_remision = cr.id
        SET cr.total = t.total_calc
        WHERE cr.id = $id_remision
          AND cr.id_empresa = $idEmpresa
    ";
    $clsConsulta->aplicaquery($recalculaTotal);

    // 2.6 Insertar cuenta por cobrar (propagando id_empresa)
    $sqlCxc = "
        INSERT INTO cab_cxc
        (id_empresa, id_cliente, id_remision, id_factura, fecha_emision, fecha_vencimiento, monto, saldo, estatus)
        VALUES
        ($idEmpresa, $id_cliente, $id_remision, NULL, '$fecha_emision', '$fecha_vencimiento', $total, $total, 'Pendiente')
    ";
    $clsConsulta->aplicaquery($sqlCxc);
}

echo json_encode(['success' => true, 'mensaje' => 'Estatus actualizado correctamente']);
