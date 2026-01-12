<?php
include '../../lib/clsConsultas.php';
$cls = new Consultas();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$response = ['success' => false];

if ($id > 0) {
    $oferta = $cls->consultaGeneral("SELECT * FROM cat_ofertas WHERE id_oferta = $id");
    if (isset($oferta[1])) {
        $response['oferta'] = $oferta[1];

        $productos = $cls->consultaGeneral("SELECT * FROM mov_ofertas_productos WHERE id_oferta = $id");
        $response['productos'] = [];

        foreach ($productos as $p) {
            $response['productos'][] = [
                'id_producto' => $p['id_producto'],
                'valor_oferta' => $p['valor_oferta'],
                'cantidad_minima' => $p['cantidad_minima'],
                'producto_bonus' => $p['producto_bonus']
            ];
        }

        $response['success'] = true;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>