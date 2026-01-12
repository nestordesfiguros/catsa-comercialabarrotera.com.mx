<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$cp=trim($_POST['cp']);      
$con="SELECT * FROM codigos_postales WHERE CP='".$cp."'";
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $id=$val['id'];
}

$con="SELECT * FROM colonias WHERE codigo_postal_id=".$id." ORDER BY nombre ASC";
//echo $con;
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
        echo '<option value="'.$val['nombre'].'">';
}
?>