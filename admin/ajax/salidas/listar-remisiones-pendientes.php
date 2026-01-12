<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Solo remisiones pendientes y que no hayan sido asignadas a una salida
$sql = "
SELECT 
    r.id,
    r.fecha,
    r.total,
    r.direccion_envio,
    c.razon_social,
    c.nombre_comercial
FROM cab_remisiones r
INNER JOIN cat_clientes c ON r.id_cliente = c.id
LEFT JOIN salida_remisiones sr ON sr.id_remision = r.id
WHERE r.estatus = 'pendiente' AND sr.id_remision IS NULL
ORDER BY r.fecha DESC
";

$remisiones = $clsConsulta->consultaGeneral($sql);

$data = [];

if ($clsConsulta->numrows > 0) {
    foreach ($remisiones as $i => $r) {
        if ($i === 0) continue;

        $data[] = [
            'id' => $r['id'],
            'folio' => $r['id'],
            'fecha' => date('Y-m-d', strtotime($r['fecha'])),
            'cliente' => $r['razon_social'] . ' / ' . $r['nombre_comercial'],
            'destino' => $r['direccion_envio'] ?? '(Sin dirección)',
            'total' => $r['total']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
