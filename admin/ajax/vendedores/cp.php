<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$municipio_id=$_POST['municipio_id'];

$lista='';
if($municipio_id!=''){
    $con="SELECT * FROM codigos_postales WHERE municipio_id =".$municipio_id;     
    $rs=$clsConsulta->consultaGeneral($con);
    foreach($rs as $v =>$val){
        $lista.= '<option value="'.$val['CP'].'">';
    }
    
}

echo $lista;

?>