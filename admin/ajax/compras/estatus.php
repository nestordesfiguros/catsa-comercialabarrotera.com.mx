<?php
// ajax/compras/estatus.php

session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa  = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idUsuario  = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;

$idOrden    = isset($_POST['id']) ? (int)$_POST['id'] : 0;
// 1=Pendiente, 2=Procesada, 3=Cancelada
$nuevo      = isset($_POST['estatus']) ? (int)$_POST['estatus'] : 0;
$idAlmacen  = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

if ($idEmpresa <= 0 || $idUsuario <= 0 || $idOrden <= 0 || !in_array($nuevo, [1, 2, 3], true)) {
    echo 'error';
    exit;
}

function estatusToEnum($v)
{
    if ($v === 1) return 'pendiente';
    if ($v === 2) return 'procesada';
    return 'cancelada';
}
$estatusNuevo = estatusToEnum($nuevo);

// ================================
// Obtener compra actual
// ================================
$sqlCab = "SELECT id, id_proveedor, fecha, estatus, total 
           FROM cab_compras 
           WHERE id={$idOrden} AND id_empresa={$idEmpresa}
           LIMIT 1";
$cab = $clsConsulta->consultaGeneral($sqlCab);

if ($clsConsulta->numrows < 1) {
    echo 'no_encontrado';
    exit;
}

$estatusActual = $cab[1]['estatus']; // enum: pendiente/procesada/cancelada
$idProveedor   = (int)$cab[1]['id_proveedor'];
$fechaCompra   = $cab[1]['fecha'];
$totalCompra   = $cab[1]['total'];

// reglas:
// - si ya está cancelada no se modifica
if ($estatusActual === 'cancelada') {
    echo 'ya_cancelada';
    exit;
}
// - si está procesada NO puede regresar a pendiente
if ($estatusActual === 'procesada' && $estatusNuevo === 'pendiente') {
    echo 'no_permitido';
    exit;
}
// - si es el mismo estatus, no hacer nada
if ($estatusActual === $estatusNuevo) {
    echo 'sin_cambios';
    exit;
}

// ================================
// Helpers inventario
// ================================
function inv_suma($clsConsulta, $idEmpresa, $idAlmacen, $idProducto, $cantidad, $referencia, $idUsuario, $tipo)
{
    // inventarios: si existe suma, si no crea
    $chk = $clsConsulta->consultaGeneral("SELECT id, cantidad 
                                         FROM inventarios 
                                         WHERE id_empresa={$idEmpresa} AND id_almacen={$idAlmacen} AND id_producto={$idProducto}
                                         LIMIT 1");
    if ($clsConsulta->numrows > 0) {
        $idInv = (int)$chk[1]['id'];
        $clsConsulta->aplicaQuery("UPDATE inventarios SET cantidad = cantidad + {$cantidad} WHERE id={$idInv} LIMIT 1");
    } else {
        $clsConsulta->aplicaQuery("INSERT INTO inventarios (id_empresa, id_almacen, id_producto, cantidad)
                                  VALUES ({$idEmpresa}, {$idAlmacen}, {$idProducto}, {$cantidad})");
    }

    // bitácora
    $clsConsulta->aplicaQuery("INSERT INTO inventario_bitacora (id_empresa, id_producto, id_almacen, cantidad, tipo_movimiento, referencia, id_usuario, fecha)
                              VALUES ({$idEmpresa}, {$idProducto}, {$idAlmacen}, {$cantidad}, '{$tipo}', '{$referencia}', {$idUsuario}, NOW())");
}

function inv_resta($clsConsulta, $idEmpresa, $idAlmacen, $idProducto, $cantidad, $referencia, $idUsuario, $tipo)
{
    // resta controlada (no valida negativos por instrucción)
    $chk = $clsConsulta->consultaGeneral("SELECT id, cantidad 
                                         FROM inventarios 
                                         WHERE id_empresa={$idEmpresa} AND id_almacen={$idAlmacen} AND id_producto={$idProducto}
                                         LIMIT 1");
    if ($clsConsulta->numrows > 0) {
        $idInv = (int)$chk[1]['id'];
        $clsConsulta->aplicaQuery("UPDATE inventarios SET cantidad = cantidad - {$cantidad} WHERE id={$idInv} LIMIT 1");
    } else {
        // si no existe, lo crea en negativo (sin “mejoras” extra)
        $clsConsulta->aplicaQuery("INSERT INTO inventarios (id_empresa, id_almacen, id_producto, cantidad)
                                  VALUES ({$idEmpresa}, {$idAlmacen}, {$idProducto}, " . (0 - $cantidad) . ")");
    }

    $clsConsulta->aplicaQuery("INSERT INTO inventario_bitacora (id_empresa, id_producto, id_almacen, cantidad, tipo_movimiento, referencia, id_usuario, fecha)
                              VALUES ({$idEmpresa}, {$idProducto}, {$idAlmacen}, {$cantidad}, '{$tipo}', '{$referencia}', {$idUsuario}, NOW())");
}

// ================================
// Si pasa a PROCESADA => crear entrada a almacén + afectar inventario
// ================================
if ($estatusNuevo === 'procesada') {

    if ($idAlmacen <= 0) {
        echo 'sin_almacen';
        exit;
    }

    // validar almacén pertenece a empresa y está activo
    $chkAlm = $clsConsulta->consultaGeneral("SELECT id FROM cat_almacenes WHERE id={$idAlmacen} AND id_empresa={$idEmpresa} AND estatus=1 LIMIT 1");
    if ($clsConsulta->numrows < 1) {
        echo 'almacen_invalido';
        exit;
    }

    // evitar duplicar entrada si ya existe por id_odc
    $chkEnt = $clsConsulta->consultaGeneral("SELECT id, estatus 
                                            FROM cab_entradas_almacen 
                                            WHERE id_empresa={$idEmpresa} AND id_odc={$idOrden} AND tipo_referencia='ODC'
                                            LIMIT 1");
    $idEntrada = 0;

    if ($clsConsulta->numrows > 0) {
        $idEntrada = (int)$chkEnt[1]['id'];
    } else {
        $referencia = "ODC-{$idOrden}";

        $sqlInsCab = "
            INSERT INTO cab_entradas_almacen
                (id_empresa, id_almacen, id_proveedor, fecha, id_usuario, estatus, total, referencia, tipo_referencia, id_odc)
            VALUES
                ({$idEmpresa}, {$idAlmacen}, {$idProveedor}, '{$fechaCompra}', {$idUsuario}, 'procesada', '{$totalCompra}', '{$referencia}', 'ODC', {$idOrden})
        ";
        $clsConsulta->guardarGeneral($sqlInsCab);
        $idEntrada = (int)$clsConsulta->ultimoid;
    }

    if ($idEntrada <= 0) {
        echo 'error';
        exit;
    }

    // obtener detalle ODC
    $sqlMov = "SELECT id_producto, cantidad, precio
               FROM mov_compras
               WHERE id_orden_compra={$idOrden}";
    $movs = $clsConsulta->consultaGeneral($sqlMov);

    if ($clsConsulta->numrows < 1) {
        echo 'sin_detalle';
        exit;
    }

    // insertar detalle entrada + actualizar inventarios
    $refMov = "ENT-ODC-{$idOrden}";
    for ($i = 1; $i <= $clsConsulta->numrows; $i++) {
        $idProducto = (int)$movs[$i]['id_producto'];
        $cantidad   = (float)$movs[$i]['cantidad'];
        $precio     = (float)$movs[$i]['precio'];

        // evitar duplicar mov_entradas si ya existe para esta entrada+producto (simple)
        $chkMovEnt = $clsConsulta->consultaGeneral("SELECT id 
                                                    FROM mov_entradas_almacen 
                                                    WHERE id_orden_compra={$idEntrada} AND id_producto={$idProducto}
                                                    LIMIT 1");
        if ($clsConsulta->numrows < 1) {
            $clsConsulta->aplicaQuery("INSERT INTO mov_entradas_almacen (id_orden_compra, id_producto, cantidad, precio)
                                      VALUES ({$idEntrada}, {$idProducto}, '{$cantidad}', '{$precio}')");
        }

        inv_suma($clsConsulta, $idEmpresa, $idAlmacen, $idProducto, $cantidad, $refMov, $idUsuario, 'ENTRADA_ALMACEN');
    }

    // actualizar estatus ODC
    $clsConsulta->aplicaQuery("UPDATE cab_compras SET estatus='procesada' WHERE id={$idOrden} AND id_empresa={$idEmpresa} LIMIT 1");

    echo 'success';
    exit;
}

// ================================
// Si pasa a CANCELADA
// - si venía de PROCESADA => revertir inventario usando el almacén de la entrada ODC
// - si venía de PENDIENTE => solo cancelar (sin movimiento)
// ================================
if ($estatusNuevo === 'cancelada') {

    if ($estatusActual === 'procesada') {
        // buscar entrada asociada para saber almacén
        $ent = $clsConsulta->consultaGeneral("SELECT id, id_almacen 
                                             FROM cab_entradas_almacen
                                             WHERE id_empresa={$idEmpresa} AND id_odc={$idOrden} AND tipo_referencia='ODC'
                                             LIMIT 1");
        if ($clsConsulta->numrows > 0) {
            $idEntrada = (int)$ent[1]['id'];
            $idAlmEnt  = (int)$ent[1]['id_almacen'];

            // leer detalle de entrada (si existe) para revertir exactamente lo aplicado
            $movEnt = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad 
                                                     FROM mov_entradas_almacen
                                                     WHERE id_orden_compra={$idEntrada}");
            if ($clsConsulta->numrows > 0) {
                $refMov = "CANCEL-ODC-{$idOrden}";
                for ($i = 1; $i <= $clsConsulta->numrows; $i++) {
                    $idProducto = (int)$movEnt[$i]['id_producto'];
                    $cantidad   = (float)$movEnt[$i]['cantidad'];
                    inv_resta($clsConsulta, $idEmpresa, $idAlmEnt, $idProducto, $cantidad, $refMov, $idUsuario, 'CANCELACION_ODC');
                }
            }
        }
    }

    $clsConsulta->aplicaQuery("UPDATE cab_compras SET estatus='cancelada' WHERE id={$idOrden} AND id_empresa={$idEmpresa} LIMIT 1");
    echo 'success';
    exit;
}

// ================================
// Si queda PENDIENTE (solo permitido si venía de pendiente, pero ya lo filtramos arriba)
// ================================
$clsConsulta->aplicaQuery("UPDATE cab_compras SET estatus='pendiente' WHERE id={$idOrden} AND id_empresa={$idEmpresa} LIMIT 1");
echo 'success';
