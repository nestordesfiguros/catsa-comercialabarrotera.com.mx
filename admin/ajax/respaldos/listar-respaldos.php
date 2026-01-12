<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

session_start();

// Parámetros DataTables
$draw = intval($_GET['draw'] ?? 1);
$start = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 25);

// Conteo total
$sql_total = "SELECT COUNT(*) as total FROM respaldos_bd";
$res_total = $clsConsulta->consultaGeneral($sql_total);
$total = $res_total[0]['total'] ?? 0;

// Datos paginados
$sql_datos = "
    SELECT r.*, 
           CONCAT(u.nombre, ' ', u.apellido1, ' ', u.apellido2) AS usuario
    FROM respaldos_bd r
    INNER JOIN usuarios u ON u.id = r.id_usuario
    ORDER BY r.fecha DESC
    LIMIT $start, $length
";
$respaldos = $clsConsulta->consultaGeneral($sql_datos);
if (!is_array($respaldos)) $respaldos = [];

$data = [];
foreach ($respaldos as $r) {
    $data[] = [
        'fecha'         => $r['fecha'],
        'nombre_archivo'=> htmlspecialchars($r['nombre_archivo']),
        'usuario'       => htmlspecialchars($r['usuario']),
        'ip_origen'     => $r['ip_origen'],
        'ruta_guardado' => $r['ruta_guardado']
    ];
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
    'data' => $data
]);
