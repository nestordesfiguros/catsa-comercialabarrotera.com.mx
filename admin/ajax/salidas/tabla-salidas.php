<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$fechaIni = $_GET['fecha_ini'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$estatus = $_GET['estatus'] ?? '';

$where = "WHERE 1=1";
if ($fechaIni && $fechaFin) {
    $where .= " AND sa.fecha BETWEEN '$fechaIni 00:00:00' AND '$fechaFin 23:59:59'";
}
if ($estatus !== '') {
    $where .= " AND sa.estatus = '$estatus'";
}

$sql = "
    SELECT 
        sa.id, 
        sa.fecha, 
        sa.estatus, 
        sa.total,
        CONCAT(v.placas, ' - ', v.marca, ' ', v.modelo) AS vehiculo,
        o.nombre AS operador,
        a.almacen AS almacen
    FROM cab_salida_almacen sa
    LEFT JOIN cat_vehiculos v ON sa.id_vehiculo = v.id
    LEFT JOIN cat_operadores o ON sa.id_operador = o.id
    LEFT JOIN cat_almacenes a ON sa.id_almacen = a.id
    $where
    ORDER BY sa.fecha DESC
";

$data = [];
$res = $clsConsulta->consultaGeneral($sql);

if ($clsConsulta->numrows > 0) {
    foreach ($res as $i => $row) {
        if ($i === 0) continue;

        $botones = '';
        if ($row['estatus'] === 'pendiente') {
            $botones = '
                <button class="btn btn-sm btn-success procesar-salida" data-id="' . $row['id'] . '">Procesar</button>
                <button class="btn btn-sm btn-danger cancelar-salida" data-id="' . $row['id'] . '">Cancelar</button>
            ';
        }

        $data[] = [
            $row['id'],
            date('Y-m-d H:i', strtotime($row['fecha'])),
            $row['almacen'],
            $row['vehiculo'],
            $row['operador'],
            '$' . number_format($row['total'], 2),
            ucfirst($row['estatus']),
            $botones
        ];
    }
}

echo json_encode(['data' => $data]);
