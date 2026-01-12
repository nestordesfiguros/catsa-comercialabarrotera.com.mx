<?php
// ajax/gastos/cambiar-estatus.php
session_start();
require_once '../../lib/config.php';
require_once '../../lib/clsConsultas.php';

$consulta = new Consultas();
$idEmpresa = $_SESSION['id_empresa'];
$idGasto = intval($_POST['idGasto'] ?? 0);
$estatusNuevo = ($_POST['estatusNuevo'] ?? 'Activo') === 'Cancelado' ? 'Cancelado' : 'Activo';

$sql = "UPDATE mov_gastos SET estatus = '$estatusNuevo', fecha_modificacion = NOW()
        WHERE id_gasto = " . intval($idGasto) . " AND id_empresa = " . intval($idEmpresa) . " LIMIT 1";
$ok = $consulta->aplicaquery($sql);
echo json_encode(["ok" => $ok ? true : false, "msg" => $ok ? "" : "No se pudo cambiar el estatus"]);
