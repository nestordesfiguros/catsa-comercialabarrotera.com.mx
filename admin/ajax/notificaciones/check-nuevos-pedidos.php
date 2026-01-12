<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$sql = "SELECT
    cab_pedidos.id
    , cab_pedidos.num_pedido
    , concat (cat_vendedores.nombre, ' '
    , cat_vendedores.apellido1, ' '
    , cat_vendedores.apellido2 ) AS vendedor
 FROM
    cab_pedidos
    INNER JOIN cat_vendedores 
        ON (cab_pedidos.id_vendedor = cat_vendedores.id)
WHERE notificado = 0 AND id_vendedor >0 ORDER BY id DESC LIMIT 1";
$resultado = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    $vendedor = $resultado[1]['vendedor'];
    $id = $resultado[1]['id'];
    $num_pedido = $resultado[1]['num_pedido'];
    echo json_encode([
        'nuevo' => true,
        'id' => $id,
        'noPedido' => $num_pedido,
        'vendedor' => $vendedor
    ]);
} else {
    echo json_encode(['nuevo' => false]);
}

?>