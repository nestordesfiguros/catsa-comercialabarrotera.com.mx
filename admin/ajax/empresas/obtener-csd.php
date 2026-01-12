<?php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$id = intval($_POST['id_empresa'] ?? 0);
if ($id <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit;
}

$sql = "SELECT * FROM empresas_csd WHERE id_empresa=" . $cls->sanitizar($id, true) . " ORDER BY id_csd DESC LIMIT 1";
$rs  = $cls->aplicaQuery($sql);
if (!$rs || $rs->num_rows === 0) {
    echo json_encode(["ok" => true, "data" => ["cer" => 0, "key" => 0, "activo" => 0]]);
    exit;
}

$d = mysqli_fetch_assoc($rs);
echo json_encode(["ok" => true, "data" => [
    "cer"    => (isset($d['cer_path']) && $d['cer_path'] != '') ? 1 : 0,
    "key"    => (isset($d['key_path']) && $d['key_path'] != '') ? 1 : 0,
    "activo" => intval($d['activo'] ?? 0)
]]);
