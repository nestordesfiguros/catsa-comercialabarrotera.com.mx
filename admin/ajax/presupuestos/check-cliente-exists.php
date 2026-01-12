<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
//var_dump($_POST);

$razon=trim($_POST['cliente']);
$con="SELECT * FROM cat_clientes WHERE razon_social='".$razon."'";
// echo $con;
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){   
    $var='{"folio":"true"}';            
}else{
    $var='{"folio":"false","razon":"'.$razon.'"}';
}
echo $var;
  
?>