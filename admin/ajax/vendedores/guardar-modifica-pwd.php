<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsClaves.php';
$clsClaves=new Claves();

//var_dump($_POST);
$usr=$_POST['usr'];
$clave=$_POST['pwd'];
$id=$_POST['id'];   
 
$pwd=$clsClaves->codificaPwd($usr,$clave);
$con="UPDATE cat_vendedores SET usr='".$usr."', pwd='".$pwd."' WHERE id=".$id;
// echo $con;

$clsConsulta->guardarGeneral($con); 

?>