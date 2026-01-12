<?php
// ajax/permisos/guardar-permisos-rol.php

require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json');

// Validar existencia de datos en $_POST
if (!isset($_POST['id_rol']) || !isset($_POST['permisos'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos']);
    exit;
}

$idRol = intval($_POST['id_rol']);
$permisos = $_POST['permisos'];

if ($idRol <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de rol inválido']);
    exit;
}

// Iniciar transacción
if (!$clsConsulta->aplicaquery("START TRANSACTION")) {
    echo json_encode(['success' => false, 'message' => 'No se pudo iniciar la transacción']);
    exit;
}

try {
    // Eliminar permisos existentes para el rol
    if (!$clsConsulta->aplicaquery("DELETE FROM permisos_rol_modulo WHERE rol = $idRol")) {
        throw new Exception('Error al eliminar permisos previos');
    }

    // Obtener IDs de módulos enviados
    $idsModulos = array_keys($permisos);
    $idsModulosInt = array_map('intval', $idsModulos);

    if (empty($idsModulosInt)) {
        // No hay permisos para insertar, solo comitear y salir
        $clsConsulta->aplicaquery("COMMIT");
        echo json_encode(['success' => true]);
        exit;
    }

    $idsModulosStr = implode(',', $idsModulosInt);

    // Obtener nombres válidos de módulos
    $resModulos = $clsConsulta->consultaGeneral("SELECT id, nombre FROM modulos WHERE id IN ($idsModulosStr)");
    $modulosData = [];
    foreach ($resModulos as $mod) {
        $modulosData[$mod['id']] = $mod['nombre'];
    }

    $valuesArr = [];
    foreach ($permisos as $modIdStr => $perm) {
        $modId = intval($modIdStr);
        if (!in_array($modId, $idsModulosInt)) {
            continue;
        }

        $ver = isset($perm['ver']) ? 1 : 0;
        $crear = isset($perm['crear']) ? 1 : 0;
        $editar = isset($perm['editar']) ? 1 : 0;
        $eliminar = isset($perm['eliminar']) ? 1 : 0;

        $valuesArr[] = "($idRol, $modId, $ver, $crear, $editar, $eliminar)";
    }


    if (empty($valuesArr)) {
        // Nada que insertar, commit y salida exitosa
        $clsConsulta->aplicaquery("COMMIT");
        echo json_encode(['success' => true]);
        exit;
    }

    $insertSQL = "INSERT INTO permisos_rol_modulo (rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar) VALUES " . implode(',', $valuesArr);

    if (!$clsConsulta->aplicaquery($insertSQL)) {
        throw new Exception('Error al insertar permisos');
    }

    $clsConsulta->aplicaquery("COMMIT");
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $clsConsulta->aplicaquery("ROLLBACK");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
