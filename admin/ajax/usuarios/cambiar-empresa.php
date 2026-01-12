<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_empresa = intval($_POST['id_empresa'] ?? 0);

if ($id_empresa <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de empresa inválido']);
    exit;
}

$sql = "SELECT razon_social FROM cat_empresas WHERE id = $id_empresa LIMIT 1";
$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0 && is_array($res)) {
    foreach ($res as $row) {
        $_SESSION['id_empresa'] = $id_empresa;
        $_SESSION['razon_social'] = $row['razon_social'];
        echo json_encode(['success' => true, 'empresa' => $row['razon_social']]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Empresa no encontrada']);
}
