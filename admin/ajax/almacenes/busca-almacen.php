<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();

$almacen=trim($_POST['almacen']);
$almacen=strtoupper($almacen);
if($almacen!=''){
    $con="SELECT * FROM cat_almacenes WHERE almacen='".$almacen."'";
    // echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){   
        $var='{"almacen":"false"}';            
    }else{
        $var='{"almacen":"true"}';
    }
}else{
    $var='{"almacen":"false"}';
}

echo $var;
  


?>