<?php
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Obtener el último folio
$query = "SELECT MAX(id_devolucion) as ultimo_id FROM devoluciones";
$rs = $clsConsulta->consultaGeneral($query);

$nuevo_id = 1;
if ($clsConsulta->numrows > 0 && !empty($rs[1]['ultimo_id'])) {
    $nuevo_id = $rs[1]['ultimo_id'] + 1;
}

// Retornar el nuevo ID
echo json_encode([['devolucion_id' => $nuevo_id]]);
?>