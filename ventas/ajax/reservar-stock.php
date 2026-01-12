<?php
// ajax/reservar-stock.php
header('Content-Type: application/json; charset=utf-8');
require '../../admin/lib/clsConsultas.php';
session_start();
$clsConsulta = new Consultas();

$idProducto = (int)($_POST['id_producto'] ?? 0);
$cantidad   = (int)($_POST['cantidad'] ?? 0);
$idUsuario  = (int)($_SESSION['id_user'] ?? 0);
$idCliente  = (int)($_SESSION['pedidoActivo'] ?? 0);

if ($idProducto <= 0 || $cantidad <= 0 || $idUsuario <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Parámetros inválidos']);
    exit;
}

try {
    $clsConsulta->aplicaquery("START TRANSACTION");

    // Inventario físico por almacén (bloqueado)
    $sqlInv = "
      SELECT id_almacen, cantidad
      FROM inventarios
      WHERE id_producto = $idProducto
      FOR UPDATE
    ";
    $inv = $clsConsulta->consultaGeneral($sqlInv);

    // Reservas por almacén (bloqueado)
    $mapReservado = [];
    if (is_array($inv) && $clsConsulta->numrows > 0) {
        foreach ($inv as $row) {
            $alm = (int)$row['id_almacen'];
            $sqlResAlm = "
              SELECT COALESCE(SUM(cantidad),0) AS r
              FROM inventarios_reservas
              WHERE id_producto = $idProducto
                AND id_almacen = $alm
                AND estatus = 'reservado'
              FOR UPDATE
            ";
            $r = $clsConsulta->consultaGeneral($sqlResAlm);
            $mapReservado[$alm] = (int)($r[1]['r'] ?? 0);
        }
    }

    // Asignación greedy
    $porReservar = $cantidad;
    $reservas    = []; // devolveremos ids y cantidades por almacén

    if (is_array($inv) && $clsConsulta->numrows > 0) {
        // ordenar por cantidad disponible desc
        usort($inv, function ($a, $b) use ($mapReservado) {
            $da = max(0, (int)$a['cantidad'] - (int)($mapReservado[(int)$a['id_almacen']] ?? 0));
            $db = max(0, (int)$b['cantidad'] - (int)($mapReservado[(int)$b['id_almacen']] ?? 0));
            return $db <=> $da;
        });

        foreach ($inv as $row) {
            $alm = (int)$row['id_almacen'];
            $fisico   = (int)$row['cantidad'];
            $resAlm   = (int)($mapReservado[$alm] ?? 0);
            $dispAlm  = max(0, $fisico - $resAlm);
            if ($dispAlm <= 0) continue;

            $toma = min($dispAlm, $porReservar);
            if ($toma <= 0) continue;

            $sqlIns = "
              INSERT INTO inventarios_reservas (id_producto, id_almacen, cantidad, id_usuario, id_cliente, estatus)
              VALUES ($idProducto, $alm, $toma, $idUsuario, " . ($idCliente ?: "NULL") . ", 'reservado')
            ";
            $clsConsulta->aplicaquery($sqlIns);
            // Obtener id insertado (compatible con tu clase)
            $resId = $clsConsulta->consultaGeneral("SELECT LAST_INSERT_ID() AS id");
            $reservas[] = [
                'id_reserva' => (int)$resId[1]['id'],
                'id_almacen' => $alm,
                'cantidad'   => $toma
            ];

            $porReservar -= $toma;
            if ($porReservar <= 0) break;
        }
    }

    if ($porReservar > 0) {
        // no alcanzó
        $clsConsulta->aplicaquery("ROLLBACK");
        echo json_encode(['ok' => false, 'msg' => 'Stock insuficiente para reservar']);
        exit;
    }

    $clsConsulta->aplicaquery("COMMIT");
    echo json_encode(['ok' => true, 'reservas' => $reservas]);
} catch (Throwable $e) {
    $clsConsulta->aplicaquery("ROLLBACK");
    echo json_encode(['ok' => false, 'msg' => 'Error de reserva']);
}
