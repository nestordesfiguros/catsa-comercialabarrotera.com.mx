<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$datos = [];

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
        activo
    FROM cat_vehiculos
    ORDER BY id DESC
";

$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    foreach ($res as $row) {
        $fila = [];

        // Nombre / Número Económico
        $fila[] = htmlspecialchars($row['nombre_vehiculo']);

        // Placas
        $fila[] = htmlspecialchars($row['placas']);

        // Marca
        $fila[] = htmlspecialchars($row['marca']);

        // Modelo
        $fila[] = htmlspecialchars($row['modelo']);

        // Año
        $fila[] = intval($row['anio']);

        // Capacidad formateada
        $fila[] = number_format($row['capacidad_kg'], 0) . ' kg';

        // Tipo de unidad
        $fila[] = htmlspecialchars($row['tipo_unidad']);

        // Estatus con badge clickeable
        $estatus = $row['activo'] == 1
            ? "<span class='badge bg-success cambiarEstatusVehiculo' style='cursor:pointer' data-id='{$row['id']}' data-valor='1'>Activo</span>"
            : "<span class='badge bg-secondary cambiarEstatusVehiculo' style='cursor:pointer' data-id='{$row['id']}' data-valor='0'>Inactivo</span>";

        $fila[] = $estatus;

        // Botones de acción
        $id = $row['id'];
        $btnEditar = "<button class='btn btn-sm btn-warning btnEditarVehiculo' data-id='{$id}' title='Editar'><i class='fas fa-edit'></i></button>";
        $btnEliminar = "<button class='btn btn-sm btn-danger btnEliminarVehiculo' data-id='{$id}' title='Eliminar'><i class='fas fa-trash'></i></button>";

        $fila[] = $btnEditar . ' ' . $btnEliminar;

        $datos[] = $fila;
    }
}

echo json_encode(['data' => $datos]);
