<?php
session_start();
header('Content-Type: application/json');
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
$estatus = intval($_POST['estatus'] ?? 0);

if ($id > 0) {
    $query = "UPDATE cat_ofertas SET estatus = $estatus WHERE id_oferta = $id";
    $resultado = $clsConsulta->guardarGeneral($query);

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Estatus actualizado correctamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo actualizar el estatus.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Parámetros inválidos.'
    ]);
}
