<?php
session_start();
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$datos = [];

$con = "
SELECT 
    cp.id, 
    cp.folio, 
    cp.fecha_salida, 
    a.almacen AS origen,
    cp.destino,
    cp.estatus,
    v.nombre_vehiculo, 
    v.placas,
    CONCAT(o.nombre,' ',o.apellido1,' ',IFNULL(o.apellido2,'')) AS operador
FROM cartas_porte cp
INNER JOIN cat_vehiculos v ON cp.id_vehiculo = v.id
INNER JOIN cat_operadores o ON cp.id_operador = o.id
INNER JOIN cat_almacenes a ON cp.origen = a.id
ORDER BY cp.id DESC
";

$res = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    foreach ($res as $row) {
        $fila = [];

        // Folio
        $fila[] = htmlspecialchars($row['folio']);

        // Vehículo con placas
        $fila[] = htmlspecialchars($row['nombre_vehiculo'] . ' - ' . $row['placas']);

        // Operador
        $fila[] = htmlspecialchars($row['operador']);

        // Fecha de salida
        $fila[] = date('d/m/Y H:i', strtotime($row['fecha_salida']));

        // Origen
        $fila[] = htmlspecialchars($row['origen']);

        // Destino
        $fila[] = htmlspecialchars($row['destino']);

        // Badge que abre modal
        $badgeClass = match ($row['estatus']) {
            'pendiente'   => 'bg-warning text-dark',
            'en_transito' => 'bg-primary',
            'finalizada'  => 'bg-success',
            'cancelada'   => 'bg-secondary',
            default       => 'bg-dark'
        };

        $badge = "<span class='badge $badgeClass btnCambiarEstatus' 
                     style='cursor:pointer'
                     data-id='{$row['id']}' 
                     data-estatus='{$row['estatus']}'>
                     " . strtoupper(str_replace('_', ' ', $row['estatus'])) . "
                  </span>";

        $fila[] = $badge;

        // Acciones
        $id = intval($row['id']);
        $btnDetalle = "<a href='cartas-porte-editar/$id' class='btn btn-sm btn-info' title='Ver Detalle'><i class='fas fa-eye'></i></a>";
        $btnPDF = "<a href='ajax/cartas-porte/pdf-carta-porte.php?id=$id' target='_blank' class='btn btn-sm btn-danger' title='Generar PDF'><i class='fas fa-file-pdf'></i></a>";

        // Si está finalizada o en tránsito, deshabilitar editar
        if ($row['estatus'] === 'finalizada' || $row['estatus'] === 'en_transito') {
            $btnDetalle = "<button class='btn btn-sm btn-secondary' title='No editable' disabled><i class='fas fa-eye'></i></button>";
        }

        $fila[] = $btnDetalle . " " . $btnPDF;

        $datos[] = $fila;
    }
}

echo json_encode(['data' => $datos]);
