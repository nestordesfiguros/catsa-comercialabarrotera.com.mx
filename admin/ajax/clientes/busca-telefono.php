<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$tel1=trim($_POST['tel1']);

if($tel1!=''){
    $con="SELECT * FROM cat_clientes WHERE tel1='".$tel1."'";
    // echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){   
        $nombre=$rs[1]['nombre'].' '.$rs[1]['apellido1'].' '.$rs[1]['apellido2'];
        $var='{"existe":"false","nombre":"'.$nombre.'"}';            
    }else{
        $var='{"existe":"true","nombre":""}';
    }
}

echo $var;

?>