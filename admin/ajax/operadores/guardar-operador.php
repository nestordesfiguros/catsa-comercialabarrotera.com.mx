<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);
$nombre = $clsConsulta->escape($_POST['nombre'] ?? '');
$apellido1 = $clsConsulta->escape($_POST['apellido1'] ?? '');
$apellido2 = $clsConsulta->escape($_POST['apellido2'] ?? '');
$rfc = strtoupper($clsConsulta->escape($_POST['rfc'] ?? ''));
$curp = strtoupper($clsConsulta->escape($_POST['curp'] ?? ''));
$telefono = $clsConsulta->escape($_POST['telefono'] ?? '');
$licencia_numero = $clsConsulta->escape($_POST['licencia_numero'] ?? '');
$licencia_tipo = $clsConsulta->escape($_POST['licencia_tipo'] ?? '');
$licencia_vigencia = $clsConsulta->escape($_POST['licencia_vigencia'] ?? null);

if ($id > 0) {
    // Update
    $sql = "
        UPDATE cat_operadores SET
            nombre = '$nombre',
            apellido1 = '$apellido1',
            apellido2 = '$apellido2',
            rfc = '$rfc',
            curp = '$curp',
            telefono = '$telefono',
            licencia_numero = '$licencia_numero',
            licencia_tipo = '$licencia_tipo',
            licencia_vigencia = " . ($licencia_vigencia ? "'$licencia_vigencia'" : "NULL") . "
        WHERE id = $id
    ";
} else {
    // Insert
    $sql = "
        INSERT INTO cat_operadores 
        (nombre, apellido1, apellido2, rfc, curp, telefono, licencia_numero, licencia_tipo, licencia_vigencia, activo)
        VALUES 
        ('$nombre','$apellido1','$apellido2','$rfc','$curp','$telefono','$licencia_numero','$licencia_tipo'," .
        ($licencia_vigencia ? "'$licencia_vigencia'" : "NULL") . ",1)
    ";
}

$res = $clsConsulta->aplicaquery($sql);
echo json_encode(['success' => $res]);
