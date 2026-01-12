<?php
// ajax/remisiones/cambiar-estatus.php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$response = ['success' => false, 'message' => ''];

try {
    // Validar datos de entrada
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
    $id_almacen_post = isset($_POST['id_almacen']) ? intval($_POST['id_almacen']) : 0;

    if ($id <= 0) {
        throw new Exception('ID de remisión inválido');
    }

    if (!in_array($estatus, ['procesada', 'cancelada'])) {
        throw new Exception('Estatus no válido');
    }

    // Verificar que la remisión esté pendiente
    $sql = "SELECT estatus FROM cab_remisiones WHERE id = $id";
    $result = $clsConsulta->consultaGeneral($sql);

    if ($clsConsulta->numrows === 0) {
        throw new Exception('Remisión no encontrada');
    }

    if ($result[1]['estatus'] !== 'pendiente') {
        throw new Exception('Solo se pueden modificar remisiones pendientes');
    }

    // Si estatus es procesada, guardar almacén primero
    if ($estatus === 'procesada') {
        if ($id_almacen_post <= 0) {
            throw new Exception('Debes seleccionar un almacén para procesar.');
        }

        $sqlSetAlmacen = "UPDATE cab_remisiones SET id_almacen = $id_almacen_post WHERE id = $id";
        $clsConsulta->aplicaQuery($sqlSetAlmacen);
    }

    // Actualizar el estatus
    $sqlUpdate = "UPDATE cab_remisiones SET estatus = '$estatus' WHERE id = $id";
    $clsConsulta->aplicaQuery($sqlUpdate);

    // Si el estatus es 'procesada', generar CxC y salida de almacén
    if ($estatus === 'procesada') {
        // Obtener datos necesarios para la CxC
        $sqlDatos = "SELECT 
            r.id AS id_remision,
            r.id_cliente,
            r.id_vendedor AS idVendedor,
            r.tipo_venta,
            c.plazo_credito,
            COALESCE(SUM(COALESCE(m.cantidad,0) * COALESCE(m.precio,0)), 0) AS monto_total
        FROM cab_remisiones r
            INNER JOIN mov_remisiones m ON r.id = m.id_remision
            LEFT JOIN cat_clientes c ON r.id_cliente = c.id
            WHERE r.id = $id
            GROUP BY r.id";

        $datos = $clsConsulta->consultaGeneral($sqlDatos);

        if ($clsConsulta->numrows > 0) {
            $d = $datos[1];
            $id_remision = intval($d['id_remision']);
            $id_cliente = intval($d['id_cliente']);
            $id_vendedor = intval($d['idVendedor']);
            $plazo_credito = intval($d['plazo_credito']);
            $monto = $d['monto_total'];
            $fecha = date('Y-m-d');
            $fecha_vencimiento = date('Y-m-d', strtotime("+$plazo_credito days"));

            if (strtolower($d['tipo_venta']) === 'credito') {
                $id_remision = intval($d['id_remision']);
                $id_cliente = intval($d['id_cliente']);
                $id_vendedor = intval($d['idVendedor']);
                $plazo_credito = intval($d['plazo_credito']);
                $monto = $d['monto_total'];
                $fecha = date('Y-m-d');
                $fecha_vencimiento = date('Y-m-d', strtotime("+$plazo_credito days"));

                $sqlCxC = "INSERT INTO cab_cxc 
                    (id_remision, id_cliente, id_vendedor, fecha_emision, fecha, fecha_vencimiento, monto, saldo, estatus)
                    VALUES 
                    ($id_remision, $id_cliente, $id_vendedor, '$fecha', '$fecha', '$fecha_vencimiento', $monto, $monto, 'Pendiente')";

                $clsConsulta->aplicaQuery($sqlCxC);
            }
        }

        // Obtener productos de la remisión
        $sqlProductos = "
            SELECT id_producto, cantidad, precio 
            FROM mov_remisiones 
            WHERE id_remision = $id
        ";
        $productos = $clsConsulta->consultaGeneral($sqlProductos);

        if ($clsConsulta->numrows > 0) {
            // Obtener ID del almacén
            $sqlAlmacen = "SELECT id_almacen FROM cab_remisiones WHERE id = $id";
            $resAlmacen = $clsConsulta->consultaGeneral($sqlAlmacen);
            $id_almacen = intval($resAlmacen[1]['id_almacen'] ?? 0);

            if ($id_almacen > 0) {
                $id_usuario = $_SESSION['id_user'] ?? 0;
                $totalSalida = 0;

                foreach ($productos as $i => $p) {
                    //  if ($i === 0) continue;
                    $cant = intval($p['cantidad'] ?? 0);
                    $prec = floatval($p['precio'] ?? 0);
                    $totalSalida += $cant * $prec;
                }

                $sqlCabSalida = "
                    INSERT INTO cab_salidas_almacen 
                    (id_almacen, id_usuario, fecha, estatus, total) 
                    VALUES 
                    ($id_almacen, $id_usuario, '$fecha', 'procesada', $totalSalida)
                ";

                $clsConsulta->guardarGeneral($sqlCabSalida);
                $id_salida = $clsConsulta->ultimoid;

                foreach ($productos as $p) {
                    $id_producto = intval($p['id_producto']);
                    $cantidad = intval($p['cantidad']);
                    $precio = floatval($p['precio']);

                    if ($id_salida <= 0 || $id_producto <= 0 || $cantidad <= 0 || $precio < 0) {
                        throw new Exception("Datos inválidos para registrar salida de almacén.");
                    }

                    $sqlMov = "
                        INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad, precio)
                        VALUES ($id_salida, $id_producto, $cantidad, $precio)
                    ";

                    $clsConsulta->aplicaQuery($sqlMov);

                    $sqlUpdInv = "
                        UPDATE inventarios 
                        SET cantidad = cantidad - $cantidad 
                        WHERE id_almacen = $id_almacen AND id_producto = $id_producto
                    ";
                    $clsConsulta->aplicaQuery($sqlUpdInv);
                }
            } else {
                throw new Exception('No se pudo determinar el almacén de origen para afectar inventario');
            }
        }
    }

    $response['success'] = true;
    $response['message'] = 'Estatus actualizado correctamente';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}


header('Content-Type: application/json');
echo json_encode($response);
