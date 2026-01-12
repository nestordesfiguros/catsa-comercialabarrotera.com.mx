<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre === '') {
    echo json_encode(['success' => false, 'message' => 'Nombre vacío']);
    exit;
}

// Validar que no exista el mismo nombre
$sqlCheck = "SELECT id FROM roles WHERE nombre = '$nombre' LIMIT 1";
$check = $clsConsulta->consultaGeneral($sqlCheck);

if (is_array($check) && count($check) > 0) {
    echo json_encode(['success' => false, 'message' => 'Este rol ya existe']);
    exit;
}

// Insertar nuevo rol
$sqlInsert = "INSERT INTO roles (nombre) VALUES ('$nombre')";
$resultado = $clsConsulta->aplicaquery($sqlInsert);

if ($resultado) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al insertar']);
}
