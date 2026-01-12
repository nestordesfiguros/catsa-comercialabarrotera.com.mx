<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$folio = strtoupper($_POST['folio']);
$id_tipo_contrato = $_POST['tipo_contrato'];
$cliente = strtoupper($_POST['cliente']);
$descripcion_contrato = strtoupper($_POST['descripcion']);

$importe_contratado = str_replace("$","",$_POST['monto']);
$importe_contratado = str_replace(",","",$importe_contratado);
$importe_contratado = trim($importe_contratado);
if($importe_contratado=='$000.00'){
    $importe_contratado=0;
}
if($importe_contratado==''){
    $importe_contratado=0;
}

$con="SELECT * FROM cat_clientes WHERE razon_social='".$cliente."'";
$rs=$clsConsulta->consultaGeneral($con);
$cliente_id=$rs[1]['id'];

$con="INSERT INTO contratos (folio, id_tipo_contrato, cliente_id, importe_contratado, descripcion_contrato) VALUES ('".$folio."', ".$id_tipo_contrato.", ".$cliente_id.", ".$importe_contratado.", '".$descripcion_contrato."') ";
$clsConsulta->guardarGeneral($con);

?>