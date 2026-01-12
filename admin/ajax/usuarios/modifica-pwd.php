<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsClaves.php';
$clsClaves=new Claves();

//var_dump($_POST);

 $id = $_POST['id'];
 $usr = $_POST['usr'];
 $clave = $_POST['pwd'];
 $pwd = $clsClaves->codificaPwd($clave);
 
 $con = "UPDATE usuarios SET usr='$usr', pwd ='$pwd' WHERE id=".$id;
 //echo $con,'<br>';
$clsConsulta->aplicaQuery($con); 
?>