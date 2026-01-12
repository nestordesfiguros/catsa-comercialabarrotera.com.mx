<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$correo=$_POST['correo'];

$con="SELECT * FROM usuarios WHERE usr='".$correo."'";
// echo $con.'<br>';
$rs=$clsConsulta->consultaGeneral($con);

if($clsConsulta->numrows>0){    
    $res= '{"existe":"true"}';
}else{
    $res='{"existe":"false"}';
}

echo $res;
?>
