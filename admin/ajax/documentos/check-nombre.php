<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();

$id=$_POST['id'];
$nombre=$_POST['nombre'];
$con="SELECT * FROM definicion_documentos WHERE nombre='".$nombre."' AND id_cliente=".$id;
//echo $con;
$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){   
    $var='{"nombre":"false"}';            
}else{
    $var='{"nombre":"true"}';
}
echo $var;
?>