<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Consulta operadores activos
$sql = "SELECT id, CONCAT(nombre, ' ', apellido1, ' ', IFNULL(apellido2,'')) AS nombre_completo
        FROM cat_operadores
        WHERE activo = 1
        ORDER BY nombre ASC";

$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    echo '<option value="">Selecciona...</option>';
    foreach ($res as $row) {
        echo '<option value="' . intval($row['id']) . '">' . htmlspecialchars($row['nombre_completo']) . '</option>';
    }
} else {
    echo '<option value="">Sin operadores activos</option>';
}
