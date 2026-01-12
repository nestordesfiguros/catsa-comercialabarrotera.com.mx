<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_usuario = intval($_POST['id_usuario'] ?? 0);
$empresas = $_POST['empresas'] ?? [];

if ($id_usuario <= 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario inválido']);
    exit;
}

// Eliminar asignaciones anteriores
$clsConsulta->aplicaquery("DELETE FROM usuarios_empresas WHERE id_usuario = $id_usuario");

// Insertar nuevas asignaciones
if (is_array($empresas) && count($empresas) > 0) {
    foreach ($empresas as $id_empresa) {
        $id_empresa = intval($id_empresa);
        if ($id_empresa > 0) {
            $sql = "INSERT INTO usuarios_empresas (id_usuario, id_empresa)
                    VALUES ($id_usuario, $id_empresa)";
            $clsConsulta->aplicaquery($sql);
        }
    }
}

echo json_encode(['success' => true]);
