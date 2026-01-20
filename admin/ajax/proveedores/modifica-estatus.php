<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idCliente = $_POST['idCliente'];
$estatus = $_POST['estatusnuevo'];
$con = "UPDATE cat_proveedores SET estatus=$estatus WHERE id=" . $idCliente;
$clsConsulta->aplicaQuery($con);
