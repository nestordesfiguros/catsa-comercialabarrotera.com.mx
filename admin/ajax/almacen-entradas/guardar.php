<?php
// ajax/almacen-entradas/guardar.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    if (!isset($_SESSION['id_user'])) {
        echo 'error: Sesión expirada. Vuelve a iniciar sesión.';
        exit;
    }

    $idEmpresa  = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
    if ($idEmpresa <= 0) {
        echo 'error: Empresa inválida.';
        exit;
    }

    $id_usuario   = (int) $_SESSION['id_user'];
    $id_almacen   = isset($_POST['almacen_id'])   ? (int) $_POST['almacen_id']   : 0;
    $id_proveedor = isset($_POST['proveedor_id']) ? (int) $_POST['proveedor_id'] : 0;

    $producto_ids = isset($_POST['productos'])  ? $_POST['productos']  : [];
    $cantidades   = isset($_POST['cantidades']) ? $_POST['cantidades'] : [];

    $fecha         = isset($_POST['fecha']) ? $clsConsulta->escape($_POST['fecha']) : '';
    $referencia    = isset($_POST['referencia']) ? $clsConsulta->escape($_POST['referencia']) : '';
    $observaciones = isset($_POST['observaciones']) ? $clsConsulta->escape($_POST['observaciones']) : '';
    $incomp_obs    = isset($_POST['incompleto_observaciones']) ? $clsConsulta->escape($_POST['incompleto_observaciones']) : '';

    $pedido_incompleto = isset($_POST['pedido_incompleto']) ? 1 : 0;

    $odc_id = (isset($_POST['odc_id']) && is_numeric($_POST['odc_id'])) ? (int)$_POST['odc_id'] : null;

    if ($fecha === '' || $id_almacen <= 0 || $id_proveedor <= 0) {
        echo 'error: Faltan campos obligatorios (fecha, almacén, proveedor).';
        exit;
    }

    if (empty($producto_ids) || empty($cantidades)) {
        echo 'error: Debes agregar al menos un producto con su cantidad.';
        exit;
    }

    if ($odc_id === null) {
        if ($referencia === '') {
            echo 'error: Documento de referencia es obligatorio cuando no se selecciona ODC.';
            exit;
        }
        if ($observaciones === '') {
            echo 'error: Observaciones son obligatorias cuando no se selecciona ODC.';
            exit;
        }
    } else {
        // Validar ODC pertenece a empresa y está procesada
        $chkOdc = $clsConsulta->consultaGeneral("SELECT id FROM cab_compras WHERE id={$odc_id} AND id_empresa={$idEmpresa} AND estatus='procesada' LIMIT 1");
        if ($clsConsulta->numrows < 1) {
            echo 'error: ODC inválida o no pertenece a la empresa.';
            exit;
        }

        // Verificar ODC no usada
        $rsODC = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS total FROM cab_entradas_almacen WHERE id_empresa={$idEmpresa} AND id_odc={$odc_id}");
        if ($clsConsulta->numrows > 0 && (int)$rsODC[1]['total'] > 0) {
            echo 'error: La ODC seleccionada ya tiene una entrada registrada.';
            exit;
        }
    }

    // Validar almacén de empresa
    $rsAlm = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS total FROM cat_almacenes WHERE id={$id_almacen} AND id_empresa={$idEmpresa} AND estatus=1");
    if ($clsConsulta->numrows > 0 && (int)$rsAlm[1]['total'] === 0) {
        echo 'error: El almacén no existe, no pertenece a la empresa o está inactivo.';
        exit;
    }

    // Validar proveedor de empresa
    $rsProv = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS total FROM cat_proveedores WHERE id={$id_proveedor} AND id_empresa={$idEmpresa} AND estatus=1");
    if ($clsConsulta->numrows > 0 && (int)$rsProv[1]['total'] === 0) {
        echo 'error: El proveedor no existe, no pertenece a la empresa o está inactivo.';
        exit;
    }

    // Normaliza productos
    $productos_limpios = [];
    foreach ($producto_ids as $pid) {
        $pid_int = (int)$pid;
        if ($pid_int > 0) {
            if (isset($cantidades[$pid_int]) && is_numeric($cantidades[$pid_int])) {
                $cantidad = (float)$cantidades[$pid_int];
                if ($cantidad > 0) {
                    $productos_limpios[$pid_int] = $cantidad;
                }
            }
        }
    }

    if (empty($productos_limpios)) {
        echo 'error: Todas las cantidades deben ser numéricas y mayores a cero.';
        exit;
    }

    // Validar productos pertenecen a empresa (tolerante)
    $ids = implode(',', array_keys($productos_limpios));
    $whereBase = "p.estatus=1 AND p.id_producto IN ({$ids}) AND (
        p.id_empresa={$idEmpresa}
        OR EXISTS (SELECT 1 FROM inventarios i WHERE i.id_empresa={$idEmpresa} AND i.id_producto=p.id_producto)
        OR EXISTS (SELECT 1 FROM cat_almacenes a WHERE a.id=p.id_almacen AND a.id_empresa={$idEmpresa})
    )";
    $chkProds = $clsConsulta->consultaGeneral("SELECT COUNT(*) AS total FROM cat_productos p WHERE {$whereBase}");
    if ($clsConsulta->numrows > 0) {
        $totalValidos = (int)$chkProds[1]['total'];
        if ($totalValidos !== count($productos_limpios)) {
            echo 'error: Uno o más productos no pertenecen a la empresa.';
            exit;
        }
    }

    // TRANSACCIÓN
    $clsConsulta->aplicaQuery("START TRANSACTION");

    $fecha_sql          = "'" . $fecha . "'";
    $referencia_sql     = ($referencia !== '') ? "'" . $referencia . "'" : "NULL";
    $observaciones_sql  = ($observaciones !== '') ? "'" . $observaciones . "'" : "NULL";
    $incomp_obs_sql     = ($incomp_obs !== '') ? "'" . $incomp_obs . "'" : "NULL";
    $odc_sql            = ($odc_id !== null) ? $odc_id : "NULL";

    // Insert cabecera (con id_empresa)
    $sqlCab = "INSERT INTO cab_entradas_almacen
        (id_empresa, id_almacen, id_proveedor, fecha, id_usuario, estatus, referencia, observaciones, pedido_incompleto, incompleto_observaciones, id_odc)
        VALUES
        ({$idEmpresa}, {$id_almacen}, {$id_proveedor}, {$fecha_sql}, {$id_usuario}, 'procesada', {$referencia_sql}, {$observaciones_sql}, {$pedido_incompleto}, {$incomp_obs_sql}, {$odc_sql})";

    $okCab = $clsConsulta->guardarGeneral($sqlCab);
    $id_entrada_almacen = (int)$clsConsulta->ultimoid;

    if (!$okCab || $id_entrada_almacen <= 0) {
        $clsConsulta->aplicaQuery("ROLLBACK");
        echo 'error: No fue posible guardar la cabecera de la entrada.';
        exit;
    }

    foreach ($productos_limpios as $id_producto => $cantidad) {
        $id_producto = (int)$id_producto;
        $cantidad = (float)$cantidad;

        // precio_compra del catálogo (solo lectura)
        $precioCompra = 0.0;
        $rsPrecio = $clsConsulta->consultaGeneral("SELECT precio_compra FROM cat_productos WHERE id_producto={$id_producto} LIMIT 1");
        if ($clsConsulta->numrows > 0) {
            $precioCompra = (float)($rsPrecio[1]['precio_compra'] ?? 0);
        }

        // movimiento
        $sqlMov = "INSERT INTO mov_entradas_almacen
            (id_orden_compra, id_producto, cantidad, precio)
            VALUES
            ({$id_entrada_almacen}, {$id_producto}, {$cantidad}, {$precioCompra})";
        $clsConsulta->aplicaQuery($sqlMov);

        // inventarios por empresa+almacén (upsert manual)
        $rsInv = $clsConsulta->consultaGeneral("
            SELECT id, cantidad
            FROM inventarios
            WHERE id_empresa={$idEmpresa} AND id_producto={$id_producto} AND id_almacen={$id_almacen}
            LIMIT 1
        ");

        if ($clsConsulta->numrows > 0) {
            $idInventario = (int)$rsInv[1]['id'];
            $nuevaCant = (float)$rsInv[1]['cantidad'] + $cantidad;
            $clsConsulta->aplicaQuery("UPDATE inventarios SET cantidad={$nuevaCant} WHERE id={$idInventario} AND id_empresa={$idEmpresa}");
        } else {
            $clsConsulta->aplicaQuery("INSERT INTO inventarios (id_empresa, id_almacen, id_producto, cantidad) VALUES ({$idEmpresa}, {$id_almacen}, {$id_producto}, {$cantidad})");
        }

        // compatibilidad UI antigua
        $rsProd = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad_disponible FROM cat_productos WHERE id_producto={$id_producto} LIMIT 1");
        if ($clsConsulta->numrows > 0) {
            $nuevaDisp = (float)($rsProd[1]['cantidad_disponible'] ?? 0) + $cantidad;
            $clsConsulta->aplicaQuery("UPDATE cat_productos SET cantidad_disponible={$nuevaDisp}, precio_compra={$precioCompra} WHERE id_producto={$id_producto}");
        }
    }

    $clsConsulta->aplicaQuery("COMMIT");
    echo 'success';
    exit;
} catch (Throwable $e) {
    try {
        $clsConsulta->aplicaQuery("ROLLBACK");
    } catch (Throwable $ignored) {
    }
    echo 'error: Excepción no controlada. ' . $e->getMessage();
    exit;
}
