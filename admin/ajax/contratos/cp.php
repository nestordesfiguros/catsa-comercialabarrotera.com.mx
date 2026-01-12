<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id=trim($_POST['municipio']);      
?>   
    <?php
    $con="SELECT * FROM codigos_postales WHERE municipio_id=".$id." ORDER BY cp ASC";
// echo $con;
    $rs=$clsConsulta->consultaGeneral($con);
    foreach($rs as $v=>$val){
        echo '<option value="'.$val['CP'].'">';
    }
?>