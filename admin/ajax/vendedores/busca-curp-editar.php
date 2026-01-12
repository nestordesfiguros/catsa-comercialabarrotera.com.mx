<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$curp=trim($_POST['curp']);
$curpActual=trim($_POST['curpActual']);

$curp=strtolower($curp);
$curpActual=strtolower($curpActual);

if($curp!=$curpActual){
    $con="SELECT * FROM cat_personal WHERE curp='".$curp."'";
    // echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){   
        $var='{"existe":"false"}';            
    }else{
        $var='{"existe":"true"}';
    }
}else{
    $var='{"existe":"true"}';
}

echo $var;
  


?>