<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$razon_social=$_POST['fact_cliente'];
$con="SELECT * FROM cat_clientes WHERE razon_social='".$razon_social."'"; 
//echo $con.' <-- Cliente'; 
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $fact_cliente = $val['id'];
    }
}

$fact_serie = strtoupper($_POST['fact_serie']);
$fact_no = strtoupper($_POST['fact_no']);
$fact_fecha = $_POST['fact_fecha'];
if(isset($_POST['fact_folio'])){
    $fact_folio = strtoupper($_POST['fact_folio']);
    $fact_folio=trim($fact_folio);
}else{
    $fact_folio='';
}


$fact_monto = str_replace("$","",$_POST['fact_monto']);
$fact_monto = str_replace(",","",$fact_monto);
$fact_monto=trim($fact_monto);
if($fact_monto==''){
    $fact_monto=0;
}
/*
$fact_amortizacion_anticipo = str_replace("$","",$_POST['fact_amortizacion_anticipo']);
$fact_amortizacion_anticipo = str_replace(",","",$fact_amortizacion_anticipo);
$fact_amortizacion_anticipo=trim($fact_amortizacion_anticipo);
if($fact_amortizacion_anticipo==''){
    $fact_amortizacion_anticipo=0;
}

$fact_retencion = str_replace("$","",$_POST['fact_retencion']);
$fact_retencion = str_replace(",","",$fact_retencion);
$fact_retencion=trim($fact_retencion);
if($fact_retencion==''){
    $fact_retencion=0;
}
*/
//$fact_fecha_alta = $_POST['fact_fecha_alta'];
$fact_id_usuario = $_POST['fact_id_usuario'];
$fact_tentativa_cobro = $_POST['fact_tentativa_cobro']; 
$fact_semana_envio= $_POST['semana_actual']; 
$fact_descripcion = $_POST['fact_descripcion']; 
$archivo = $_POST['archivos'];

// Toma el valor de la última estimación
/*
$con="SELECT COALESCE(MAX(fact_no_estimacion), 0) AS maximo_valor FROM facturas WHERE fact_folio='".$fact_folio."'";
$rs=$clsConsulta->consultaGeneral($con);
if($rs[1]['maximo_valor']==0){
    $fact_no_estimacion=1;
}else{
    $fact_no_estimacion=$rs[1]['maximo_valor']+1;
}
*/
$con="INSERT INTO post_venta (fact_serie, fact_no, fact_cliente, fact_fecha, fact_folio, fact_monto, fact_tentativa_cobro, fact_id_usuario, fact_fecha_alta, fact_descripcion, fact_semana_envio) VALUES ('".$fact_serie."', '".$fact_no."', ".$fact_cliente.", '".$fact_fecha."', '".$fact_folio."', ".$fact_monto.", ".$fact_tentativa_cobro.", ".$fact_id_usuario.", NOW(), '".$fact_descripcion."', ".$fact_semana_envio." )";


$rs=$clsConsulta->guardarGeneral($con);
$id_factura=$clsConsulta->ultimoid; 

$archivo = explode("|",$_POST['archivos']);
foreach($archivo as $file){
    if($file!=''){
        $conf="INSERT INTO post_venta_archivos(fact_arch_id_factura, fact_arch_nombre, fact_arch_fecha_alta) VALUES (".$id_factura.", '".$file."', NOW() )";
    //    echo $conf.'<br>';
        $clsConsulta->aplicaQuery($conf);   
    }            
}


  setcookie("fact_cliente", $fact_cliente, [
    "expires" => time() + 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  setcookie("fact_serie", $fact_serie, [
    "expires" => time() + 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  setcookie("fact_no", $fact_no, [
    "expires" => time() + 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  
  setcookie("id_factura", $id_factura, [
    "expires" => time() + 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  
  setcookie("fact_cliente", $fact_cliente, [
    "expires" => time() + 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);
  setcookie("fact_folio", $fact_folio, [
    "expires" => time() + 3600,
    "path" => "/",
    "samesite" => "Strict"
  ]);

echo $id_factura;

?>