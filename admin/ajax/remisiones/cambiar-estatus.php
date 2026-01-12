<?php
// ajax/remisiones/cambiar-estatus.php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$response = ['success' => false, 'message' => ''];

try {
    $idRemision   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nuevoEstatus = isset($_POST['estatus']) ? trim((string)$_POST['estatus']) : '';
    $comentario   = isset($_POST['comentario']) ? trim((string)$_POST['comentario']) : '';
    $idAlmacen    = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;

    if ($idRemision <= 0) throw new Exception('ID de remisión inválido.');
    if (!in_array($nuevoEstatus, ['procesada', 'cancelada'], true)) throw new Exception('Estatus no válido.');

    $idUsuario = 0;
    if (isset($_SESSION['id_usuario'])) $idUsuario = (int)$_SESSION['id_usuario'];
    elseif (isset($_SESSION['id_user'])) $idUsuario = (int)$_SESSION['id_user'];
    if ($idUsuario <= 0) $idUsuario = 1;

    // Remisión actual
    $rem = $clsConsulta->consultaGeneral("SELECT id, estatus, id_cliente, id_vendedor, tipo_venta FROM cab_remisiones WHERE id = $idRemision LIMIT 1");
    if ($clsConsulta->numrows === 0) throw new Exception('Remisión no encontrada.');

    $remRow = $rem[1];
    if (strtolower((string)$remRow['estatus']) !== 'pendiente') {
        throw new Exception('Solo se pueden modificar remisiones pendientes.');
    }

    $idCliente  = (int)$remRow['id_cliente'];
    $idVendedor = (int)($remRow['id_vendedor'] ?? 0);
    $tipoVenta  = strtolower((string)($remRow['tipo_venta'] ?? 'contado'));

    if ($nuevoEstatus === 'cancelada') {
        $clsConsulta->aplicaquery("UPDATE cab_remisiones SET estatus='cancelada' WHERE id = $idRemision LIMIT 1");
        $response['success'] = true;
        $response['message'] = 'Remisión cancelada.';
        echo json_encode($response);
        exit;
    }

    // procesada requiere almacén
    if ($idAlmacen <= 0) throw new Exception('Debes seleccionar un almacén para procesar.');

    // validar almacén activo
    $chkA = $clsConsulta->consultaGeneral("SELECT id FROM cat_almacenes WHERE id = $idAlmacen AND estatus = 1 LIMIT 1");
    if ($clsConsulta->numrows <= 0) throw new Exception('Almacén inválido o inactivo.');

    // Partidas
    $partidas = $clsConsulta->consultaGeneral("
        SELECT mr.id_producto, IFNULL(mr.cantidad,0) AS cantidad, IFNULL(mr.precio_unitario,0) AS precio_unitario
        FROM mov_remisiones mr
        WHERE mr.id_remision = $idRemision
    ");
    if ($clsConsulta->numrows === 0) throw new Exception('La remisión no tiene partidas.');

    // Transacción
    $clsConsulta->aplicaquery("START TRANSACTION");

    // Validar existencias (total vs disponible)
    $faltantes = [];
    foreach ($partidas as $k => $p) {
        if ($k === 0) continue;

        $idProducto = (int)$p['id_producto'];
        $qty = (float)$p['cantidad'];

        // existencia_total en inventarios
        $ex = $clsConsulta->consultaGeneral("
            SELECT IFNULL(SUM(cantidad),0) AS inv_total
            FROM inventarios
            WHERE id_almacen = $idAlmacen AND id_producto = $idProducto
        ");
        $invTotal = (float)($ex[1]['inv_total'] ?? 0);

        // reservado_otros (reservado de otros clientes) -> para no pisar reservas ajenas
        $resO = $clsConsulta->consultaGeneral("
            SELECT IFNULL(SUM(cantidad),0) AS reservado_otros
            FROM inventarios_reservas
            WHERE id_almacen = $idAlmacen
              AND id_producto = $idProducto
              AND estatus = 'reservado'
              AND (vence_at IS NULL OR vence_at > NOW())
              AND id_cliente <> $idCliente
        ");
        $reservadoOtros = (float)($resO[1]['reservado_otros'] ?? 0);

        $disponibleParaEstaRemision = $invTotal - $reservadoOtros;

        if ($disponibleParaEstaRemision + 0.0001 < $qty) {
            $np = $clsConsulta->consultaGeneral("SELECT nombre FROM cat_productos WHERE id_producto = $idProducto LIMIT 1");
            $nombreProd = ($clsConsulta->numrows > 0) ? ($np[1]['nombre'] ?? ('Producto ' . $idProducto)) : ('Producto ' . $idProducto);

            $faltantes[] = $nombreProd . " (faltan: " . max(0, $qty - $disponibleParaEstaRemision) . ")";
        }
    }

    if (!empty($faltantes)) {
        $clsConsulta->aplicaquery("ROLLBACK");
        throw new Exception('Inventario insuficiente en almacén seleccionado: ' . implode(', ', $faltantes));
    }

    // Crear salida de almacén única
    $fechaHoy = date('Y-m-d');
    $totalSalida = 0.0;
    foreach ($partidas as $k => $p) {
        if ($k === 0) continue;
        $totalSalida += ((float)$p['cantidad']) * ((float)$p['precio_unitario']);
    }

    $clsConsulta->guardarGeneral("
        INSERT INTO cab_salidas_almacen (id_almacen, id_usuario, fecha, estatus, total)
        VALUES ($idAlmacen, $idUsuario, '$fechaHoy', 'procesada', $totalSalida)
    ");
    $idSalida = (int)$clsConsulta->ultimoid;
    if ($idSalida <= 0) {
        $clsConsulta->aplicaquery("ROLLBACK");
        throw new Exception('No se pudo crear la salida de almacén.');
    }

    // Relación salida-remisión
    $clsConsulta->aplicaquery("
        INSERT IGNORE INTO rel_salidas_remisiones (id_salida, id_remision)
        VALUES ($idSalida, $idRemision)
    ");

    // Insertar detalle y descontar inventario
    foreach ($partidas as $k => $p) {
        if ($k === 0) continue;

        $idProducto = (int)$p['id_producto'];
        $qty        = (float)$p['cantidad'];
        $precioU    = (float)$p['precio_unitario'];

        $clsConsulta->aplicaquery("
            INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad, precio_unitario, id_remision)
            VALUES ($idSalida, $idProducto, $qty, $precioU, $idRemision)
        ");

        $clsConsulta->aplicaquery("
            UPDATE inventarios
            SET cantidad = IFNULL(cantidad,0) - $qty
            WHERE id_almacen = $idAlmacen AND id_producto = $idProducto
        ");

        // (Opcional / recomendado mínimo): consumir reservas DEL MISMO CLIENTE (no de otros)
        // Esto evita que “existencia_disponible” quede castigada de más después de surtir.
        // Nota: consume filas completas; si tus reservas siempre se guardan por pedido, cuadra bien.
        $rowsRes = $clsConsulta->consultaGeneral("
            SELECT id, cantidad
            FROM inventarios_reservas
            WHERE id_almacen = $idAlmacen
              AND id_producto = $idProducto
              AND id_cliente = $idCliente
              AND estatus = 'reservado'
              AND (vence_at IS NULL OR vence_at > NOW())
            ORDER BY created_at ASC
        ");

        $porConsumir = $qty;
        if ($clsConsulta->numrows > 0) {
            foreach ($rowsRes as $rK => $r) {
                if ($rK === 0) continue;
                if ($porConsumir <= 0) break;

                $rid = (int)$r['id'];
                $rcant = (float)$r['cantidad'];

                $clsConsulta->aplicaquery("UPDATE inventarios_reservas SET estatus='consumido' WHERE id = $rid LIMIT 1");
                $porConsumir -= $rcant;
            }
        }
    }

    // CxC si crédito
    if ($tipoVenta === 'credito' || $tipoVenta === 'crédito') {
        $cli = $clsConsulta->consultaGeneral("SELECT plazo_credito FROM cat_clientes WHERE id = $idCliente LIMIT 1");
        $plazo = 30;
        if ($clsConsulta->numrows > 0 && isset($cli[1]['plazo_credito']) && (int)$cli[1]['plazo_credito'] > 0) {
            $plazo = (int)$cli[1]['plazo_credito'];
        }

        $montoRS = $clsConsulta->consultaGeneral("
            SELECT COALESCE(SUM(cantidad * precio_unitario),0) AS monto
            FROM mov_remisiones
            WHERE id_remision = $idRemision
        ");
        $monto = (float)($montoRS[1]['monto'] ?? 0);

        $fechaEmision = date('Y-m-d');
        $fechaVenc = date('Y-m-d', strtotime("+$plazo days", strtotime($fechaEmision)));

        $clsConsulta->aplicaquery("
            INSERT INTO cab_cxc (id_remision, id_cliente, id_vendedor, fecha_emision, fecha, fecha_vencimiento, monto, saldo, estatus)
            VALUES ($idRemision, $idCliente, $idVendedor, '$fechaEmision', '$fechaEmision', '$fechaVenc', $monto, $monto, 'Pendiente')
        ");
    }

    // Estatus remisión
    $clsConsulta->aplicaquery("UPDATE cab_remisiones SET estatus='procesada' WHERE id = $idRemision LIMIT 1");

    $clsConsulta->aplicaquery("COMMIT");

    $response['success'] = true;
    $response['message'] = 'Remisión procesada. Salida generada: ' . $idSalida;
    $response['salida'] = $idSalida;
} catch (Exception $e) {
    @$clsConsulta->aplicaquery("ROLLBACK");
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
