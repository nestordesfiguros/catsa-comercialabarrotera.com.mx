<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$roles = [];

$sql = "SELECT id, nombre FROM roles ORDER BY nombre";
$res = $clsConsulta->consultaGeneral($sql);

// Validación segura
if (is_array($res)) {
    foreach ($res as $r) {
        $roles[] = [
            'id' => $r['id'],
            'nombre' => $r['nombre']
        ];
    }
}

echo json_encode(['roles' => $roles]);
