<?php
// ajax/compras/estatus.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estatus = isset($_POST['estatus']) ? (int)$_POST['estatus'] : 0;

if ($idEmpresa <= 0 || $id <= 0 || !in_array($estatus, [1, 2, 3], true)) {
    echo 'error';
    exit;
}

switch ($estatus) {
    case 1:
        $estado = 'pendiente';
        break;
    case 2:
        $estado = 'procesada';
        break;
    case 3:
        $estado = 'cancelada';
        break;
}

// SOLO actualiza si pertenece a empresa
$con = "UPDATE cab_compras
        SET estatus='{$estado}'
        WHERE id={$id} AND id_empresa={$idEmpresa}";
$clsConsulta->aplicaQuery($con);

echo 'success';
