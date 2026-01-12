<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$cp=trim($_POST['cp']);
$lista='';
if($cp!=''){
    $con="SELECT * FROM colonias WHERE SUBSTRING(slug, 1, 5) ='".$cp."'";
    // echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    foreach($rs as $v =>$val){
        $lista.='<option value="'.$val['nombre'].'">';
    }
    
}

echo $lista;

?>