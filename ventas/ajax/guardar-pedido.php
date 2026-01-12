<?php
session_start();
require_once '../../admin/lib/clsConsultas.php';
$clsConsulta = new Consultas();
// Llamamos a la función y mostramos el resultado
$folio = $clsConsulta->obtenerFechaYHoraNumerica();
$fechaHora = date('Y-m-d H:i:s');

header('Content-Type: application/json');
try {
    // Procesar los datos recibidos
    $data = json_decode(file_get_contents('php://input'), true);

    // Validaciones y lógica de negocio...
    if (isset($_POST['items']) && isset($_POST['id_cliente']) && isset($_POST['idVendedor'])) {
        // Acceder al id_cliente
        $id_cliente = $_POST['id_cliente'];
        $id_vendedor = $_POST['idVendedor'];

        $con = "INSERT INTO cab_pedidos (num_pedido, id_cliente, id_vendedor, fecha, estatus) VALUES ( '" . $folio . "', " . $id_cliente . ", " . $id_vendedor . ", '" . $fechaHora . "', 'pendiente') ";
        //echo $con;
        $clsConsulta->guardarGeneral($con);
        $id_pedido = $clsConsulta->ultimoid;

        // Acceder a los productos en 'items'

        $items = $_POST['items'];

        // Recorrer los productos
        foreach ($items as $index => $item) {
            $sql = "INSERT INTO mov_pedidos (id_pedido, id_producto, clave, precio, cantidad) 
    VALUES ('" . $id_pedido . "', 
            '" . $item['id'] . "', 
            '" . $item['code'] . "', 
            '" . $item['price'] . "', 
            '" . $item['quantity'] . "')";
            $clsConsulta->aplicaQuery($sql);

            // === DESCONTAR INVENTARIO SOLO EN ALMACÉN 1 ===
            $id_producto = $item['id'];
            $cantidad = $item['quantity'];
            $sqlInv = "SELECT cantidad FROM inventarios WHERE id_producto = $id_producto AND id_almacen = 1";
            $rsInv = $clsConsulta->consultaGeneral($sqlInv);
            $cantidad_actual = isset($rsInv[1]['cantidad']) ? (int)$rsInv[1]['cantidad'] : 0;

            if ($cantidad_actual >= $cantidad) {
                $sqlDesc = "UPDATE inventarios SET cantidad = cantidad - $cantidad WHERE id_producto = $id_producto AND id_almacen = 1";
                $clsConsulta->aplicaQuery($sqlDesc);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "No hay suficiente inventario para el producto $id_producto"
                ]);
                exit;
            }
            // === FIN DESCUENTO INVENTARIO ===
        }
    } else {
        echo "No se recibieron los datos correctamente.";
    }
    unset($_SESSION['pedidoActivo']);

    echo json_encode([
        'success' => true,
        'message' => 'Pedido guardado correctamente'
    ]);
} catch (Exception $e) {

    echo json_encode([

        'success' => false,

        'message' => $e->getMessage()

    ]);
}
