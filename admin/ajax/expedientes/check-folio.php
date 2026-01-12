<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$folio=$_POST['folio'];
$con="SELECT folio FROM contratos WHERE folio='".$folio."'";
// echo $con;
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){   
    $var='{"existe":"false"}';
}else{
    $var='{"existe":"true"}';
}
echo $var;
  
?>