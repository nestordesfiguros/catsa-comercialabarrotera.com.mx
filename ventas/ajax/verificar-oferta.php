<?php
require_once '../../admin/lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

$idProducto = intval($_POST['id_producto'] ?? 0);
$response = ['oferta' => null];

if ($idProducto > 0) {
    $sql = "SELECT 
                valor_oferta, 
                cantidad_minima, 
                producto_bonus 
            FROM mov_ofertas_productos 
            WHERE id_producto = $idProducto 
              AND valor_oferta IS NOT NULL 
              AND valor_oferta > 0
            ORDER BY id DESC 
            LIMIT 1";

    $rs = $clsConsulta->consultaGeneral($sql);
    if ($clsConsulta->numrows > 0) {
        $response['oferta'] = $rs[1];
    }
}

echo json_encode($response);
