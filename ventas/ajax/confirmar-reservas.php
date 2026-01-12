<?php
// ajax/confirmar-reservas.php
header('Content-Type: application/json; charset=utf-8');
require '../../admin/lib/clsConsultas.php';
session_start();
$clsConsulta = new Consultas();

$idUsuario = (int)($_SESSION['id_user'] ?? 0);
$idCliente = (int)($_SESSION['pedidoActivo'] ?? 0);
if ($idUsuario <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Sin sesión']);
    exit;
}

try {
    $clsConsulta->aplicaquery("START TRANSACTION");

    // Traer reservas activas del usuario (opcionalmente filtra por cliente)
    $sqlRes = "
      SELECT id, id_producto, id_almacen, cantidad
      FROM inventarios_reservas
      WHERE estatus='reservado' AND id_usuario=$idUsuario
      " . ($idCliente ? " AND (id_cliente=$idCliente OR id_cliente IS NULL)" : "") . "
      FOR UPDATE
    ";
    $res = $clsConsulta->consultaGeneral($sqlRes);
    if (!is_array($res) || $clsConsulta->numrows <= 0) {
        $clsConsulta->aplicaquery("ROLLBACK");
        echo json_encode(['ok' => false, 'msg' => 'No hay reservas']);
        exit;
    }

    // Descuenta inventario por cada reserva y marca consumido
    foreach ($res as $row) {
        $idRes = (int)$row['id'];
        $idAlm = (int)$row['id_almacen'];
        $idPro = (int)$row['id_producto'];
        $cant  = (int)$row['cantidad'];

        // Descuento físico
        $upd = "
          UPDATE inventarios 
          SET cantidad = GREATEST(cantidad - $cant, 0)
          WHERE id_producto = $idPro AND id_almacen = $idAlm
        ";
        $clsConsulta->aplicaquery($upd);

        // Consumir reserva
        $clsConsulta->aplicaquery("UPDATE inventarios_reservas SET estatus='consumido' WHERE id=$idRes");
    }

    $clsConsulta->aplicaquery("COMMIT");
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    $clsConsulta->aplicaquery("ROLLBACK");
    echo json_encode(['ok' => false, 'msg' => 'Error al confirmar']);
}
