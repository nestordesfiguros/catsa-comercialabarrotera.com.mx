<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$correo=trim($_POST['correo']);
$correoanterior=trim($_POST['correoanterior']);
if($correoanterior==$correo && $correoanterior!=''){
    $res='{"existe":"false"}';
}else{
    $con="SELECT * FROM cat_vendedores WHERE usr='".$correo."'";
    // echo $con.'<br>';
    $rs=$clsConsulta->consultaGeneral($con);

    if($clsConsulta->numrows>0){    
        $res= '{"existe":"true"}';
    }else{
        $res='{"existe":"false"}';
    }
}

echo $res;
?>
