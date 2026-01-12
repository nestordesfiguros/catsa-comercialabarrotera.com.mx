<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql = "
    SELECT 
        id,
        nombre_vehiculo,
        placas,
        marca,
        modelo,
        anio,
        capacidad_kg,
        tipo_unidad,
        config_vehicular,
        tipo_permiso_sct,
        numero_permiso_sct,
        aseguradora,
        num_poliza,
        remolque1_tipo,
        remolque1_placa,
        remolque2_tipo,
        remolque2_placa,
        activo
    FROM cat_vehiculos
    WHERE id = $id
    LIMIT 1
";

$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0 && isset($res[1])) {
    $data = $res[1];
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
}
