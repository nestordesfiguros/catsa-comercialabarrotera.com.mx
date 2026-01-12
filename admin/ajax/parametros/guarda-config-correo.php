<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$email_smtp = $_POST['email_smtp'];
$email_cuenta = $_POST['email_cuenta'];
$email_password = $_POST['email_password'];
$email_ssl = (isset($_POST['email_ssl']) && $_POST['email_ssl']=='on')?1:0;
$email_puerto = $_POST['email_puerto'];

$con="UPDATE parametros SET email_smtp='".$email_smtp."', email_cuenta='".$email_cuenta."', email_password='".$email_password."', email_ssl=".$email_ssl.", email_puerto=".$email_puerto." WHERE id=1";
//echo $con;
$clsConsulta->aplicaQuery($con);


?>