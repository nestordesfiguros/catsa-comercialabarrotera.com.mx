<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
session_start();

// Fecha enviada desde POST o por defecto hoy
$fecha = $_POST['fecha'] ?? date('Y-m-d');

$con = "
    SELECT v.id, v.nombre_vehiculo
    FROM cat_vehiculos v
    WHERE v.activo = 1
      AND v.id NOT IN (
          SELECT id_vehiculo
          FROM cartas_porte
          WHERE DATE(fecha_salida) = '$fecha'
            AND estatus NOT IN ('cancelada')
      )
    ORDER BY v.nombre_vehiculo ASC
";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    echo '<option value="">Selecciona...</option>';
    foreach ($rs as $v => $val) {
        echo '<option value="' . $val['id'] . '">' . $val['nombre_vehiculo'] . '</option>';
    }
} else {
    echo '<option value="">No hay vehículos disponibles</option>';
}
