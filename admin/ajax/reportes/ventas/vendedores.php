<?php
// admin/ajax/reportes/ventas/vendedores.php
require __DIR__ . '/../../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Seleccionamos vendedores activos (ajusta condición si tienes flag 'activo')
$sql = "SELECT id, nombre, apellido1, apellido2 FROM cat_vendedores ORDER BY nombre, apellido1";
$rs = $clsConsulta->consultaGeneral($sql);

$vendedoresArray = [];

if ($clsConsulta->numrows > 0 && is_array($rs)) {
    // si la clase devuelve 1-based, recorremos desde 1
    foreach ($rs as $k => $v) {
        if (!is_array($v) || !isset($v['id'])) continue;
        $vendedoresArray[] = [
            "id" => (int)$v['id'],
            "nombre_completo" => trim(($v['nombre'] ?? '') . ' ' . ($v['apellido1'] ?? '') . ' ' . ($v['apellido2'] ?? ''))
        ];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($vendedoresArray, JSON_UNESCAPED_UNICODE);
