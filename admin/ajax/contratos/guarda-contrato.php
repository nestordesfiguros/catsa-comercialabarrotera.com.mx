<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);

$folio = strtoupper($_POST['folio']);
$archivo = $_POST['archivo'];
$id_tipo_contrato = $_POST['id_tipo'];
//$id_folio_base = $_POST['folio'];
$cliente_id = $_POST['id_cliente'];
$registro_patronal = strtoupper($_POST['registro_patronal']);
$fecha_firma = $_POST['fecha_firma'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_termino = $_POST['fecha_termino'];
$fecha_firma_siroc = $_POST['fecha_firma_siroc'];
$fecha_cierre_siroc = $_POST['fecha_cierre_siroc'];

$monto_anticipo = str_replace("$","",$_POST['monto_anticipo']);
$monto_anticipo = str_replace(",","",$monto_anticipo);
$monto_anticipo=trim($monto_anticipo);
if($monto_anticipo==''){
    $monto_anticipo=0;
}

$importe_contratado = str_replace("$","",$_POST['importe_contratado']);
$importe_contratado = str_replace(",","",$importe_contratado);
$importe_contratado = trim($importe_contratado);
    
$suministros = str_replace("$","",$_POST['suministros']);
$suministros = str_replace(",","",$suministros);
$suministros = trim($suministros);
if($suministros=='$000.00'){
    $suministros=0;
}

$total_contrato = str_replace("$","",$_POST['total_contrato']);
$total_contrato = str_replace(",","",$total_contrato);
$total_contrato = trim($total_contrato);

$porcentaje_retencion = $_POST['porcentaje_retencion'];
if($porcentaje_retencion==''){
    $porcentaje_retencion=0;
}
$porcentaje_amortizacion_anticipo = $_POST['porcentaje_amortizacion_anticipo'];
if($porcentaje_amortizacion_anticipo == ''){
    $porcentaje_amortizacion_anticipo =0;
}
//$concepto_adenda = $_POST['concepto_adenda'];
$descripcion_contrato = $_POST['descripcion_contrato'];
$licencia = strtoupper($_POST['licencia']);
$calle = strtoupper($_POST['calle']);
$no_ext = strtoupper($_POST['no_ext']);
$no_int = strtoupper($_POST['no_int']);
$localidad = strtoupper($_POST['localidad']);
$referencia = strtoupper($_POST['referencia']);
$estado_id = $_POST['estado_id'];
$municipio_id = $_POST['municipio_id'];
$codigo_postal = $_POST['codigo_postal'];
$colonia = strtoupper($_POST['colonia']);
$permite_deductivas = (isset($_POST['permite_deductivas']))?1:0;
$permite_aditivas = (isset($_POST['permite_aditivas']))?1:0;  

$con="INSERT INTO contratos (folio, id_tipo_contrato, cliente_id, registro_patronal, fecha_firma, fecha_inicio, fecha_termino, fecha_firma_siroc, fecha_cierre_siroc, monto_anticipo, importe_contratado, suministros, total_contrato, porcentaje_retencion, porcentaje_amortizacion_anticipo, descripcion_contrato, licencia, calle, no_ext, no_int, localidad, referencia, estado_id, municipio_id, codigo_postal, colonia, permite_deductivas, permite_aditivas) VALUES ('".$folio."', ".$id_tipo_contrato.", ".$cliente_id.", '".$registro_patronal."', '".$fecha_firma."', '".$fecha_inicio."', '".$fecha_termino."', '".$fecha_firma_siroc."', '".$fecha_cierre_siroc."', ".$monto_anticipo.", ".$importe_contratado.", ".$suministros.", ".$total_contrato.", ".$porcentaje_retencion.", ".$porcentaje_amortizacion_anticipo.", '".$descripcion_contrato."', '".$licencia."', '".$calle."', '".$no_ext."', '".$no_int."', '".$localidad."', '".$referencia."', ".$estado_id.", ".$municipio_id.", '".$codigo_postal."', '".$colonia."', ".$permite_deductivas.", ".$permite_aditivas.") ";
//echo $con;
$clsConsulta->guardarGeneral($con);
$id_contrato=$clsConsulta->ultimoid; 

$archivo = explode("|",$_POST['archivo']);
foreach($archivo as $file){
    if($file!=''){
        $conf="INSERT INTO contratos_archivos(id_contrato, archivo) VALUES (".$id_contrato.", '".$file."')";
   //     echo $conf.'<br>';
        $clsConsulta->aplicaQuery($conf);   
    }            
}
?>