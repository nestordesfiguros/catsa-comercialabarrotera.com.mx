<?php
// ajax/empresas/obtener.php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit;
}

$sql = "SELECT * FROM cat_empresas WHERE id={$id} LIMIT 1";
$rs  = $cls->aplicaQuery($sql);
if (!$rs || $rs->num_rows === 0) {
    echo json_encode(["ok" => false, "msg" => "No encontrado"]);
    exit;
}

$data = mysqli_fetch_assoc($rs);
echo json_encode(["ok" => true, "data" => $data]);
