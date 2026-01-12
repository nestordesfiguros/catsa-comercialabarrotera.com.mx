<?php
    
    $monto = str_replace("$","",$_POST['monto']);
    $monto = str_replace(",","",$monto);
    $impomontorte = trim($monto);
    if($impomontorte>=1){
        $var='{"monto":"true"}';            
    }else{
        $var='{"monto":"false"}';
    }
    //echo $impomontorte;
    echo $var;

?>