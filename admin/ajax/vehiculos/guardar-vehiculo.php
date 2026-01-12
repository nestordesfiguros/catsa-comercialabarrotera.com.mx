<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
$nombre_vehiculo = $clsConsulta->escape($_POST['nombre_vehiculo'] ?? '');
$placas = $clsConsulta->escape($_POST['placas'] ?? '');
$marca = $clsConsulta->escape($_POST['marca'] ?? '');
$modelo = $clsConsulta->escape($_POST['modelo'] ?? '');
$anio = intval($_POST['anio'] ?? 0);
$capacidad_kg = intval($_POST['capacidad_kg'] ?? 0);
$tipo_unidad = $clsConsulta->escape($_POST['tipo_unidad'] ?? '');
$activo = intval($_POST['activo'] ?? 1);

// === Campos nuevos para Carta Porte ===
$config_vehicular = $clsConsulta->escape($_POST['config_vehicular'] ?? '');
$tipo_permiso_sct = $clsConsulta->escape($_POST['tipo_permiso_sct'] ?? '');
$numero_permiso_sct = $clsConsulta->escape($_POST['numero_permiso_sct'] ?? '');
$aseguradora = $clsConsulta->escape($_POST['aseguradora'] ?? '');
$num_poliza = $clsConsulta->escape($_POST['num_poliza'] ?? '');
$remolque1_tipo = $clsConsulta->escape($_POST['remolque1_tipo'] ?? '');
$remolque1_placa = $clsConsulta->escape($_POST['remolque1_placa'] ?? '');
$remolque2_tipo = $clsConsulta->escape($_POST['remolque2_tipo'] ?? '');
$remolque2_placa = $clsConsulta->escape($_POST['remolque2_placa'] ?? '');

if ($id > 0) {
    // Actualización
    $sql = "UPDATE cat_vehiculos SET
        nombre_vehiculo = '$nombre_vehiculo',
        placas = '$placas',
        marca = '$marca',
        modelo = '$modelo',
        anio = $anio,
        capacidad_kg = $capacidad_kg,
        tipo_unidad = '$tipo_unidad',
        config_vehicular = '$config_vehicular',
        tipo_permiso_sct = '$tipo_permiso_sct',
        numero_permiso_sct = '$numero_permiso_sct',
        aseguradora = '$aseguradora',
        num_poliza = '$num_poliza',
        remolque1_tipo = '$remolque1_tipo',
        remolque1_placa = '$remolque1_placa',
        remolque2_tipo = '$remolque2_tipo',
        remolque2_placa = '$remolque2_placa',
        activo = $activo
        WHERE id = $id
    ";
} else {
    // Inserción
    $sql = "INSERT INTO cat_vehiculos 
        (nombre_vehiculo, placas, marca, modelo, anio, capacidad_kg, tipo_unidad,
         config_vehicular, tipo_permiso_sct, numero_permiso_sct,
         aseguradora, num_poliza,
         remolque1_tipo, remolque1_placa, remolque2_tipo, remolque2_placa,
         activo)
        VALUES (
        '$nombre_vehiculo', '$placas', '$marca', '$modelo', $anio, $capacidad_kg, '$tipo_unidad',
        '$config_vehicular', '$tipo_permiso_sct', '$numero_permiso_sct',
        '$aseguradora', '$num_poliza',
        '$remolque1_tipo', '$remolque1_placa', '$remolque2_tipo', '$remolque2_placa',
        $activo
        )
    ";
}

$res = $clsConsulta->aplicaquery($sql);
echo json_encode(['success' => $res]);
