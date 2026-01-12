<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$data = [];
$idCliente = $_POST['idCliente'];

$con="SELECT
    cat_clientes.calle
    , cat_clientes.num_ext
    , cat_clientes.num_int
    , cat_clientes.colonia
    , cat_clientes.cp
    , cat_clientes.localidad
    , cat_clientes.tel1
    , cat_clientes.correo
    , cat_clientes.mapa
    , estados.nombre AS nestado
    , municipios.nombre AS nmunicipio
FROM
    cat_clientes
    INNER JOIN estados 
        ON (cat_clientes.id_estado = estados.id)
    INNER JOIN municipios 
        ON (cat_clientes.id_municipio = municipios.id)
WHERE cat_clientes.id=".$idCliente;
//print_r($con);
$rs=$clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0) {  
    foreach($rs as $v=>$val){
        $data = [
            "calle"=>$val['calle'],
            "num_ext"=>$val['num_ext'],
            "num_int"=>$val['num_int'],
            "cp"=>$val['cp'],
            "colonia"=>$val['colonia'],
            "nestado"=>$val['nestado'],
            "nmunicipio"=>$val['nmunicipio']
        ];
    }     
}

 header('Content-Type: application/json'); // Asegúrate de establecer el tipo de contenido
 echo json_encode($data);
?>