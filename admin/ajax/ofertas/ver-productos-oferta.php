<?php
// ajax/ofertas/ver-productos-oferta.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$id_oferta = isset($_POST['id_oferta']) ? intval($_POST['id_oferta']) : 0;
$response = [];

if ($id_oferta > 0) {
    $con = "
        SELECT 
            p.nombre AS producto,
            mop.valor_oferta,
            mop.cantidad_minima,
            mop.producto_bonus
        FROM mov_ofertas_productos mop
        INNER JOIN cat_productos p ON p.id_producto = mop.id_producto
        WHERE mop.id_oferta = $id_oferta
    ";

    $result = $clsConsulta->consultaGeneral($con);

    foreach ($result as $row) {
        $response[] = [
            'producto' => $row['producto'],
            'valor_oferta' => $row['valor_oferta'],
            'cantidad_minima' => $row['cantidad_minima'],
            'tipo' => $row['producto_bonus'] == 1 ? 'Bonus' : 'Base'
        ];
    }
}

echo json_encode($response);
?>