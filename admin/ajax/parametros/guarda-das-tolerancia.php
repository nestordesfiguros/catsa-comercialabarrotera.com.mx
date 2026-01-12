<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$dias_inicial = $_POST['dias_inicial'];
$dias_final = $_POST['dias_final'];


$con="UPDATE parametros_dias_tolerancia SET dias_inicial='".$dias_inicial."', dias_final='".$dias_final."' WHERE id=1";
//echo $con;
$clsConsulta->aplicaQuery($con);


?>