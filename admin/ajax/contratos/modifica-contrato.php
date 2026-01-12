<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$id = $_POST['id'];
$folio = $_POST['folio'];
//$archivo = $_POST['archivo'];
$id_tipo_contrato = $_POST['id_tipo'];
$cliente_id = $_POST['id_cliente'];
$registro_patronal = strtoupper($_POST['registro_patronal']);
$fecha_firma = $_POST['fecha_firma'];
$fecha_alta= $_POST['fecha_firma'];
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

$porcentaje_retencion = trim($_POST['porcentaje_retencion']);
if($porcentaje_retencion==''){
    $porcentaje_retencion=0;
}
/*
$porcentaje_amortizacion_anticipo = trim($_POST['porcentaje_amortizacion_anticipo']);
if($porcentaje_amortizacion_anticipo == ''){
    $porcentaje_amortizacion_anticipo =0;
}
*/
//$concepto_adenda = $_POST['concepto_adenda'];
$descripcion_contrato = strtoupper($_POST['descripcion_contrato']);
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
$fraccionamiento = strtoupper($_POST['fraccionamiento']);
//$accion = $_POST['accion'];
$id_usuario = $_POST['id_usuario'];
$sucursales = strtoupper($_POST['sucursales']);
$seccion='EXPEDIENTES';
$sucursal = $_POST['sucursales'];
$accion = 'Folio / Órden de trabajo / Órden de pedido con folio '.$folio.' se ha Modificado ';
$comentario='';
$seccion='Folios';

/*
$permite_deductivas = (isset($_POST['permite_deductivas']))?1:0;
$permite_aditivas = (isset($_POST['permite_aditivas']))?1:0;  
*/

$con="UPDATE contratos SET id_tipo_contrato=".$id_tipo_contrato.", registro_patronal='".$registro_patronal."', fecha_firma='".$fecha_firma."', fecha_inicio='".$fecha_inicio."', fecha_termino='".$fecha_termino."', fecha_firma_siroc='".$fecha_firma_siroc."', fecha_cierre_siroc='".$fecha_cierre_siroc."', monto_anticipo=".$monto_anticipo.", importe_contratado=".$importe_contratado.", suministros=".$suministros.", total_contrato=".$total_contrato.", porcentaje_retencion=".$porcentaje_retencion.", descripcion_contrato='".$descripcion_contrato."', licencia='".$licencia."', calle='".$calle."', no_ext='".$no_ext."', no_int='".$no_int."', localidad='".$localidad."', referencia='".$referencia."', estado_id=".$estado_id.", municipio_id=".$municipio_id.", codigo_postal='".$codigo_postal."', colonia='".$colonia."', fraccionamiento='".$fraccionamiento."', es_contrato=1 WHERE id=".$id;
//echo $con;
$clsConsulta->aplicaQuery($con);
//$id_contrato=$clsConsulta->ultimoid; 
/*
$archivo = explode("|",$_POST['archivo']);
foreach($archivo as $file){
    if($file!=''){
        $conf="INSERT INTO contratos_archivos(id_contrato, archivo) VALUES (".$id.", '".$file."')";
   //     echo $conf.'<br>';
        $clsConsulta->aplicaQuery($conf);   
    }            
}

*/

$clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);
/*
    $conin="INSERT INTO presupuestos_correos (id_presupuesto, id_cliente, token, fecha_envio, correo, sucursal, enviado) VALUES (".$idPresupuesto.", ".$cliente_id.", '".$token."', NOW(), '".$correoDestinatario."', '".$sucursal."', '".$enviado."' )";    
    $clsConsulta->aplicaQuery($conin); 
*/
?>