<?php
// ajax/almacen-salidas/crear-desde-remision.php
session_start();
include '../../lib/clsConsultas.php';
include '../../lib/SalidasAlmacen.php';

$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idUsuario = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;

$idRemision = isset($_POST['id_remision']) ? (int)$_POST['id_remision'] : 0;

if ($idEmpresa <= 0 || $idUsuario <= 0 || $idRemision <= 0) {
    echo "error: parámetros inválidos";
    exit;
}

$svc = new SalidasAlmacen($clsConsulta);
$res = $svc->crearDesdeRemision($idEmpresa, $idUsuario, $idRemision);

if ($res['ok']) {
    echo "success:" . (int)($res['id_salida'] ?? 0);
} else {
    echo "error:" . ($res['msg'] ?? 'falló');
}
