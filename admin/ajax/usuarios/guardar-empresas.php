<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_usuario = intval($_POST['id_usuario'] ?? 0);
$empresas = $_POST['empresas'] ?? [];

if ($id_usuario <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

if (!is_array($empresas)) {
    echo json_encode(['success' => false, 'message' => 'Formato de empresas incorrecto']);
    exit;
}

if (count($empresas) === 0) {
    echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos una empresa']);
    exit;
}

// Limpiar asignaciones previas
$clsConsulta->aplicaquery("DELETE FROM usuarios_empresas WHERE id_usuario = $id_usuario");

// Insertar nuevas asignaciones (si hay)
$asignadas = 0;
foreach ($empresas as $id_empresa) {
    $id_empresa = intval($id_empresa);
    if ($id_empresa > 0) {
        $sql = "INSERT INTO usuarios_empresas (id_usuario, id_empresa) VALUES ($id_usuario, $id_empresa)";
        if ($clsConsulta->aplicaquery($sql)) {
            $asignadas++;
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => $asignadas > 0
        ? "Se asignaron $asignadas empresa(s)"
        : "No se asignaron empresas"
]);
