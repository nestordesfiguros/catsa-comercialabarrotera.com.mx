<?php
// verifica-pedidos-remisiones.php

header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

require_once __DIR__ . '/../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Función para verificar los movimientos entre pedidos y remisiones
function verificarMovimientosPedidosRemisiones()
{
    global $clsConsulta;

    // Obtener los pedidos pendientes
    $sqlPedidos = "
        SELECT p.id AS pedido_id, p.num_pedido, r.id AS remision_id, r.id_pedido AS remision_pedido_id
        FROM cab_pedidos p
        LEFT JOIN cab_remisiones r ON r.id_pedido = p.id
        WHERE p.estatus = 'procesado'
    ";
    $pedidos = $clsConsulta->consultaGeneral($sqlPedidos);

    $resultado = [];

    // Verificar que cada remisión esté asociada a un pedido
    if ($clsConsulta->numrows > 0) {
        foreach ($pedidos as $pedido) {
            $pedido_id = $pedido['pedido_id'];
            $num_pedido = $pedido['num_pedido'];
            $remision_id = $pedido['remision_id'];
            $remision_pedido_id = $pedido['remision_pedido_id'];

            // Comprobamos si hay remisión asociada a este pedido
            if ($remision_pedido_id != $pedido_id) {
                $resultado[] = [
                    'pedido_id' => $pedido_id,
                    'num_pedido' => $num_pedido,
                    'status' => 'error',
                    'message' => 'La remisión no está asociada al pedido'
                ];
            } else {
                $resultado[] = [
                    'pedido_id' => $pedido_id,
                    'num_pedido' => $num_pedido,
                    'status' => 'success',
                    'message' => 'La remisión está correctamente asociada al pedido'
                ];
            }
        }
    } else {
        $resultado[] = ['status' => 'error', 'message' => 'No se encontraron pedidos procesados'];
    }

    return $resultado;
}

// Ejecutar la verificación
$response = verificarMovimientosPedidosRemisiones();
echo json_encode(['result' => $response], JSON_UNESCAPED_UNICODE);
exit;
