<?php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id_vendedor   = intval($_POST['id_vendedor'] ?? 0);
$fecha_inicio  = $_POST['fecha_inicio'] ?? '';
$fecha_fin     = $_POST['fecha_fin'] ?? '';

if (!$id_vendedor || !$fecha_inicio || !$fecha_fin) {
  echo json_encode(['success' => false, 'msg' => 'Datos incompletos.']);
  exit;
}

if ($fecha_inicio > $fecha_fin) {
  echo json_encode(['success' => false, 'msg' => 'La fecha de inicio no puede ser mayor a la de fin.']);
  exit;
}

// Validar si ya existe un corte para ese vendedor y ese periodo exacto
$validacion = "SELECT COUNT(*) AS total FROM pagos_vendedores 
               WHERE id_vendedor = $id_vendedor 
               AND fecha_inicio = '$fecha_inicio' 
               AND fecha_fin = '$fecha_fin'";
$resultado = $clsConsulta->consultaGeneral($validacion);
$yaExiste = $resultado[0]['total'] ?? 0;

if ($yaExiste > 0) {
  echo json_encode(['success' => false, 'msg' => 'Ya existe un corte de comisiones para ese vendedor y periodo.']);
  exit;
}

// Obtener total de ventas y porcentaje de comisión
$sql = "SELECT 
          SUM(r.total) AS total_ventas,
          v.comision
        FROM cab_remisiones r
        INNER JOIN cat_vendedores v ON v.id = r.id_vendedor
        WHERE r.id_vendedor = $id_vendedor
          AND DATE(r.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
          AND r.estatus = 'procesada'
          AND (
            r.tipo_venta = 'contado'
            OR (
              r.tipo_venta = 'credito' 
              AND EXISTS (
                SELECT 1 FROM cab_cxc c 
                WHERE c.id_remision = r.id AND c.estatus = 'Pagada'
              )
          ))";

$datos = $clsConsulta->consultaGeneral($sql)[0] ?? null;

if (!$datos || floatval($datos['total_ventas']) <= 0) {
  echo json_encode(['success' => false, 'msg' => 'No hay ventas válidas en el periodo.']);
  exit;
}

$total_ventas = floatval($datos['total_ventas']);
$porcentaje   = floatval($datos['comision']);
$total_comision = $total_ventas * $porcentaje / 100;

$insert = "INSERT INTO pagos_vendedores 
(id_vendedor, fecha_inicio, fecha_fin, total_ventas, porcentaje_comision, total_comision, estatus)
VALUES ($id_vendedor, '$fecha_inicio', '$fecha_fin', $total_ventas, $porcentaje, $total_comision, 'pendiente')";

$res = $clsConsulta->aplicaquery($insert);
echo json_encode(['success' => $res, 'msg' => $res ? 'Corte registrado correctamente' : 'Error al guardar']);
