<?php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit;
}

$campos = [
    'razon_social',
    'nombre_comercial',
    'rfc',
    'correo',
    'calle',
    'num_ext',
    'num_int',
    'id_estado',
    'id_municipio',
    'cp',
    'colonia',
    'tel1',
    'tel2',
    'regimen_fiscal'
];

$sets = [];
foreach ($campos as $c) {
    if (array_key_exists($c, $_POST)) {
        $isNum = in_array($c, ['id_estado', 'id_municipio', 'cp'], true) && is_numeric($_POST[$c]);
        $sets[] = $c . '=' . $cls->sanitizar($_POST[$c], $isNum);
    }
}
if (empty($sets)) {
    echo json_encode(["ok" => false, "msg" => "Sin cambios"]);
    exit;
}

$sql = "UPDATE cat_empresas SET " . implode(', ', $sets) . " WHERE id={$id} LIMIT 1";
$ok  = $cls->aplicaQuery($sql);
echo json_encode(["ok" => (bool)$ok, "msg" => $ok ? 'Guardado' : 'Sin cambios']);
