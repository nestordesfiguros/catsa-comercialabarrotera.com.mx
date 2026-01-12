<?php
// ajax/remisiones/guardar.php
session_start();
header('Content-Type: text/html; charset=utf-8');

require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/* =========================
   Multiempresa (obligatorio)
========================= */
$id_empresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
if ($id_empresa <= 0) {
    echo 'error_empresa';
    exit;
}

/* =========================
   Usuario
========================= */
$id_usuario = 0;
if (isset($_SESSION['id_usuario'])) $id_usuario = (int)$_SESSION['id_usuario'];
elseif (isset($_SESSION['id_user'])) $id_usuario = (int)$_SESSION['id_user'];
if ($id_usuario <= 0) $id_usuario = 1;

/* =========================
   Inputs principales
========================= */
$id_cliente      = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
$id_vendedor     = isset($_POST['id_vendedor']) ? (int)$_POST['id_vendedor'] : 0;
$direccion_envio = isset($_POST['direccion_envio']) ? trim((string)$_POST['direccion_envio']) : '';
$total           = isset($_POST['total']) ? (float)$_POST['total'] : 0.0;

$tipo_venta = isset($_POST['tipo_venta']) ? strtolower(trim((string)$_POST['tipo_venta'])) : 'contado';
if ($tipo_venta !== 'contado' && $tipo_venta !== 'credito') $tipo_venta = 'contado';

$plazo_credito_post = isset($_POST['plazo_credito']) ? (int)$_POST['plazo_credito'] : 0;

/* ✅ NUEVOS */
$id_almacen = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;
$procesar   = isset($_POST['procesar']) ? (int)$_POST['procesar'] : 1; // default 1

/* =========================
   Validaciones mínimas
========================= */
if ($id_cliente <= 0) {
    echo 'error_cliente';
    exit;
}
if ($id_almacen <= 0) {
    echo 'error_almacen';
    exit;
}
if ($total <= 0) {
    echo 'error_total';
    exit;
}

/* =========================
   Validar cliente pertenece a empresa
========================= */
$cli = $clsConsulta->consultaGeneral("
    SELECT id, plazo_credito
    FROM cat_clientes
    WHERE id = $id_cliente AND id_empresa = $id_empresa AND estatus = 1
    LIMIT 1
");
if ($clsConsulta->numrows < 1) {
    echo 'error_cliente_empresa';
    exit;
}

/* =========================
   Validar almacén pertenece a empresa
========================= */
$alm = $clsConsulta->consultaGeneral("
    SELECT id
    FROM cat_almacenes
    WHERE id = $id_almacen AND id_empresa = $id_empresa AND estatus = 1
    LIMIT 1
");
if ($clsConsulta->numrows < 1) {
    echo 'error_almacen_empresa';
    exit;
}

/* =========================
   Fecha (del form si viene)
========================= */
$fecha_form = isset($_POST['fecha']) ? trim((string)$_POST['fecha']) : '';
$fecha_form = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_form) ? $fecha_form : '';
$fecha_sql = $fecha_form
    ? "CONCAT('$fecha_form',' ', DATE_FORMAT(NOW(), '%H:%i:%s'))"
    : "NOW()";

$direccion_envio_sql = $clsConsulta->escape($direccion_envio);

/* =========================
   Obtener productos (POST arrays o JSON)
========================= */
$productos = [];

if (isset($_POST['producto_id']) && is_array($_POST['producto_id'])) {
    $num = count($_POST['producto_id']);
    for ($i = 0; $i < $num; $i++) {
        $pid  = (int)($_POST['producto_id'][$i] ?? 0);
        $cant = (float)($_POST['cantidad'][$i] ?? 0);
        $pu   = (float)($_POST['precio_venta'][$i] ?? 0);

        if ($pid > 0 && $cant > 0 && $pu > 0) {
            $productos[] = [
                'id_producto'     => $pid,
                'cantidad'        => $cant,
                'precio_unitario' => $pu,
                'precio'          => isset($_POST['precio'][$i]) ? (float)$_POST['precio'][$i] : ($pu * $cant),
            ];
        }
    }
} elseif (!empty($_POST['productos'])) {
    $productos = json_decode($_POST['productos'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($productos)) {
        echo 'error_productos';
        exit;
    }
}

if (count($productos) < 1) {
    echo 'error_sin_productos';
    exit;
}

/* =========================
   Si procesar=1: validar existencias (multi-almacén)
   Inventario por almacén: tabla inventarios
========================= */
if ($procesar === 1) {
    foreach ($productos as $prod) {
        $pid  = (int)$prod['id_producto'];
        $cant = (float)$prod['cantidad'];

        $inv = $clsConsulta->consultaGeneral("
            SELECT cantidad
            FROM inventarios
            WHERE id_empresa = $id_empresa
              AND id_almacen = $id_almacen
              AND id_producto = $pid
            LIMIT 1
        ");
        $exist = 0;
        if ($clsConsulta->numrows > 0 && is_array($inv) && isset($inv[1]['cantidad'])) {
            $exist = (float)$inv[1]['cantidad'];
        }

        if ($cant > $exist) {
            // Puedes retornar más detalle si quieres (producto/cantidad), pero dejo estándar
            echo 'error_stock_insuficiente';
            exit;
        }
    }
}

/* =========================
   Estatus inicial de remisión
========================= */
$estatus_remision = ($procesar === 1) ? 'procesada' : 'pendiente';

/* =========================
   Insert cab_remisiones (multiempresa + almacén)
========================= */
$sqlRemision = "
    INSERT INTO cab_remisiones
        (id_empresa, id_cliente, id_vendedor, id_almacen, fecha, estatus, total, direccion_envio, id_usuario, tipo_venta, plazo_credito)
    VALUES
        ($id_empresa, $id_cliente, $id_vendedor, $id_almacen, $fecha_sql, '$estatus_remision', $total, $direccion_envio_sql, $id_usuario, '$tipo_venta',
         " . ($plazo_credito_post > 0 ? $plazo_credito_post : "NULL") . ")
";
$guardar = $clsConsulta->guardarGeneral($sqlRemision);
$id_remision = (int)$clsConsulta->ultimoid;

if (!$guardar || $id_remision <= 0) {
    echo 'error_guardar_remision';
    exit;
}

/* =========================
   Insert mov_remisiones
========================= */
foreach ($productos as $prod) {
    $id_producto     = (int)$prod['id_producto'];
    $cantidad        = (float)$prod['cantidad'];
    $precio_unitario = (float)$prod['precio_unitario'];
    $precio          = isset($prod['precio']) ? (float)$prod['precio'] : ($precio_unitario * $cantidad);

    $sqlDetalle = "
        INSERT INTO mov_remisiones (id_remision, id_producto, cantidad, precio, precio_unitario)
        VALUES ($id_remision, $id_producto, $cantidad, $precio, $precio_unitario)
    ";
    $clsConsulta->guardarGeneral($sqlDetalle);
}

/* =========================
   CxC si crédito
========================= */
if ($tipo_venta === 'credito') {
    // prioridad: plazo_credito POST > cat_clientes > 30
    $plazo_credito = 30;
    if ($plazo_credito_post > 0) {
        $plazo_credito = $plazo_credito_post;
    } else {
        if (isset($cli[1]['plazo_credito']) && (int)$cli[1]['plazo_credito'] > 0) {
            $plazo_credito = (int)$cli[1]['plazo_credito'];
        }
    }

    $fecha_emision = $fecha_form ? $fecha_form : date('Y-m-d');
    $fecha_vencimiento = date('Y-m-d', strtotime($fecha_emision . " +$plazo_credito days"));

    $sqlCxC = "
        INSERT INTO cab_cxc (id_empresa, id_cliente, id_remision, fecha_emision, fecha_vencimiento, monto, saldo, estatus, id_vendedor, id_usuario, fecha)
        VALUES ($id_empresa, $id_cliente, $id_remision, '$fecha_emision', '$fecha_vencimiento', $total, $total, 'Pendiente', $id_vendedor, $id_usuario, '$fecha_emision')
    ";
    $clsConsulta->guardarGeneral($sqlCxC);
}

/* =========================
   Si procesar=1: crear Salida automáticamente desde la Remisión
   Tablas:
   - cab_salidas_almacen
   - mov_salidas_almacen
   - rel_salidas_remisiones (relación)
   - inventarios (descontar)
   - inventario_bitacora (bitácora)
========================= */
if ($procesar === 1) {
    $fecha_salida = $fecha_form ? $fecha_form : date('Y-m-d');
    $referencia = "REMISION#$id_remision";

    // 1) cab_salidas_almacen
    $ref_sql = $clsConsulta->escape($referencia);
    $obs_sql = $clsConsulta->escape("Salida generada automáticamente desde remisión #$id_remision");

    $sqlCabSalida = "
        INSERT INTO cab_salidas_almacen
            (id_empresa, id_almacen, id_cliente, destino_libre, fecha, id_usuario, estatus, tipo_salida, referencia, observaciones)
        VALUES
            ($id_empresa, $id_almacen, $id_cliente, NULL, '$fecha_salida', $id_usuario, 'procesada', 'venta', $ref_sql, $obs_sql)
    ";
    $okSalida = $clsConsulta->guardarGeneral($sqlCabSalida);
    $id_salida = (int)$clsConsulta->ultimoid;

    if (!$okSalida || $id_salida <= 0) {
        echo 'error_crear_salida';
        exit;
    }

    // 2) relación salida-remisión (usa la tabla rel_salidas_remisiones)
    $clsConsulta->guardarGeneral("
        INSERT INTO rel_salidas_remisiones (id_salida, id_remision)
        VALUES ($id_salida, $id_remision)
    ");

    // 3) detalle + descontar inventario + bitácora
    foreach ($productos as $prod) {
        $pid  = (int)$prod['id_producto'];
        $cant = (float)$prod['cantidad'];

        // mov_salidas_almacen
        $clsConsulta->guardarGeneral("
            INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad)
            VALUES ($id_salida, $pid, $cant)
        ");

        // descontar inventarios
        $inv = $clsConsulta->consultaGeneral("
            SELECT id, cantidad
            FROM inventarios
            WHERE id_empresa = $id_empresa AND id_almacen = $id_almacen AND id_producto = $pid
            LIMIT 1
        ");

        if ($clsConsulta->numrows > 0 && is_array($inv) && isset($inv[1]['id'])) {
            $id_inv = (int)$inv[1]['id'];
            $exist  = (float)$inv[1]['cantidad'];
            $nuevo  = $exist - $cant;
            if ($nuevo < 0) $nuevo = 0;

            $clsConsulta->aplicaQuery("
                UPDATE inventarios
                SET cantidad = $nuevo
                WHERE id = $id_inv
            ");
        } else {
            // si por alguna razón no existe, lo dejamos en 0 (pero no debería pasar porque validamos arriba)
            $clsConsulta->guardarGeneral("
                INSERT INTO inventarios (id_empresa, id_almacen, id_producto, cantidad)
                VALUES ($id_empresa, $id_almacen, $pid, 0)
            ");
        }

        // inventario_bitacora
        $clsConsulta->guardarGeneral("
            INSERT INTO inventario_bitacora (id_empresa, id_producto, id_almacen, cantidad, tipo_movimiento, referencia, id_usuario, fecha)
            VALUES ($id_empresa, $pid, $id_almacen, $cant, 'SALIDA', $ref_sql, $id_usuario, NOW())
        ");
    }
}

echo 'success';
