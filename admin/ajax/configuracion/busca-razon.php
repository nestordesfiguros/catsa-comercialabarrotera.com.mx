<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();

$razon=trim($_POST['razon']);
$razon=strtoupper($razon);
if($razon!=''){
    $con="SELECT * FROM cat_empresas WHERE razon_social='".$razon."'";
    // echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){   
        $var='{"razon":"false"}';            
    }else{
        $var='{"razon":"true"}';
    }
}else{
    $var='{"razon":"false"}';
}

echo $var;
  


?>