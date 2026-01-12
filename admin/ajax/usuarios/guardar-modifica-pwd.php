<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
include '../../lib/clsClaves.php';
$clsClaves = new Claves();

//var_dump($_POST);

$nombre = $_POST['nombre'];
$usr = $_POST['usr'];
//$id_puesto = $_POST['id_puesto'];
$id_personal = $_POST['id_personal'] ?? 'NULL';  // usar la cadena NULL si está vacío
$clave = $_POST['pwd'];
$pwd = $clsClaves->codificaPwd($clave);

$con = "INSERT INTO usuarios (usr, pwd, nombre, id_personal,  estatus, fecha_alta) 
          VALUES ('$usr', '$pwd', '$nombre', $id_personal,  1, NOW())";

//echo $con;
$clsConsulta->guardarGeneral($con);
