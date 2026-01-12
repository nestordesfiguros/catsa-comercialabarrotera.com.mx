<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idRemision = intval($_POST['id_remision'] ?? 0);

if ($idRemision <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de remisión inválido']);
    exit;
}

// Inicializar sesión si no existe
if (!isset($_SESSION['remisiones_cartaporte'])) {
    $_SESSION['remisiones_cartaporte'] = [];
}

// Si ya existe, evitar duplicado
if (in_array($idRemision, $_SESSION['remisiones_cartaporte'])) {
    echo json_encode(['success' => false, 'message' => 'La remisión ya está agregada.']);
    exit;
}

// Agregar remisión a la sesión
$_SESSION['remisiones_cartaporte'][] = $idRemision;

// Confirmar
echo json_encode(['success' => true, 'message' => 'Remisión agregada correctamente.']);
