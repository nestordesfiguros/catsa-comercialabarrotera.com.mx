<?php
include '../admin/lib/clsConsultas.php';
$clsConsulta=new Consultas();
//var_dump($_POST);
$correo=$_POST['email'];

$con="SELECT usr FROM usuarios WHERE usr='".$correo."'";
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    $valor='{"correo":"true"}';
}else{
    $valor='{"correo":"false"}';
}
echo $valor;
?>