<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Leer JSON puro desde el cuerpo
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// Validar que se haya decodificado correctamente
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'No se recibió JSON válido']);
    exit;
}

$id_rol = intval($data['id_rol'] ?? 0);
$permisos = $data['permisos'] ?? [];

if ($id_rol <= 0 || !is_array($permisos) || count($permisos) === 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Eliminar permisos anteriores
$eliminarSQL = "DELETE FROM permisos_rol_modulo WHERE rol = $id_rol";
$clsConsulta->aplicaquery($eliminarSQL);

// Insertar los nuevos permisos
$guardados = 0;
foreach ($permisos as $permiso) {
    $id_modulo = intval($permiso['id_modulo'] ?? 0);
    $ver = intval($permiso['ver'] ?? 0);
    $crear = intval($permiso['crear'] ?? 0);
    $editar = intval($permiso['editar'] ?? 0);
    $eliminar = intval($permiso['eliminar'] ?? 0);

    if ($id_modulo <= 0) continue;

    $sql = "INSERT INTO permisos_rol_modulo (rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar)
        VALUES ($id_rol, '$id_modulo', $ver, $crear, $editar, $eliminar)";

    if ($clsConsulta->aplicaquery($sql)) {
        $guardados++;
    }
}

echo json_encode([
    'success' => true,
    'message' => "Permisos actualizados correctamente. ($guardados módulos)"
]);
