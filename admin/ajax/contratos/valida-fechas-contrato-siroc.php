<?php
$fecha_firma_siroc=trim($_POST['fecha_firma_siroc']);      
$fecha_inicio=trim($_POST['fecha_inicio']); 

//echo $fecha_inicio.' --- '.$fecha_firma_siroc;

if($fecha_inicio==$fecha_firma_siroc){
    $var='{"valor":"true"}'; 
}else{
    $var='{"valor":"false"}'; 
}
echo $var;
?>