<?php 
// ajax/clientes/get-plazo-cliente.php
include '../../lib/clsConsultas.php';
$cls = new Consultas();
$id = intval($_POST['id']);
$plazo = 30;
$rs = $cls->consultaGeneral("SELECT plazo_credito FROM cat_clientes WHERE id=$id LIMIT 1");
if ($rs && isset($rs[0]['plazo_credito'])) $plazo = intval($rs[0]['plazo_credito']);
echo json_encode(['plazo_credito' => $plazo]);
exit;

?>