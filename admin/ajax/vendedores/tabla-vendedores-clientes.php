<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idVendedor = $_GET['idVendedor'];
$data_array = [];

$con = "SELECT
    vendedores_clientes.*
    , cat_clientes.razon_social
    , cat_clientes.no_cliente
    , cat_clientes.nombre_comercial
    , cat_clientes.calle
    , cat_clientes.tel1
    , cat_clientes.mapa
FROM
    vendedores_clientes
    INNER JOIN cat_clientes 
        ON (vendedores_clientes.id_cliente = cat_clientes.id) WHERE id_vendedor= $idVendedor";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $id = $val['id'];
        $razonSocial = $val['razon_social'];
        $nombreComercial = "'".$val['nombre_comercial'];
        /*
        $calle = $val['calle'];
        $num_ext = $val['num_ext'];
        $num_int = $val['num_int'];
        $colonia = $val['colonia'];
        $cp = $val['cp'];
        $id_municipio = $val['id_municipio'];
        $id_estado = $val['id_estado'];
        $localidad = $val['localidad'];        
        $tel = $val['tel'];
        $correo = $val['correo'];
        */
               
        $razon_social = '<div>' . $razonSocial . ' / '.$nombreComercial.'</div>';
        //$nombre_comercial = '<div>' . $nombreComercial . '</div>';
        $borrar = '<div class="text-center"><i class="fas fa-trash fa-lg text-danger" style="cursor:pointer;" onclick="fnborrarClientes(' . $id . ');" ></i></div>';
        

        $data_array[] = array(
            $razon_social,            
            $borrar
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
