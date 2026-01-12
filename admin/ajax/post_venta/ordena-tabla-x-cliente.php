<?php 
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$orden='';
$cookie_name = "ordenxRazonSocial";
//$cookie_value='TODOS';
$razon_social=$_POST['razon_social'];
$con="SELECT razon_social FROM cat_clientes WHERE razon_social='".$_POST['razon_social']."'";                
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    $cookie_value=$rs[1]['razon_social'];
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
    $orden='{"orden":"'.$cookie_value.'"}';
}
if($_POST['razon_social']=='TODOS'){
    $cookie_value='TODOS';
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
    $orden='{"orden":"TODOS"}';
}

  echo $orden;
?>