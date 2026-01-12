<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$datos = [];

$sql = "
    SELECT 
        id,
        CONCAT(nombre, ' ', apellido1, ' ', IFNULL(apellido2,'')) AS nombre_completo,
        rfc,
        curp,
        licencia_numero,
        licencia_tipo,
        licencia_vigencia,
        telefono,
        activo
    FROM cat_operadores
    ORDER BY id DESC
";

$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    foreach ($res as $row) {
        $fila = [];

        // Nombre completo
        $fila[] = $row['nombre_completo'];

        // RFC
        $fila[] = $row['rfc'];

        // CURP
        $fila[] = $row['curp'];

        // Licencia y tipo
        $fila[] = $row['licencia_numero'];
        $fila[] = $row['licencia_tipo'];

        // Vigencia de licencia
        $fila[] = $row['licencia_vigencia'] ?? '';

        // Teléfono
        $fila[] = $row['telefono'] ?? '';

        // Estatus con badge clickeable
        $estatus = $row['activo'] == 1
            ? "<span class='badge bg-success cambiarEstatusOperador' style='cursor:pointer' data-id='{$row['id']}' data-valor='1'>Activo</span>"
            : "<span class='badge bg-secondary cambiarEstatusOperador' style='cursor:pointer' data-id='{$row['id']}' data-valor='0'>Inactivo</span>";
        $fila[] = $estatus;

        // Acciones
        $id = $row['id'];
        $btnEditar = "<button class='btn btn-sm btn-warning btnEditarOperador' data-id='{$id}'><i class='fas fa-edit'></i></button>";
        $btnEliminar = "<button class='btn btn-sm btn-danger btnEliminarOperador' data-id='{$id}'><i class='fas fa-trash'></i></button>";
        $fila[] = $btnEditar . ' ' . $btnEliminar;

        $datos[] = $fila;
    }
}

echo json_encode(['data' => $datos]);
