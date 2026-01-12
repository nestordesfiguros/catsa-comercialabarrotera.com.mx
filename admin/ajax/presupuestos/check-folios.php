<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();


$folio=$_POST['folio'];
$con="SELECT * FROM presupuestos WHERE folio='".$folio."'";
// echo $con;
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){   
    $var='{"folio":"false"}';            
}else{
    $var='{"folio":"true"}';
}
echo $var;
  
?>