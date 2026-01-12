<?php
// ajax/usuarios/lista-roles.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Usa la ruta correcta según tu proyecto:
require '../../lib/clsConsultas.php';

$clsConsulta = new Consultas();

$roles = $clsConsulta->consultaGeneral("SELECT id, nombre FROM roles ORDER BY nombre");

if ($clsConsulta->numrows > 0) {
    echo json_encode(array_values($roles));
} else {
    echo json_encode([]);
}
