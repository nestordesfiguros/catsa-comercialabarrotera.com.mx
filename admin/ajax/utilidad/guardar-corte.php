<?php
// ajax/utilidad/guardar-corte.php

session_start();
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Validación básica de sesión
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'msg' => 'Sesión expirada, vuelve a ingresar.']);
    exit;
}

$id_user = $_SESSION['id_user'];

// Validar y sanitizar POST
$fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
$fecha_fin    = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';
$total_ventas = isset($_POST['total_ventas']) ? floatval($_POST['total_ventas']) : 0;
$total_costo  = isset($_POST['total_costo']) ? floatval($_POST['total_costo']) : 0;
$utilidad     = isset($_POST['utilidad']) ? floatval($_POST['utilidad']) : 0;
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

if (!$fecha_inicio || !$fecha_fin) {
    echo json_encode(['success' => false, 'msg' => 'Indica el rango de fechas.']);
    exit;
}
if ($fecha_inicio > $fecha_fin) {
    echo json_encode(['success' => false, 'msg' => 'La fecha inicial no puede ser mayor que la final.']);
    exit;
}
if ($total_ventas < 0 || $total_costo < 0 || $utilidad < 0) {
    echo json_encode(['success' => false, 'msg' => 'Los totales no pueden ser negativos.']);
    exit;
}

// Validar que NO exista ya un corte en el mismo rango de fechas
$sql_valida = "
    SELECT id FROM cortes_utilidad
    WHERE fecha_inicio = '$fecha_inicio'
      AND fecha_fin = '$fecha_fin'
    LIMIT 1
";
$res_valida = $clsConsulta->consultaGeneral($sql_valida);

if ($res_valida && count($res_valida) > 0) {
    echo json_encode(['success' => false, 'msg' => 'Ya existe un corte guardado para este rango de fechas.']);
    exit;
}

// Insertar nuevo corte
$sql = "
    INSERT INTO cortes_utilidad
    (fecha_inicio, fecha_fin, total_ventas, total_costo, utilidad, observaciones, usuario_id)
    VALUES
    ('$fecha_inicio', '$fecha_fin', $total_ventas, $total_costo, $utilidad, '" . addslashes($observaciones) . "', $id_user)
";

$result = $clsConsulta->guardarGeneral($sql);

if ($result) {
    echo json_encode(['success' => true, 'msg' => 'Corte guardado correctamente.']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Ocurrió un error al guardar el corte.']);
}
