<?php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

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

$cols = [];
$vals = [];
foreach ($campos as $c) {
  if (isset($_POST[$c])) {
    $cols[] = $c;
    $isNum = in_array($c, ['id_estado', 'id_municipio', 'cp'], true) && is_numeric($_POST[$c]);
    $vals[] = $cls->sanitizar($_POST[$c], $isNum);
  }
}

if (empty($cols)) {
  echo json_encode(["ok" => false, "msg" => "Sin datos"]);
  exit;
}

$sql = "INSERT INTO cat_empresas (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
$ok  = $cls->guardarGeneral($sql);

echo json_encode(["ok" => (bool)$ok, "id" => $cls->ultimoid ?? null]);
