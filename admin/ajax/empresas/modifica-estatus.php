<?php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$idEmpresa   = intval($_POST['idEmpresa'] ?? 0);
$estatusNuevo = intval($_POST['estatusNuevo'] ?? 0);

if ($idEmpresa <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit;
}

$sql = "UPDATE cat_empresas SET estatus=" . $cls->sanitizar($estatusNuevo, true) . " WHERE id=" . $cls->sanitizar($idEmpresa, true) . " LIMIT 1";
$ok  = $cls->aplicaQuery($sql);

echo json_encode(["ok" => (bool)$ok]);
