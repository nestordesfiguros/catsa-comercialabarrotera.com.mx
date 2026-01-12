<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();


date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d h:m:s');

$id = $_POST['id'];
$razon_social = $_POST['razon_social'];
$rfc = $_POST['rfc'];
$correo = $_POST['correo'];
$calle = $_POST['calle'];
$num_ext = $_POST['num_ext'];
$num_int = (isset($_POST['num_int']))?$_POST['num_int']:'';
$colonia = $_POST['colonia'];
$cp = $_POST['cp'];
$id_municipio = $_POST['id_municipio'];
$id_estado = $_POST['id_estado'];
$localidad = (isset($_POST['localidad']))?$_POST['localidad']:'';

$con = "UPDATE cat_empresas 
        SET razon_social = '".$razon_social."', 
            rfc = '".$rfc."', 
            correo = '".$correo."', 
            calle = '".$calle."', 
            num_ext = '".$num_ext."', 
            num_int = '".$num_int."', 
            colonia = '".$colonia."', 
            cp = ".$cp.", 
            id_municipio = ".$id_municipio.", 
            id_estado = ".$id_estado.", 
            localidad = '".$localidad."', 
            fecha_alta = '".$fechaActual."' 
        WHERE id = ".$id; 

$clsConsulta->aplicaQuery($con);


?>