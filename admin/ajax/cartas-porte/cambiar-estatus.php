<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
$estatus = $_POST['estatus'] ?? '';

if ($id <= 0 || $estatus === '') {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Validar que el estatus sea permitido
$estatusPermitidos = ['pendiente', 'en_transito', 'finalizada', 'cancelada'];
if (!in_array($estatus, $estatusPermitidos)) {
    echo json_encode(['success' => false, 'message' => 'Estatus no válido']);
    exit;
}

// Obtener estatus actual para validar si es editable
$con = "SELECT estatus FROM cartas_porte WHERE id = $id LIMIT 1";
$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'message' => 'Carta Porte no encontrada']);
    exit;
}

$estatusActual = $rs[1]['estatus'];

// Si la carta ya está finalizada o en tránsito, solo permitir cancelar
if ($estatusActual === 'finalizada') {
    echo json_encode(['success' => false, 'message' => 'La Carta Porte finalizada no puede modificarse']);
    exit;
}

// Actualizar estatus
$conUpdate = "
    UPDATE cartas_porte 
    SET estatus = '$estatus'
    WHERE id = $id
    LIMIT 1
";

if ($clsConsulta->aplicaQuery($conUpdate)) {
    echo json_encode(['success' => true, 'message' => "Estatus actualizado a '$estatus'"]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estatus']);
}
