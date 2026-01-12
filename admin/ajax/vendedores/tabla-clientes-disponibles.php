<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idVendedor = $_GET['idVendedor'];

// Excluir clientes ya asignados a cualquier vendedor
$sql = "SELECT cc.*
FROM cat_clientes cc
WHERE NOT EXISTS (
    SELECT 1
    FROM vendedores_clientes vc
    WHERE vc.id_cliente = cc.id 
)
AND cc.estatus =1
AND cc.deleted_at IS NULL;";

$rs = $clsConsulta->consultaGeneral($sql);

$data = [];

foreach ($rs as $v => $val) {
    $no_cliente = $val['no_cliente'];
    $razon = $val['razon_social'];
    $comercial = $val['nombre_comercial'];
    $idCliente = $val['id'];

    $data[] = [
        "<div>$no_cliente</div>",
        "<div>$razon</div>",
        "<div>$comercial</div>",
        '<div class="text-center">
            <i class="fas fa-user-plus fa-lg text-info" style="cursor:pointer;" onclick="fnAddClientes('.$idCliente.')"></i>
         </div>'
    ];
}

echo json_encode(["data" => $data]);

?>