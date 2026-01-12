<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_carta = intval($_POST['id_carta'] ?? 0);
$id_remision = intval($_POST['id_remision'] ?? 0);

if ($id_carta <= 0 || $id_remision <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// 1️ Eliminar relación en la BD
$con = "DELETE FROM cartas_porte_remisiones 
        WHERE id_carta_porte = $id_carta AND id_remision = $id_remision 
        LIMIT 1";

if (!$clsConsulta->aplicaQuery($con)) {
    echo json_encode(['success' => false, 'message' => 'No se pudo quitar la remisión']);
    exit;
}

// 2️ Limpiar la remisión de la sesión temporal
if (isset($_SESSION['remisiones_cartaporte'])) {
    $index = array_search($id_remision, $_SESSION['remisiones_cartaporte']);
    if ($index !== false) {
        unset($_SESSION['remisiones_cartaporte'][$index]);
        $_SESSION['remisiones_cartaporte'] = array_values($_SESSION['remisiones_cartaporte']); // Reindexar
    }
}

// 3️ Respuesta OK
echo json_encode(['success' => true, 'message' => 'Remisión eliminada correctamente']);
