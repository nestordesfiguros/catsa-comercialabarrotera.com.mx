<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsClaves.php';
$clsClaves=new Claves();

$mail=$_POST['mail'];
$pwd=$clsClaves->generar_clave();
echo $pwd;
?>