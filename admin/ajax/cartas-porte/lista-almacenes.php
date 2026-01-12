<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Consulta almacenes activos
$sql = "SELECT id, almacen 
        FROM cat_almacenes 
        WHERE estatus = 1 
        ORDER BY almacen ASC";

$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    echo '<option value="">Selecciona...</option>';
    foreach ($res as $row) {
        $nombre = $row['almacen'] ?: 'SIN NOMBRE';
        echo '<option value="' . intval($row['id']) . '">' . htmlspecialchars($nombre) . '</option>';
    }
} else {
    echo '<option value="">Sin almacenes activos</option>';
}
