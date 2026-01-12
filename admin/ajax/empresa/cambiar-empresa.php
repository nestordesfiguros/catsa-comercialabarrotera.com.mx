<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['empresaId']);
$razon = trim($_POST['razonSocial']);

$_SESSION['id_empresa'] = $id;
$_SESSION['razon_social'] = $razon;

echo json_encode([
    'ok' => true,
    'id' => $id,
    'SESSION' => $_SESSION['id_empresa']
]);
?>

